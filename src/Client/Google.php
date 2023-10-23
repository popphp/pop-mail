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
use Pop\Mail\Api\AbstractGoogle;

/**
 * Google mail client class
 *
 * @category   Pop
 * @package    Pop\Mail
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2024 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    4.0.0
 */
class Google extends AbstractGoogle implements HttpClientInterface
{

    /**
     * Get messages
     *
     * @param  string $folder
     * @param  bool   $unread
     * @param  int    $limit
     * @throws Exception|Http\Exception|Http\Client\Exception|Http\Client\Handler\Exception
     * @return mixed
     */
    public function getMessages(string $folder = 'Inbox', bool $unread = false, int $limit = 10): mixed
    {
        if ($this->client === null) {
            throw new Exception('Error: The client object has not been instantiated yet.');
        }

        $this->verifyToken();

        $uri = '';

        /**
         * TO-DO
         */

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

        $uri = '';

        /**
         * TO-DO
         */

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

        $uri = '';

        /**
         * TO-DO
         */

        return $this->client->send($uri);
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

        /**
         * TO-DO
         */

        return $this->client->send("/mailfolders('" . $folder . "')/messages/" . $messageId . '/attachments/' . $attachmentId);
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

        /**
         * TO-DO
         */

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