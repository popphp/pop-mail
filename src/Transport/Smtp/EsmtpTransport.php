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

use Pop\Mail\Transport\Smtp\Stream\BufferInterface;

/**
 * ESMTP transport class
 *
 * @category   Pop
 * @package    Pop\Mail
 * @author     Chris Corbyn, from the SwiftMailer library https://github.com/swiftmailer/swiftmailer
 * @version    4.0.4
 */
class EsmtpTransport extends AbstractSmtp implements AgentInterface
{

    /**
     * ESMTP extension handlers
     * @var array
     */
    private array $handlers = [];

    /**
     * ESMTP capabilities
     * @var array
     */
    private array $capabilities = [];

    /**
     * Connection buffer parameters
     * @var array
     */
    private array $params = [
        'protocol'               => 'tcp',
        'host'                   => 'localhost',
        'port'                   => 25,
        'timeout'                => 30,
        'blocking'               => 1,
        'tls'                    => false,
        'type'                   => BufferInterface::TYPE_SOCKET,
        'stream_context_options' => [],
    ];

    /**
     * Creates a new EsmtpTransport using the given I/O buffer
     *
     * @param ?BufferInterface $buffer
     * @param ?array           $handlers
     */
    public function __construct(?BufferInterface $buffer = null, ?array $handlers = null)
    {
        if ($buffer === null) {
            $buffer = new Stream\Buffer(new Stream\Filter\StringReplacementFactory());
        }
        if ($handlers === null) {
            $handlers  = [new AuthHandler([
                new Auth\CramMd5Authenticator(),
                new Auth\LoginAuthenticator(),
                new Auth\NTLMAuthenticator(),
                new Auth\PlainAuthenticator(),
                new Auth\XOAuth2Authenticator()
            ])];
        }
        parent::__construct($buffer);
        $this->setExtensionHandlers($handlers);
    }

    /**
     * Set the host to connect to
     *
     * @param  string $host
     * @return EsmtpTransport
     */
    public function setHost(string $host): EsmtpTransport
    {
        $this->params['host'] = $host;
        return $this;
    }

    /**
     * Get the host to connect to
     *
     * @return string
     */
    public function getHost(): string
    {
        return $this->params['host'];
    }

    /**
     * Set the port to connect to
     *
     * @param  int|string $port
     * @return EsmtpTransport
     */
    public function setPort(int|string $port): EsmtpTransport
    {
        $this->params['port'] = (int)$port;

        return $this;
    }

    /**
     * Get the port to connect to
     *
     * @return int
     */
    public function getPort(): int
    {
        return $this->params['port'];
    }

    /**
     * Set the connection timeout
     *
     * @param  int|string $timeout seconds
     * @return EsmtpTransport
     */
    public function setTimeout(int|string $timeout): EsmtpTransport
    {
        $this->params['timeout'] = (int) $timeout;
        $this->buffer->setParam('timeout', (int) $timeout);

        return $this;
    }

    /**
     * Get the connection timeout
     *
     * @return int
     */
    public function getTimeout(): int
    {
        return $this->params['timeout'];
    }

    /**
     * Set the encryption type (tls or ssl)
     *
     * @param  string $encryption
     * @return EsmtpTransport
     */
    public function setEncryption(string $encryption): EsmtpTransport
    {
        $encryption = strtolower($encryption);
        if ('tls' == $encryption) {
            $this->params['protocol'] = 'tcp';
            $this->params['tls'] = true;
        } else {
            $this->params['protocol'] = $encryption;
            $this->params['tls'] = false;
        }

        return $this;
    }

    /**
     * Get the encryption type
     *
     * @return string
     */
    public function getEncryption(): string
    {
        return $this->params['tls'] ? 'tls' : $this->params['protocol'];
    }

    /**
     * Sets the stream context options
     *
     * @param  array $options
     * @return EsmtpTransport
     */
    public function setStreamOptions(array $options): EsmtpTransport
    {
        $this->params['stream_context_options'] = $options;
        return $this;
    }

    /**
     * Returns the stream context options
     *
     * @return array
     */
    public function getStreamOptions(): array
    {
        return $this->params['stream_context_options'];
    }

    /**
     * Sets the source IP
     *
     * @param  string $source
     * @return EsmtpTransport
     */
    public function setSourceIp(string $source): EsmtpTransport
    {
        $this->params['sourceIp'] = $source;

        return $this;
    }

    /**
     * Returns the IP used to connect to the destination
     *
     * @return string|null
     */
    public function getSourceIp(): string|null
    {
        return $this->params['sourceIp'] ?? null;
    }

    /**
     * Set ESMTP extension handlers
     *
     * @param  array $handlers
     * @return EsmtpTransport
     */
    public function setExtensionHandlers(array $handlers): EsmtpTransport
    {
        $assoc = [];
        foreach ($handlers as $handler) {
            $assoc[$handler->getHandledKeyword()] = $handler;
        }

        @uasort($assoc, [$this, 'sortHandlers']);
        $this->handlers = $assoc;
        $this->setHandlerParams();

        return $this;
    }

