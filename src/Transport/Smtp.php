<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp-framework
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2020 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Pop\Mail\Transport;

use Pop\Mail\Message;
use Pop\Mail\Transport\Smtp\Stream\BufferInterface as BI;

/**
 * SMTP transport class
 *
 * @category   Pop
 * @package    Pop\Mail
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2020 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    3.5.0
 */
class Smtp extends Smtp\EsmtpTransport implements TransportInterface
{

    /**
     * Create a new SMTP transport
     *
     * @param string $host      host
     * @param int    $port      port
     * @param string $sec       security
     * @param BI     $buffer    buffer
     * @param array  $handlers  handlers
     */
    public function __construct($host = 'localhost', $port = 25, $sec = null, BI $buffer = null, array $handlers = null)
    {
        parent::__construct($buffer, $handlers);

        $this->setHost($host);
        $this->setPort($port);
        $this->setEncryption($sec);
    }

    /**
     * Send the message
     *
     * @param Message $message
     * @return int
     */
    public function send(Message $message)
    {
        return parent::send($message);
    }

}
