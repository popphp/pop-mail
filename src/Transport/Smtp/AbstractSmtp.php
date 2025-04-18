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
namespace Pop\Mail\Transport\Smtp;

use Pop\Mail\Message;
use Pop\Mail\Transport\TransportInterface;
use Pop\Mail\Transport\Smtp\Stream\BufferInterface;

/**
 * Abstract SMTP transport class
 *
 * @category   Pop
 * @package    Pop\Mail
 * @author     Chris Corbyn, from the SwiftMailer library https://github.com/swiftmailer/swiftmailer
 * @version    4.0.4
 */
abstract class AbstractSmtp implements SmtpInterface, TransportInterface
{

    /**
     * Input-Output buffer for sending/receiving SMTP commands and responses
     * @var ?BufferInterface
     */
    protected ?BufferInterface $buffer = null;

    /**
     * Connection status
     * @var bool
     */
    protected bool $started = false;

    /**
     * The domain name to use in HELO command
     * @var string
     */
    protected string $domain = '[127.0.0.1]';

    /**
     * Source IP
     * @var ?string
     */
    protected ?string $sourceIp = null;

    /**
     * Return an array of params for the Buffer
     */
    abstract protected function getBufferParams(): array;

    /**
     * Creates a new EsmtpTransport using the given I/O buffer.
     *
     * @param BufferInterface $buffer
     */
    public function __construct(BufferInterface $buffer)
    {
        $this->buffer = $buffer;
        $this->lookupHostname();
    }

    /**
     * Set the name of the local domain which Swift will identify itself as.
     *
     * This should be a fully-qualified domain name and should be truly the domain
     * you're using.
     *
     * If your server doesn't have a domain name, use the IP in square
     * brackets (i.e. [127.0.0.1]).
     *
     * @param  string $domain
     * @return AbstractSmtp
     */
    public function setLocalDomain(string $domain): AbstractSmtp
    {
        $this->domain = $domain;
        return $this;
    }

    /**
     * Get the name of the domain Swift will identify as
     *
     * @return string
     */
    public function getLocalDomain(): string
    {
        return $this->domain;
    }

    /**
     * Sets the source IP
     *
     * @param string $source
     */
    public function setSourceIp(string $source): AbstractSmtp
    {
        $this->sourceIp = $source;
        return $this;
    }

    /**
     * Returns the IP used to connect to the destination
     *
     * @return ?string
     */
    public function getSourceIp(): ?string
    {
        return $this->sourceIp;
    }

    /**
     * Start the SMTP connection
     */
    public function start(): void
    {
        if (!$this->started) {
            try {
                $this->buffer->initialize($this->getBufferParams());
            } catch (Exception $e) {
                $this->throwException($e);
            }
            $this->readGreeting();
            $this->doHeloCommand();

            $this->started = true;
        }
    }

    /**
     * Test if an SMTP connection has been established
     *
     * @return bool
     */
    public function isStarted(): bool
    {
        return $this->started;
    }

    /**
     * Send the given Message.
     *
     * Recipient/sender data will be retrieved from the Message API.
     * The return value is the number of recipients who were accepted for delivery.
     *
     * @param  Message $message
     * @return int
     * @throws \Exception
     */
    public function send(Message $message): int
    {
        if (!$this->isStarted()) {
            $this->start();
        }
        $sent        = 0;
        $reversePath = $this->getReversePath($message);
        $domain      = null;

        if ($reversePath === null) {
            $this->throwException(new Exception('Cannot send message without a sender address'));
        } else {
            $domain = substr($reversePath, (strpos($reversePath, '@') + 1));
            $message->generateId($domain);
        }

        $to  = $message->getTo();
        $cc  = $message->getCc();
        $tos = array_merge($to, $cc);
        $bcc = $message->getBcc();

        $message->setBcc([]);

        try {
            $sent += $this->sendTo($message, $reversePath, $tos);
            $sent += $this->sendBcc($message, $reversePath, $bcc);
        } catch (\Exception $e) {
            $message->setBcc($bcc);
            throw $e;
        }

        $message->setBcc($bcc);
        $message->generateId($domain); // Make sure a new Message ID is used

        return $sent;
    }

