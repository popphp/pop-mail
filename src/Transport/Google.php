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
namespace Pop\Mail\Transport;

use Google\Service\Gmail;
use Pop\Http\Client;
use Pop\Mail\Api\AbstractGoogle;
use Pop\Mail\Message;

/**
 * Mailgun API transport class
 *
 * @category   Pop
 * @package    Pop\Mail
 * @author     Nick Sagona, III <dev@noladev.com>
 * @copyright  Copyright (c) 2009-2025 NOLA Interactive, LLC.
 * @license    https://www.popphp.org/license     New BSD License
 * @version    4.0.3
 */
class Google extends AbstractGoogle implements TransportInterface
{

    /**
     * Send the message
     *
     * @param  Message $message
     * @return mixed
     */
    public function send(Message $message): mixed
    {
        $messageObject = new Gmail\Message();
        $messageObject->setRaw(base64_encode($message->render()));

        $this->verifyToken();
        $this->client->setAccessToken($this->token);

        $gmail      = new Gmail($this->client);
        $gmail->users_messages->send($this->username, $messageObject);

        return null;
    }

}
