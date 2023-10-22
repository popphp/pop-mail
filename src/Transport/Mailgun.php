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
use Pop\Mail\Message;

/**
 * Mailgun transport class
 *
 * @category   Pop
 * @package    Pop\Mail
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2024 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    4.0.0
 */
class Mailgun extends AbstractTransport
{

    /**
     * Mailgun API client params
     * @var ?Client
     */
    protected ?Client $client = null;

    /**
     * Constructor
     *
     * Instantiate the Mailgun API transport object
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
        $request->addHeader('Authorization', 'Basic ' . base64_encode('api:' . $apiKey));
        $this->client = new Client($request, ['force_custom_method' => true]);

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
                if (str_contains($contentType, ';')) {
                    $contentType = trim(substr($contentType, 0, strpos($contentType, ';')));
                }
                $fields['attachment[' . $i . ']'] = curl_file_create($part->getFilename(), $contentType, $part->getBasename());
                $i++;
            }
        }

        $this->client->setData($fields);

        if ($i > 0) {
            $this->client->getRequest()->addHeader('Content-Type', 'multipart/form-data');
        }

        return $this->client->send();
    }

}
