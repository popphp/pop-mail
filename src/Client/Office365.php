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
namespace Pop\Mail\Client;

use Pop\Http;
use Pop\Mail\Api\AbstractOffice365;

/**
 * Office 365 mail client class
 *
 * @category   Pop
 * @package    Pop\Mail
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2024 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    4.0.0
 */
class Office365 extends AbstractOffice365 implements HttpClientInterface
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

        $data = [];

        if (!empty($limit)) {
            $data['$top'] = $limit;
        }

        if (!empty($search)) {
            $filterStrings = [];
            foreach ($search as $key => $value) {
                $op         = 'eq';
                $startsWith = false;
                $endsWith   = false;
                if (str_ends_with($key, '%')) {
                    $startsWith = true;
                    $key        = substr($key, 0, -1);
                } else if (str_starts_with($key, '%')) {
                    $endsWith = true;
                    $key      = substr($key, 1);
                } else {
                    if (str_ends_with($key, '!=')) {
                        $op  = 'ne';
                        $key = substr($key, 0, -2);
                    } else if (str_ends_with($key, '>')) {
                        $op  = 'gt';
                        $key = substr($key, 0, -1);
                    } else if (str_ends_with($key, '>=')) {
                        $op  = 'ge';
                        $key = substr($key, 0, -2);
                    } else if (str_ends_with($key, '<')) {
                        $op  = 'lt';
                        $key = substr($key, 0, -1);
                    } else if (str_ends_with($key, '<=')) {
                        $op  = 'le';
                        $key = substr($key, 0, -2);
                    }
                }

                switch (strtolower($key)) {
                    case 'unread':
                        $filterStrings[] = "isRead " . $op . " " . ($value) ? "false" : "true";
                        break;
                    case 'sent':
                        $filterStrings[] = "sentDateTime " . $op . " " . date('c', strtotime($value));
                        break;
                    default:
                        if ($startsWith) {
                            $filterStrings[] = "startsWith(" . $key . ", '" . $value . "')";
                        } else if ($endsWith) {
                            $filterStrings[] = "endsWith(" . $key . ", '" . $value . "')";
                        } else {
                            $filterStrings[] = $key . " " . $op . " '" . $value . "'";
                        }
                }
            }

            if (!empty($filterStrings)) {
                $data['filter'] = implode(' and ', $filterStrings);
            }
        }

        $this->client->setAuth(Http\Auth::createBearer($this->token));
        $this->client->addOption('method', 'GET');
        $this->client->addOption('type', Http\Client\Request::URLFORM);
        $this->client->addOption('auto', true);

        $uri = "/" . $this->accountId . "/mailfolders('" . $folder . "')/messages";
        if (!empty($data)){
            $uri .= '?' . rawurldecode(http_build_query($data, "\n"));
        }

        return $this->client->send($uri);
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

        $this->client->setAuth(Http\Auth::createBearer($this->token));
        $this->client->addOption('method', 'GET');
        $this->client->addOption('type', Http\Client\Request::URLFORM);
        $this->client->addOption('auto', true);

        $uri = "/" . $this->accountId . "/messages/" . $messageId;
        if ($raw) {
            $uri .= '/$value';
        }

        return $this->client->send($uri);
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

        $this->client->setAuth(Http\Auth::createBearer($this->token));
        $this->client->addOption('method', 'GET');
        $this->client->addOption('type', Http\Client\Request::URLFORM);
        $this->client->addOption('auto', true);

        return $this->client->send(
            "/" . $this->accountId . "/mailfolders('" . $folder . "')/messages/" . $messageId . "/attachments"
        );
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

        $this->client->setAuth(Http\Auth::createBearer($this->token));
        $this->client->addOption('method', 'GET');
        $this->client->addOption('type', Http\Client\Request::URLFORM);
        $this->client->addOption('auto', true);

        return $this->client->send(
            "/" . $this->accountId . "/mailfolders('" . $folder . "')/messages/" . $messageId . "/attachments/" . $attachmentId
        );
    }

    /**
     * Mark message as read
     *
     * @param  string $messageId
     * @param  bool   $isRead
     * @throws Exception|Http\Exception|Http\Client\Exception|Http\Client\Handler\Exception
     * @return Office365
     */
    public function markAsRead(string $messageId, bool $isRead = true): Office365
    {
        if ($this->client === null) {
            throw new Exception('Error: The client object has not been instantiated yet.');
        }

        $this->verifyToken();

        $this->client->setAuth(Http\Auth::createBearer($this->token));
        $this->client->addOption('type', Http\Client\Request::JSON);
        $this->client->addOption('auto', true);
        $this->client->addOption('method', 'PATCH');
        $this->client->setData(['isRead' => $isRead]);

        $this->client->send("/" . $this->accountId . "/messages/" . $messageId);

        return $this;
    }

    /**
     * Mark message as unread
     *
     * @param  string $messageId
     * @throws Exception|Http\Exception|Http\Client\Exception|Http\Client\Handler\Exception
     * @return Office365
     */
    public function markAsUnread(string $messageId): Office365
    {
        return $this->markAsRead($messageId, false);
    }

}