    /**
     * Get ESMTP extension handlers
     *
     * @return array
     */
    public function getExtensionHandlers(): array
    {
        return array_values($this->handlers);
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
        $stopSignal = false;
        $response   = null;
        foreach ($this->getActiveHandlers() as $handler) {
            $response = $handler->onCommand($this, $command, $codes, $stopSignal);
            if ($stopSignal) {
                return $response;
            }
        }

        return parent::executeCommand($command, $codes);
    }

    // -- Mixin invocation code

    /**
     * Mixin handling method for ESMTP handlers
     *
     * @param  mixed $method
     * @param  mixed $args
     * @throws Exception
     * @return mixed
     */
    public function __call(mixed $method, mixed $args): mixed
    {
        foreach ($this->handlers as $handler) {
            if (method_exists($handler, $method)) {
                $return = call_user_func_array([$handler, $method], $args);
                // Allow fluid method calls
                if (is_null($return) && str_starts_with(strtolower((string)$method), 'set')) {
                    return $this;
                } else {
                    return $return;
                }
            } else {
                throw new Exception('Error: The method ' . $method . ' does not exist.');
            }
        }

        return null;
    }

    /**
     * Get the params to initialize the buffer
     *
     * @return array
     */
    protected function getBufferParams(): array
    {
        return $this->params;
    }

    /**
     * Overridden to perform EHLO instead
     *
     * @return void
     */
    protected function doHeloCommand(): void
    {
        try {
            $response = $this->executeCommand(sprintf("EHLO %s\r\n", $this->domain), [250]);
        } catch (Exception $e) {
            parent::doHeloCommand();
        }

        if ($this->params['tls']) {
            try {
                $this->executeCommand("STARTTLS\r\n", [220]);

                if (!$this->buffer->startTls()) {
                    throw new Exception('Unable to connect with TLS encryption');
                }

                try {
                    $response = $this->executeCommand(sprintf("EHLO %s\r\n", $this->domain), [250]);
                } catch (Exception $e) {
                    parent::doHeloCommand();
                }
            } catch (Exception $e) {
                $this->throwException($e);
            }
        }

        $this->capabilities = $this->getCapabilities($response);
        $this->setHandlerParams();
        foreach ($this->getActiveHandlers() as $handler) {
            $handler->afterEhlo($this);
        }
    }

    /**
     * Overridden to add Extension support
     *
     * @param  string $address
     * @return void
     */
    protected function doMailFromCommand(string $address): void
    {
        $handlers = $this->getActiveHandlers();
        $params = [];
        foreach ($handlers as $handler) {
            $params = array_merge($params, (array) $handler->getMailParams());
        }
        $paramStr = !empty($params) ? ' '.implode(' ', $params) : '';
        $this->executeCommand(sprintf("MAIL FROM:<%s>%s\r\n", $address, $paramStr), [250]);
    }

    /**
     * Overridden to add Extension support
     *
     * @param string $address
     */
    protected function doRcptToCommand(string $address): void
    {
        $handlers = $this->getActiveHandlers();
        $params = [];
        foreach ($handlers as $handler) {
            $params = array_merge($params, (array) $handler->getRcptParams());
        }
        $paramStr = !empty($params) ? ' '.implode(' ', $params) : '';
        $this->executeCommand(
            sprintf("RCPT TO:<%s>%s\r\n", $address, $paramStr), [250, 251, 252]
        );
    }

    /**
     * Determine ESMTP capabilities by function group
     *
     * @param  string $ehloResponse
     * @return array
     */
    private function getCapabilities(string $ehloResponse): array
    {
        $capabilities = [];
        $ehloResponse = trim($ehloResponse);
        $lines        = explode("\r\n", $ehloResponse);
        array_shift($lines);
        foreach ($lines as $line) {
            if (preg_match('/^[0-9]{3}[ -]([A-Z0-9-]+)((?:[ =].*)?)$/Di', $line, $matches)) {
                $keyword = strtoupper($matches[1]);
                $paramStr = strtoupper(ltrim($matches[2], ' ='));
                $params = !empty($paramStr) ? explode(' ', $paramStr) : [];
                $capabilities[$keyword] = $params;
            }
        }

        return $capabilities;
    }

    /**
     * Set parameters which are used by each extension handler
     */
    private function setHandlerParams(): void
    {
        foreach ($this->handlers as $keyword => $handler) {
            if (array_key_exists($keyword, $this->capabilities)) {
                $handler->setKeywordParams($this->capabilities[$keyword]);
            }
        }
    }

    /**
     * Get ESMTP handlers which are currently ok to use
     */
    private function getActiveHandlers(): array
    {
        $handlers = [];
        foreach ($this->handlers as $keyword => $handler) {
            if (array_key_exists($keyword, $this->capabilities)) {
                $handlers[] = $handler;
            }
        }

        return $handlers;
    }

    /**
     * Custom sort for extension handler ordering
     *
     * @param HandlerInterface $a
     * @param HandlerInterface $b
     * @return int
     */
    private function sortHandlers(HandlerInterface $a, HandlerInterface $b): int
    {
        return $a->getPriorityOver($b->getHandledKeyword());
    }
}
