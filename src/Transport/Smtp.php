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

use Pop\Mail\Message;
use Pop\Mail\Transport\Smtp\Stream\BufferInterface;

/**
 * SMTP transport class
 *
 * @category   Pop
 * @package    Pop\Mail
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2024 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    4.0.0
 */
class Smtp extends Smtp\EsmtpTransport implements TransportInterface
{

    /**
     * Create a new SMTP transport
     *
     * @param string           $host
     * @param int              $port
     * @param ?string          $security
     * @param ?BufferInterface $buffer
     * @param ?array           $handlers
     */
    public function __construct(
        string $host = 'localhost', int $port = 25, ?string $security = null, ?BufferInterface $buffer = null, ?array $handlers = null
    )
    {
        parent::__construct($buffer, $handlers);

        $this->setHost($host);
        $this->setPort($port);
        $this->setEncryption($security);
    }

    /**
     * Send the message
     *
     * @param  Message $message
     * @return int
     */
    public function send(Message $message): int
    {
        return parent::send($message);
    }

}
