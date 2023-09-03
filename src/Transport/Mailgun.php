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
 * Mailgun transport class
 *
 * @category   Pop
 * @package    Pop\Mail
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2023 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    3.8.0
 */
class Mailgun extends AbstractTransport
{

    /**
     * Mailgun API client params
     * @var Client\Curl
     */
    protected $client = null;

    /**
     * Constructor
     *
     * Instantiate the Mailgun API transport object
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
     * @return Client\Curl
     */
    public function createClient($apiUrl, $apiKey)
    {
        $this->client = new Client\Curl($apiUrl);
        $this->client->addRequestHeader('Authorization', 'Basic ' . base64_encode('api:' . $apiKey));
        $this->client->setOption(CURLOPT_CUSTOMREQUEST, 'POST');

        return $this->client;
    }

    /**
     * Get the API client
     *
     * @return Client\Curl
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
        $fields         = [];
        $headers        = $message->getHeaders();
        $parts          = $message->getParts();
        $primaryHeaders = ['Subject', 'To', 'From', 'CC', 'BCC'];

        foreach ($headers as $header => $value) {
            if (!in_array($header, $primaryHeaders)) {
                $header = 'h:' . $header;
            } else {
                $header = strtolower($header);
            }
            $fields[$header] = $value;
        }

        $i = 0;

        foreach ($parts as $part) {
            if ($part instanceof Message\Text) {
                $fields['text'] = $part->getBody();
            } else if ($part instanceof Message\Html) {
                $fields['html'] = $part->getBody();
            } else if ($part instanceof Message\Attachment) {
                $contentType = $part->getContentType();
                if (strpos($contentType, ';') !== false) {
                    $contentType = trim(substr($contentType, 0, strpos($contentType, ';')));
                }
                $fields['attachment[' . $i . ']'] = curl_file_create($part->getFilename(), $contentType, $part->getBasename());
                $i++;
            }
        }

        $this->client->setFields($fields);

        if ($i > 0) {
            $this->client->addRequestHeader('Content-Type', 'multipart/form-data');
        }

        $this->client->send();
    }

}
