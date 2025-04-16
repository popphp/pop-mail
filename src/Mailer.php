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
namespace Pop\Mail;

use Pop\Mail\Transport\TransportInterface;

/**
 * Mailer class
 *
 * @category   Pop
 * @package    Pop\Mail
 * @author     Nick Sagona, III <dev@noladev.com>
 * @copyright  Copyright (c) 2009-2025 NOLA Interactive, LLC.
 * @license    https://www.popphp.org/license     New BSD License
 * @version    4.0.4
 */
class Mailer
{

    /**
     * Transport object
     * @var ?TransportInterface
     */
    protected ?TransportInterface $transport = null;


    /**
     * Default from address
     * @var ?string
     */
    protected ?string $defaultFrom = null;

    /**
     * Constructor
     *
     * Instantiate the message object
     *
     * @param TransportInterface $transport
     * @param ?string            $defaultFrom
     */
    public function __construct(TransportInterface $transport, ?string $defaultFrom = null)
    {
        $this->transport   = $transport;
        $this->defaultFrom = $defaultFrom;
    }

    /**
     * Get the transport object
     *
     * @return TransportInterface
     */
    public function transport(): TransportInterface
    {
        return $this->transport;
    }

    /**
     * Set default from address
     *
     * @param  string $from
     * @return Mailer
     */
    public function setDefaultFrom(string $from): Mailer
    {
        $this->defaultFrom = $from;
        return $this;
    }

    /**
     * Get default from address
     *
     * @return ?string
     */
    public function getDefaultFrom(): ?string
    {
        return $this->defaultFrom;
    }

    /**
     * Has default from address
     *
     * @return bool
     */
    public function hasDefaultFrom(): bool
    {
        return ($this->defaultFrom !== null);
    }

    /**
     * Send message
     *
     * @param  Message $message
     * @return mixed
     */
    public function send(Message $message): mixed
    {
        if ((!$message->hasFrom()) && ($this->hasDefaultFrom())) {
            $message->setFrom($this->defaultFrom);
        }

        return $this->transport->send($message);
    }

    /**
     * Send messages from mail queue
     *
     * @param  Queue $queue
     * @return int
     */
    public function sendFromQueue(Queue $queue): int
    {
        $sent     = 0;
        $messages = $queue->prepare();

        foreach ($messages as $message) {
            if ((!$message->hasFrom()) && ($this->hasDefaultFrom())) {
                $message->setFrom($this->defaultFrom);
            }

            $this->transport->send($message);
            $sent++;
        }

        return $sent;
    }

    /**
     * Send messages from email messages saved to disk in a directory
     *
     * @param  string $dir
     * @throws Exception
     * @return int
     */
    public function sendFromDir(string $dir): int
    {
        if (!file_exists($dir)) {
            throw new Exception('Error: That directory does not exist');
        }

        $sent  = 0;
        $files = array_filter(scandir($dir), function($value) {
            return (($value != '.') && ($value != '..') && ($value != '.empty'));
        });

        foreach ($files as $file) {
            $message = Message::load($dir . DIRECTORY_SEPARATOR . $file);

            if ((!$message->hasFrom()) && ($this->hasDefaultFrom())) {
                $message->setFrom($this->defaultFrom);
            }

            $this->transport->send($message);
            $sent++;
        }

        return $sent;
    }

}
