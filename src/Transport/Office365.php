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
class Office365 extends AbstractHttp
{

    /**
     * Create the API client
     *
     * @param  array $options
     * @throws Exception|Client\Exception
     * @return Office365
     */
    public function createClient(array $options): Office365
    {

        /**
         * TO-DO
         */
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
        return '';
    }

}