    /**
     * Stop the SMTP connection.
     */
    public function stop(): void
    {
        if ($this->started) {
            try {
                $this->executeCommand("QUIT\r\n", [221]);
            } catch (Exception $e) {
            }

            try {
                $this->buffer->terminate();
            } catch (Exception $e) {
                $this->throwException($e);
            }
        }
        $this->started = false;
    }

    /**
     * Reset the current mail transaction.
     */
    public function reset(): void
    {
        $this->executeCommand("RSET\r\n", [250]);
    }

    /**
     * Get the IoBuffer where read/writes are occurring.
     *
     * @return BufferInterface
     */
    public function getBuffer(): BufferInterface
    {
        return $this->buffer;
    }

    /**
     * Run a command against the buffer, expecting the given response codes.
     *
     * If no response codes are given, the response will not be validated.
     * If codes are given, an exception will be thrown on an invalid response.
     *
     * @param  string $command
     * @param  array  $codes
     * @return string
     */
    public function executeCommand(string $command, array $codes = []): string
    {
        $seq      = $this->buffer->write($command);
        $response = $this->getFullResponse($seq);
        $this->assertResponseCode($response, $codes);

        return $response;
    }

    /** Read the opening SMTP greeting */
    protected function readGreeting(): void
    {
        $this->assertResponseCode($this->getFullResponse(0), [220]);
    }

    /** Send the HELO welcome */
    protected function doHeloCommand(): void
    {
        $this->executeCommand(sprintf("HELO %s\r\n", $this->domain), [250]);
    }

    /**
     * Send the MAIL FROM command
     *
     * @param string $address
     * @return void
     */
    protected function doMailFromCommand(string $address): void
    {
        $this->executeCommand(sprintf("MAIL FROM:<%s>\r\n", $address), [250]);
    }

    /**
     * Send the RCPT TO command
     *
     * @param  string $address
     * @return void
     */
    protected function doRcptToCommand(string $address): void
    {
        $this->executeCommand(sprintf("RCPT TO:<%s>\r\n", $address), [250, 251, 252]);
    }

    /** Send the DATA command */
    protected function doDataCommand(): void
    {
        $this->executeCommand("DATA\r\n", [354]);
    }

    /**
     * Stream the contents of the message over the buffer
     *
     * @param Message $message
     */
    protected function streamMessage(Message $message): void
    {
        $this->buffer->setWriteTranslations(["\r\n." => "\r\n.."]);
        try {
            $message->toByteStream($this->buffer);
            $this->buffer->flushBuffers();
        } catch (Exception $e) {
            $this->throwException($e);
        }
        $this->buffer->setWriteTranslations([]);
        $this->executeCommand("\r\n.\r\n", [250]);
    }

    /**
     * Determine the best-use reverse path for this message
     *
     * @param  Message $message
     * @return null|string
     */
    protected function getReversePath(Message $message): null|string
    {
        $returnKeys = array_keys($message->getReturnPath());
        $senderKeys = array_keys($message->getSender());
        $fromKeys   = array_keys($message->getFrom());
        $path       = null;
        if (isset($returnKeys[0]) && !empty($returnKeys[0])) {
            $path = $returnKeys[0];
        } elseif (isset($senderKeys[0]) && !empty($senderKeys[0])) {
            $path = $senderKeys[0];
        } elseif (isset($fromKeys[0]) && !empty($fromKeys[0])) {
            $path = $fromKeys[0];
        }

        return $path;
    }

    /**
     * Throw a TransportException, first sending it to any listeners
     *
     * @param  Exception $e
     * @throws Exception
     */
    protected function throwException(Exception $e): void
    {
        throw $e;
    }

