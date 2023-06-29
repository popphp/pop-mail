<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp-framework
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2023 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Pop\Mail\Transport;

use Pop\Http\Client;
use Pop\Mail\Message;

/**
 * Sendgrid transport class
 *
 * @category   Pop
 * @package    Pop\Mail
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2023 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    3.6.0
 */
class Sendgrid extends AbstractTransport
{

    /**
     * Sendgrid API client params
     * @var Client\Stream
     */
    protected $client = null;

    /**
     * Constructor
     *
     * Instantiate the Sendgrid API transport object
     *
     * @param string $apiUrl
     * @param string $apiKey
     */
    public function __construct($apiUrl = null, $apiKey = null)
    {
        if ((null !== $apiUrl) && (null !== $apiKey)) {
            $this->createClient($apiUrl, $apiKey);
        }
    }

    /**
     * Create the API client
     *
     * @param string $apiUrl
     * @param string $apiKey
     * @return Client\Stream
     */
    public function createClient($apiUrl, $apiKey)
    {
        $this->client = new Client\Stream($apiUrl, 'POST');
        $this->client->addRequestHeader('Authorization', 'Bearer ' . $apiKey);
        $this->client->createAsJson();

        return $this->client;
    }

    /**
     * Get the API client
     *
     * @return Client\Stream
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * Send the message
     *
     * @param  Message $message
     * @return void
     */
    public function send(Message $message)
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
            if ($part instanceof Message\Text) {
                $fields['content'][] = [
                    'type'  => 'text/plain',
                    'value' => $part->getBody()
                ];
            } else if ($part instanceof Message\Html) {
                $fields['content'][] = [
                    'type'  => 'text/html',
                    'value' => $part->getBody()
                ];
            } else if ($part instanceof Message\Attachment) {
                if (!isset($fields['attachments'])) {
                    $fields['attachments'] = [];
                }
                $contentType = $part->getContentType();
                if (strpos($contentType, ';') !== false) {
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

        $this->client->setFields($fields);
        $this->client->send();
    }

}
