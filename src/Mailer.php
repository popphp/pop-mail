<?php
declare(strict_types=1);
/**
 * Pop PHP Framework (https://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp-framework
 * @author     Nick Sagona, III <dev@noladev.com>
 * @copyright  Copyright (c) 2009-2027 NOLA Interactive, LLC.
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
 * @copyright  Copyright (c) 2009-2027 NOLA Interactive, LLC.
 * @license    https://www.popphp.org/license     New BSD License
 * @version    5.0.0
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
     * If the transport implements BatchTransportInterface, all prepared
     * messages are handed to it in one call instead of being sent one at a
     * time, so batch-capable transports can use their provider's native
     * bulk-send endpoint.
     *
     * @param  Queue $queue
     * @return int
     */
    public function sendFromQueue(Queue $queue): int
    {
        $messages = $queue->prepare();

        foreach ($messages as $message) {
            if ((!$message->hasFrom()) && ($this->hasDefaultFrom())) {
                $message->setFrom($this->defaultFrom);
            }
        }

        return $this->dispatch($messages);
    }

    /**
     * Send messages from email messages saved to disk in a directory
     *
     * If the transport implements BatchTransportInterface, all loaded
     * messages are handed to it in one call instead of being sent one at a
     * time (see sendFromQueue()).
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

        $files = array_filter(scandir($dir), function($value) {
            return (($value != '.') && ($value != '..') && ($value != '.empty'));
        });

        $messages = [];
        foreach ($files as $file) {
            $message = Message::load($dir . DIRECTORY_SEPARATOR . $file);

            if ((!$message->hasFrom()) && ($this->hasDefaultFrom())) {
                $message->setFrom($this->defaultFrom);
            }

            $messages[] = $message;
        }

        return $this->dispatch($messages);
    }

    /**
     * Send a batch of already-prepared messages, preferring the transport's
     * native batch API when it supports one
     *
     * @param  Message[] $messages
     * @return int
     */
    private function dispatch(array $messages): int
    {
        if ($this->transport instanceof Transport\BatchTransportInterface) {
            return $this->transport->sendBatch($messages);
        }

        $sent = 0;
        foreach ($messages as $message) {
            $this->transport->send($message);
            $sent++;
        }

        return $sent;
    }

}
