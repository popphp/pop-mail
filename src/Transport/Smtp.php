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

use Pop\Mail\Message;
use Pop\Mail\Transport\Smtp\Stream\BufferInterface;

/**
 * SMTP transport class
 *
 * @category   Pop
 * @package    Pop\Mail
 * @author     Nick Sagona, III <dev@noladev.com>
 * @copyright  Copyright (c) 2009-2025 NOLA Interactive, LLC.
 * @license    https://www.popphp.org/license     New BSD License
 * @version    4.0.4
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
        if ($security !== null) {
            $this->setEncryption($security);
        }
    }


    /**
     * Create the SMTP transport
     *
     * @param  array $options
     * @throws Exception
     * @return Smtp
     */
    public static function create(array $options): Smtp
    {
        if (empty($options['host']) || empty($options['port']) || empty($options['username']) || empty($options['password'])) {
            throw new Exception('Error: The required credentials were not provided.');
        }

        $smtp = new Smtp($options['host'], $options['port']);
        $smtp->setUsername($options['username'])
            ->setPassword($options['password']);

        if (isset($options['encryption'])) {
            $smtp->setEncryption($options['encryption']);
        }

        return $smtp;
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
