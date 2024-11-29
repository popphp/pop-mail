<?php
/**
 * Pop PHP Framework (https://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp-framework
 * @author     Nick Sagona, III <dev@noladev.com>
 * @copyright  Copyright (c) 2009-2025 NOLA Interactive, LLC.
 * @license    https://www.popphp.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Pop\Mail\Client;

use GuzzleHttp\Psr7\Request;
use Pop\Http;
use Pop\Mail\Api\AbstractGoogle;
use Google\Service\Gmail;
/**
 * Google mail client class
 *
 * @category   Pop
 * @package    Pop\Mail
 * @author     Nick Sagona, III <dev@noladev.com>
 * @copyright  Copyright (c) 2009-2025 NOLA Interactive, LLC.
 * @license    https://www.popphp.org/license     New BSD License
 * @version    4.0.3
 */
class Google extends AbstractGoogle implements HttpClientInterface
{

    /**
     * Get messages
     *
     * @param  string $folder
     * @param  array  $search
     * @param  int    $limit
     * @throws Exception|Http\Exception|Http\Client\Exception|Http\Client\Handler\Exception
     * @return mixed
     */
    public function getMessages(string $folder = 'Inbox', array $search = [], int $limit = 10): mixed
    {
        if ($this->client === null) {
            throw new Exception('Error: The client object has not been instantiated yet.');
        }

        $this->verifyToken();
        $this->client->setAccessToken($this->token);

        $messages      = [];
        $options       = [];
        $filterStrings = ['in:' . $folder];

        if (!empty($limit)) {
            $options['maxResults'] = $limit;
        }

        if (!empty($search)) {
            foreach ($search as $key => $value) {
                switch (strtolower($key)) {
                    case 'unread':
                        $filterStrings[] = "is:" . (($value) ? "unread" : "read");
                        break;
                    case 'sent after':
                    case 'sent before':
                    case 'sent older':
                    case 'sent newer':
                        $filterStrings[] = "in:" . $key . " " . date('m/d/Y', strtotime($value));
                        break;
                    default:
                        $filterStrings[] = $key . ":" . $value;
                }
            }
        }

        $options['q'] = implode(' ', $filterStrings);

        $gmail       = new Gmail($this->client);
        $messageList = $gmail->users_messages->listUsersMessages($this->username, $options);
        $batch       = $gmail->createBatch();

        foreach ($messageList as $message) {
            $batch->add(new Request('GET', 'https://gmail.googleapis.com/gmail/v1/users/' . $this->username . '/messages/' . $message->id));
        }

        $responses = $batch->execute();

        foreach ($responses as $response) {
            $responseBody = ($response->getStatusCode() == 200) ? json_decode((string)$response->getBody(), true) : [];
            if (isset($responseBody['id'])) {
                $hasAttachments = false;
                if (isset($responseBody['payload'])) {
                    if (isset($responseBody['payload']['headers'])) {
                        foreach ($responseBody['payload']['headers'] as $header) {
                            switch ($header['name']) {
                                case 'Subject':
                                    $responseBody['Subject'] = $header['value'];
                                    break;
                                case 'Date':
                                    $responseBody['Date'] = $header['value'];
                                    break;
                                case 'To':
                                    $responseBody['To'] = $header['value'];
                                    break;
                                case 'From':
                                    $responseBody['From'] = $header['value'];
                                    break;
                                case 'Reply-To':
                                    $responseBody['Reply-To'] = $header['value'];
                                    break;
                            }
                        }
                    }

                    if (isset($responseBody['payload']['parts'])) {
                        foreach ($responseBody['payload']['parts'] as $part) {
                            if (!empty($part['filename'])) {
                                $hasAttachments = true;
                                break;
                            } else if (isset($part['parts'])) {
                                foreach ($part['parts'] as $p) {
                                    if (!empty($p['filename'])) {
                                        $hasAttachments = true;
                                        break;
                                    }
                                }
                            }
                        }
                    }
                }

                $responseBody['attachments'] = $hasAttachments;
                $responseBody['unread']      = (isset($responseBody['labelIds']) && in_array('UNREAD', $responseBody['labelIds']));

                $messages[$responseBody['id']] = $responseBody;
            }

        }

        return $messages;
    }

