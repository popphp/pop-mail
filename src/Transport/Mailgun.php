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
use Pop\Mail\Api\AbstractHttp;
use Pop\Mail\Message;

/**
 * Mailgun API transport class
 *
 * @category   Pop
 * @package    Pop\Mail
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2024 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    4.0.0
 */
class Mailgun extends AbstractHttp implements TransportInterface
{

    /**
     * Create the API client
     *
     * @param  array|string $options
     * @throws Exception|Client\Exception|\Pop\Mail\Api\Exception
     * @return Mailgun
     */
    public function createClient(array|string $options): Mailgun
    {
        if (is_string($options)) {
            $options = $this->parseOptions($options);
        }

        if (!isset($options['api_url']) || !isset($options['api_key'])) {
            throw new Exception('Error: The required client options were not provided.');
        }

        $request = new Client\Request($options['api_url'], 'POST');
        $request->addHeader('Authorization', 'Basic ' . base64_encode('api:' . $options['api_key']));
        $this->client = new Client($request, new Client\Handler\Curl(), ['force_custom_method' => true]);

        return $this;
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