    /**
     * Throws an Exception if a response code is incorrect
     *
     * @param string $response
     * @param array  $wanted
     */
    protected function assertResponseCode(string $response, array $wanted): void
    {
        list($code) = sscanf($response, '%3d');
        $valid = (empty($wanted) || in_array($code, $wanted));

        if (!$valid) {
            $this->throwException(
                new Exception(
                    'Expected response code ' . implode('/', $wanted) . ' but got code ' .
                    '"' . $code . '", with message "' . $response . '"',
                    $code
                )
            );
        }
    }

    /**
     * Get an entire multi-line response using its sequence number
     *
     * @param  string $seq
     * @throws Exception
     * @return string
     */
    protected function getFullResponse(string $seq): string
    {
        $response = '';
        try {
            do {
                $line = $this->buffer->readLine($seq);
                $response .= $line;
            } while (($line !== null) && ($line !== false) && ($line[3] != ' '));
        } catch (Exception $e) {
            $this->throwException($e);
        }

        return $response;
    }

    /**
     * Send an email to the given recipients from the given reverse path
     *
     * @param  Message $message
     * @param  string  $reversePath
     * @param  array   $recipients
     * @return int
     */
    private function doMailTransaction(Message $message, string $reversePath, array $recipients): int
    {
        $sent = 0;
        $this->doMailFromCommand($reversePath);
        foreach ($recipients as $forwardPath) {
            try {
                $this->doRcptToCommand($forwardPath);
                ++$sent;
            } catch (Exception $e) {
            }
        }

        if ($sent != 0) {
            $this->doDataCommand();
            $this->streamMessage($message);
        } else {
            $this->reset();
        }

        return $sent;
    }

    /**
     * Send a message to the given To: recipients
     *
     * @param  Message $message
     * @param  string  $reversePath
     * @param  array   $to
     * @return int
     */
    private function sendTo(Message $message, string $reversePath, array $to): int
    {
        if (empty($to)) {
            return 0;
        }

        return $this->doMailTransaction($message, $reversePath, array_keys($to));
    }

    /**
     * Send a message to all Bcc: recipients
     *
     * @param  Message $message
     * @param  string  $reversePath
     * @param  array   $bcc
     * @return int
     */
    private function sendBcc(Message $message, string $reversePath, array $bcc): int
    {
        $sent = 0;
        foreach ($bcc as $forwardPath => $name) {
            $message->setBcc([$forwardPath => $name]);
            $sent += $this->doMailTransaction($message, $reversePath, [$forwardPath]);
        }

        return $sent;
    }

    /**
     * Try to determine the hostname of the server this is run on
     */
    private function lookupHostname(): void
    {
        if (!empty($_SERVER['SERVER_NAME']) && $this->isFqdn($_SERVER['SERVER_NAME'])) {
            $this->domain = $_SERVER['SERVER_NAME'];
        } elseif (!empty($_SERVER['SERVER_ADDR'])) {
            // Set the address literal tag (See RFC 5321, section: 4.1.3)
            if (!str_contains($_SERVER['SERVER_ADDR'], ':')) {
                $prefix = ''; // IPv4 addresses are not tagged.
            } else {
                $prefix = 'IPv6:'; // Adding prefix in case of IPv6.
            }

            $this->domain = sprintf('[%s%s]', $prefix, $_SERVER['SERVER_ADDR']);
        }
    }

    /**
     * Determine is the $hostname is a fully-qualified name
     *
     * @param  string $hostname
     * @return bool
     */
    private function isFqdn(string $hostname): bool
    {
        // We could do a really thorough check, but there's really no point
        if (false !== $dotPos = strpos($hostname, '.')) {
            return ($dotPos > 0) && ($dotPos != strlen($hostname) - 1);
        }

        return false;
    }

    /**
     * Destructor
     */
    public function __destruct()
    {
        $this->stop();
    }

}
