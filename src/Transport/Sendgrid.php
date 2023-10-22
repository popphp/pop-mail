<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp-framework
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2024 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Pop\Mail\Transport;

use Pop\Http\Client;
use Pop\Http\Auth;
use Pop\Mail\Message;
use Pop\Mail\Message\Text;
use Pop\Mail\Message\Html;
use Pop\Mail\Message\Attachment;

/**
 * Sendgrid transport class
 *
 * @category   Pop
 * @package    Pop\Mail
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2024 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    4.0.0
 */
class Sendgrid extends AbstractTransport
{

    /**
     * Sendgrid API client params
     * @var ?Client
     */
    protected ?Client $client = null;

    /**
     * Constructor
     *
     * Instantiate the Sendgrid API transport object
     *
     * @param ?string $apiUrl
     * @param ?string $apiKey
     */
    public function __construct(?string $apiUrl = null, ?string $apiKey = null)
    {
        if (($apiUrl !== null) && ($apiKey !== null)) {
            $this->createClient($apiUrl, $apiKey);
        }
    }

    /**
     * Create the API client
     *
     * @param  string $apiUrl
     * @param  string $apiKey
     * @return Client
     */
    public function createClient(string $apiUrl, string $apiKey)
    {
        $request = new Client\Request($apiUrl, 'POST');
        $request->createAsJson();
        $this->client = new Client($request, Auth::createBearer($apiKey));

        return $this->client;
    }

    /**
     * Get the API client
     *
     * @return Client
     */
    public function getClient(): Client
    {
        return $this->client;
    }

    /**
     * Send the message
     *
     * @param  Message $message
     * @return mixed
     */
    public function send(Message $message): mixed
    {
        $headers         = $message->getHeaders();
        $parts           = $message->getParts();
        $personalHeaders = ['From', 'Reply-To', 'Subject', 'To', 'CC', 'BCC'];
        $fields          = [
            'personalizations' => [],
            'content'          => [],
            'subject'          => $message->getSubject()
        ];

        $toAddresses    = $message->getTo();
        $ccAddresses    = $message->getCc();
        $bccAddresses   = $message->getBcc();
        $fromAddress    = $message->getFrom();
        $replyToAddress = $message->getReplyTo();

        $to  = [];
        $cc  = [];
        $bcc = [];

        foreach ($toAddresses as $email => $name) {
            if (!empty($name)) {
                $to[] = [
                    'email' => $email,
                    'name'  => $name
                ];
            } else {
                $to[] = [
                    'email' => $email
                ];
            }
        }

        foreach ($ccAddresses as $email => $name) {
            if (!empty($name)) {
                $cc[] = [
                    'email' => $email,
                    'name'  => $name
                ];
            } else {
                $cc[] = [
                    'email' => $email
                ];
            }
        }

        foreach ($bccAddresses as $email => $name) {
            if (!empty($name)) {
                $bcc[] = [
                    'email' => $email,
                    'name'  => $name
                ];
            } else {
                $bcc[] = [
                    'email' => $email
                ];
            }
        }

        if (!empty($to)) {
            $fields['personalizations'][] = ['to' => $to];
        }
        if (!empty($cc)) {
            $fields['personalizations'][] = ['cc' => $cc];
        }
        if (!empty($bcc)) {
            $fields['personalizations'][] = ['bcc' => $bcc];
        }

        $fields['from'] = ['email' => array_key_first($fromAddress)];


        if (!empty($fromAddress[$fields['from']['email']])) {
            $fields['from']['name'] = $fromAddress[$fields['from']['email']];
        }

        if (!empty($replyToAddress)) {
            $fields['reply_to'] = ['email' => array_key_first($replyToAddress)];
            if (!empty($replyToAddress[$fields['reply_to']['email']])) {
                $fields['reply_to']['name'] = $replyToAddress[$fields['reply_to']['email']];
            }
        } else {
            $fields['reply_to'] = $fields['from'];
        }

        foreach ($headers as $header => $value) {
            if (!in_array($header, $personalHeaders)) {
                if (!isset($fields['headers'])) {
                    $fields['headers'] = [];
                }
                $fields['headers'][$header] = $value;
            }
        }

        foreach ($parts as $part) {
            if ($part instanceof Text) {
                $fields['content'][] = [
                    'type'  => 'text/plain',
                    'value' => $part->getBody()
                ];
            } else if ($part instanceof Html) {
                $fields['content'][] = [
                    'type'  => 'text/html',
                    'value' => $part->getBody()
                ];
            } else if ($part instanceof Attachment) {
                if (!isset($fields['attachments'])) {
                    $fields['attachments'] = [];
                }
                $contentType = $part->getContentType();
                if (str_contains($contentType, ';')) {
                    $contentType = trim(substr($contentType, 0, strpos($contentType, ';')));
                }
                $fields['attachments'][] = [
                    'content'     => base64_encode(file_get_contents($part->getFilename())),
                    'type'        => $contentType,
                    'filename'    => $part->getBasename(),
                    'disposition' => 'attachment'
                ];
            }
        }

        $this->client->setData($fields);
        return $this->client->send();
    }

}