    /**
     * Get messages
     *
     * @param  string $messageId
     * @param  bool   $raw
     * @throws Exception|Http\Exception|Http\Client\Exception|Http\Client\Handler\Exception
     * @return mixed
     */
    public function getMessage(string $messageId, bool $raw = false): mixed
    {
        if ($this->client === null) {
            throw new Exception('Error: The client object has not been instantiated yet.');
        }

        $this->verifyToken();
        $this->client->setAccessToken($this->token);

        $gmail   = new Gmail($this->client);
        $message = $gmail->users_messages->get($this->username, $messageId, ['format' => (($raw) ? 'raw' : 'full')]);

        return (($raw) && isset($message['raw'])) ? base64_decode(strtr($message['raw'], '._-', '+/=')) : $message;
    }

    /**
     * Get message attachments
     *
     * @param  string $messageId
     * @param  string $folder
     * @throws Exception|Http\Exception|Http\Client\Exception|Http\Client\Handler\Exception
     * @return mixed
     */
    public function getAttachments(string $messageId, string $folder = 'Inbox'): mixed
    {
        if ($this->client === null) {
            throw new Exception('Error: The client object has not been instantiated yet.');
        }

        $this->verifyToken();
        $this->client->setAccessToken($this->token);

        $gmail   = new Gmail($this->client);
        $message = $gmail->users_messages->get($this->username, $messageId, ['format' => 'full']);

        $attachments = [];
        $payload     = $message->getPayload();
        $parts       = $payload->getParts();

        foreach ($parts as $part) {
            if (!empty($part->getFilename())) {
                $attachments[$part->getBody()->getAttachmentId()] = [
                    'id'       => $part->getBody()->getAttachmentId(),
                    'filename' => $part->getFilename(),
                    'mimeType' => $part->getMimeType(),
                    'size'     => $part->getBody()->getSize()
                ];
            } else {
                foreach ($part->getParts() as $p) {
                    if (!empty($p->getFilename())) {
                        $attachments[$p->getBody()->getAttachmentId()] = [
                            'id'       => $p->getBody()->getAttachmentId(),
                            'filename' => $p->getFilename(),
                            'mimeType' => $p->getMimeType(),
                            'size'     => $p->getBody()->getSize()
                        ];
                    }
                }
            }
        }

        return $attachments;
    }

    /**
     * Get message attachment
     *
     * @param  string $messageId
     * @param  string $attachmentId
     * @param  string $folder
     * @throws Exception|Http\Exception|Http\Client\Exception|Http\Client\Handler\Exception
     * @return mixed
     */
    public function getAttachment(string $messageId, string $attachmentId, string $folder = 'Inbox'): mixed
    {
        if ($this->client === null) {
            throw new Exception('Error: The client object has not been instantiated yet.');
        }

        $this->verifyToken();
        $this->client->setAccessToken($this->token);

        $gmail      = new Gmail($this->client);
        $attachment =  $gmail->users_messages_attachments->get($this->username, $messageId, $attachmentId);

        return base64_decode(strtr($attachment->getData(), '-_', '+/'));
    }

    /**
     * Mark message as read
     *
     * @param  string $messageId
     * @param  bool   $isRead
     * @throws Exception|Http\Exception|Http\Client\Exception|Http\Client\Handler\Exception
     * @return Google
     */
    public function markAsRead(string $messageId, bool $isRead = true): Google
    {
        if ($this->client === null) {
            throw new Exception('Error: The client object has not been instantiated yet.');
        }

        $this->verifyToken();
        $this->client->setAccessToken($this->token);

        $messageRequest = new Gmail\ModifyMessageRequest();

        if ($isRead) {
            $messageRequest->setRemoveLabelIds('UNREAD');
        } else {
            $messageRequest->setAddLabelIds('UNREAD');
        }

        $gmail      = new Gmail($this->client);
        $gmail->users_messages->modify($this->username, $messageId, $messageRequest);

        return $this;
    }

    /**
     * Mark message as unread
     *
     * @param  string $messageId
     * @throws Exception|Http\Exception|Http\Client\Exception|Http\Client\Handler\Exception
     * @return Google
     */
    public function markAsUnread(string $messageId): Google
    {
        return $this->markAsRead($messageId, false);
    }
    
}
