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

use Pop\Http;
use Pop\Mail\Api\AbstractOffice365;
use Pop\Mail\Message;
use Pop\Mail\Message\Html;
use Pop\Mail\Message\Text;
use Pop\Mail\Message\Attachment;

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
class Office365 extends AbstractOffice365 implements TransportInterface
{

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
        $personalHeaders = ['From', 'Reply-To', 'Subject', 'To', 'CC', 'BCC', 'MIME-Version'];
        $fields          = [
            'message' => [
                'subject' => $message->getSubject(),
                'body'    => []
            ],
        ];

        $toAddresses    = $message->getTo();
        $ccAddresses    = $message->getCc();
        $bccAddresses   = $message->getBcc();
        $replyToAddress = $message->getReplyTo();
        $senderAddress  = $message->getSender();

        if (!empty($toAddresses)) {
            $fields['message']['toRecipients'] = [];
            foreach ($toAddresses as $email => $name) {
                if (!empty($name)) {
                    $fields['message']['toRecipients'][] = [
                        'emailAddress' => [
                            'address' => $email,
                            'name'   => $name
                        ]
                    ];
                } else {
                    $fields['message']['toRecipients'][] = [
                        'emailAddress' => [
                            'address' => $email
                        ]
                    ];
                }
            }
        }

        if (!empty($ccAddresses)) {
            $fields['message']['ccRecipients'] = [];
            foreach ($ccAddresses as $email => $name) {
                if (!empty($name)) {
                    $fields['message']['ccRecipients'][] = [
                        'emailAddress' => [
                            'address' => $email,
                            'name'    => $name
                        ]
                    ];
                } else {
                    $fields['message']['ccRecipients'][] = [
                        'emailAddress' => [
                            'address' => $email
                        ]
                    ];
                }
            }
        }

        if (!empty($bccAddresses)) {
            $fields['message']['bccRecipients'] = [];
            foreach ($bccAddresses as $email => $name) {
                if (!empty($name)) {
                    $fields['message']['bccRecipients'][] = [
                        'emailAddress' => [
                            'address' => $email,
                            'name'    => $name
                        ]
                    ];
                } else {
                    $fields['message']['bccRecipients'][] = [
                        'emailAddress' => [
                            'address' => $email
                        ]
                    ];
                }
            }
        }

        if (!empty($replyToAddress)) {
            $fields['message']['replyTo'] = ['emailAddress' => ['address' => array_key_first($replyToAddress)]];
        }
        if (!empty($senderAddress)) {
            $fields['message']['sender'] = ['emailAddress' => ['address' => array_key_first($senderAddress)]];
        }

        if (!empty($headers)) {
            foreach ($headers as $header => $value) {
                if (!in_array($header, $personalHeaders)) {
                    if (!isset($fields['message']['internetMessageHeaders'])) {
                        $fields['message']['internetMessageHeaders'] = [];
                    }
                    $fields['message']['internetMessageHeaders'][$header] = $value;
                }
            }
        }

        foreach ($parts as $part) {
            if ($part instanceof Text) {
                $fields['message']['body'] = [
                    'contentType' => 'text',
                    'content'     => $part->getBody()
                ];
            } else if ($part instanceof Html) {
                $fields['message']['body'] = [
                    'contentType' => 'html',
                    'content'     => $part->getBody()
                ];
            } else if ($part instanceof Attachment) {
                if (!isset($fields['message']['attachments'])) {
                    $fields['message']['attachments'] = [];
                }
                $contentType = $part->getContentType();
                if (str_contains($contentType, ';')) {
                    $contentType = trim(substr($contentType, 0, strpos($contentType, ';')));
                }
                $fields['message']['attachments'][] = [
                    '@odata.type'  =>  "#microsoft.graph.fileAttachment",
                    'contentBytes' => base64_encode(file_get_contents($part->getFilename())),
                    'contentType'  => $contentType,
                    'name'         => $part->getBasename(),
                ];
            }
        }

        $this->verifyToken();

        $this->client->setAuth(Http\Auth::createBearer($this->token));
        $this->client->addOption('method', 'POST');
        $this->client->addOption('type', Http\Client\Request::JSON);
        $this->client->addOption('auto', true);

        $this->client->setData($fields);
        return $this->client->send('/' . $this->accountId . '/sendmail');
    }

}