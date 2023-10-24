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
use Aws\Ses\SesClient;
use Pop\Mail\Message\Attachment;
use Pop\Mail\Message\Html;
use Pop\Mail\Message\Text;

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
class Ses extends AbstractHttp implements TransportInterface
{

    /**
     * Create the API client
     *
     * @param  array|string $options
     * @throws Exception|\Pop\Mail\Api\Exception
     * @return Ses
     */
    public function createClient(array|string $options): Ses
    {
        if (is_string($options)) {
            $options = $this->parseOptions($options);
        }

        $key    = $options['key'] ?? null;
        $secret = $options['secret'] ?? null;

        if (($key === null) || ($secret === null)) {
            throw new Exception('Error: The required credentials to create the client object are missing.');
        }

        $this->client = new SesClient([
            'credentials' => [
                'key'     => $key,
                'secret'  => $secret
            ],
            'version' => 'latest',
            'region'  => $options['region'] ?? 'us-east-1'
        ]);

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
        if ($message->hasAttachments()) {
            return $this->client->sendRawEmail([
                'RawMessage' => [
                    'Data' => $message->render()
                ]
            ]);
        } else {
            $parts       = $message->getParts();
            $messageData = [
                'Destination' => [
                    'ToAddresses' => []
                ],
                'Message' => [
                    'Subject' => [
                        'Data' => $message->getSubject()
                    ],
                    'Body' => []
                ],
                'Source' => array_key_first($message->getFrom())
            ];

            $toAddresses       = $message->getTo();
            $ccAddresses       = $message->getCc();
            $bccAddresses      = $message->getBcc();
            $replyToAddress    = $message->getReplyTo();
            $returnPathAddress = $message->getReturnPath();

            if (!empty($toAddresses)) {
                foreach ($toAddresses as $email => $name) {
                    $messageData['Destination']['ToAddresses'][] = (!empty($name)) ? $name . ' <' . $email . '>' : $email;
                }
            }
            if (!empty($ccAddresses)) {
                $messageData['Destination']['CcAddresses'] = [];
                foreach ($ccAddresses as $email => $name) {
                    $messageData['Destination']['CcAddresses'][] = (!empty($name)) ? $name . ' <' . $email . '>' : $email;
                }
            }
            if (!empty($bccAddresses)) {
                $messageData['Destination']['BccAddresses'] = [];
                foreach ($bccAddresses as $email => $name) {
                    $messageData['Destination']['BccAddresses'][] = (!empty($name)) ? $name . ' <' . $email . '>' : $email;
                }
            }
            if (!empty($replyToAddress)) {
                $messageData['ReplyToAddresses'] = [array_key_first($replyToAddress)];
            }
            if (!empty($returnPathAddress)) {
                $messageData['ReturnPath'] = array_key_first($returnPathAddress);
            }

            foreach ($parts as $part) {
                if ($part instanceof Text) {
                    $messageData['Message']['Body']['Text'] = [
                        'Data' => $part->getBody()
                    ];
                } else if ($part instanceof Html) {
                    $messageData['Message']['Body']['Html'] = [
                        'Data' => $part->getBody()
                    ];
                }
            }

            return $this->client->sendEmail($messageData);
        }
    }

}