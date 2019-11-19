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
namespace Pop\Mail\Transport\Smtp;

/**
 * ESMTP transport class
 *
 * @category   Pop
 * @package    Pop\Mail
 * @author     Chris Corbyn, from the SwiftMailer library https://github.com/swiftmailer/swiftmailer
 * @version    3.5.0
 */
class EsmtpTransport extends AbstractSmtp implements AgentInterface
{

    /**
     * ESMTP extension handlers
     * @var array
     */
    private $handlers = [];

    /**
     * ESMTP capabilities
     * @var array
     */
    private $capabilities = [];

    /**
     * Connection buffer parameters
     * @var array
     */
    private $params = [
        'protocol'               => 'tcp',
        'host'                   => 'localhost',
        'port'                   => 25,
        'timeout'                => 30,
        'blocking'               => 1,
        'tls'                    => false,
        'type'                   => Stream\BufferInterface::TYPE_SOCKET,
        'stream_context_options' => [],
    ];

    /**
     * Creates a new EsmtpTransport using the given I/O buffer
     *
     * @param Stream\BufferInterface $buffer
     * @param array                  $handlers
     */
    public function __construct(Stream\BufferInterface $buffer = null, array $handlers = null)
    {
        if (null === $buffer) {
            $buffer = new Stream\Buffer(new Stream\Filter\StringReplacementFactory());
        }
        if (null === $handlers) {
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
    public function setHost($host)
    {
        $this->params['host'] = $host;
        return $this;
    }

    /**
     * Get the host to connect to
     *
     * @return string
     */
    public function getHost()
    {
        return $this->params['host'];
    }

    /**
     * Set the port to connect to
     *
     * @param  int $port
     * @return EsmtpTransport
     */
    public function setPort($port)
    {
        $this->params['port'] = (int)$port;

        return $this;
    }

    /**
     * Get the port to connect to
     *
     * @return int
     */
    public function getPort()
    {
        return $this->params['port'];
    }

    /**
     * Set the connection timeout
     *
     * @param  int $timeout seconds
     * @return EsmtpTransport
     */
    public function setTimeout($timeout)
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
    public function getTimeout()
    {
        return $this->params['timeout'];
    }

    /**
     * Set the encryption type (tls or ssl)
     *
     * @param string $encryption
     * @return EsmtpTransport
     */
    public function setEncryption($encryption)
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
    public function getEncryption()
    {
        return $this->params['tls'] ? 'tls' : $this->params['protocol'];
    }

    /**
     * Sets the stream context options
     *
     * @param  array $options
     * @return EsmtpTransport
     */
    public function setStreamOptions($options)
    {
        $this->params['stream_context_options'] = $options;
        return $this;
    }

    /**
     * Returns the stream context options
     *
     * @return array
     */
    public function getStreamOptions()
    {
        return $this->params['stream_context_options'];
    }

    /**
     * Sets the source IP
     *
     * @param string $source
     * @return EsmtpTransport
     */
    public function setSourceIp($source)
    {
        $this->params['sourceIp'] = $source;

        return $this;
    }

    /**
     * Returns the IP used to connect to the destination
     *
     * @return string
     */
    public function getSourceIp()
    {
        return isset($this->params['sourceIp']) ? $this->params['sourceIp'] : null;
    }

    /**
     * Set ESMTP extension handlers
     *
     * @param  array $handlers
     * @return EsmtpTransport
     */
    public function setExtensionHandlers(array $handlers)
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
    public function getExtensionHandlers()
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
     * @param  int[]  $codes
     * @return string
     */
    public function executeCommand($command, $codes = [])
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
     * @param  $method
     * @param  $args
     * @throws Exception
     * @return mixed
     */
    public function __call($method, $args)
    {
        foreach ($this->handlers as $handler) {
            if (method_exists($handler, $method)) {
                $return = call_user_func_array([$handler, $method], $args);
                // Allow fluid method calls
                if (is_null($return) && substr(strtolower($method), 0, 3) == 'set') {
                    return $this;
                } else {
                    return $return;
                }
            } else {
                throw new Exception('Error: The method ' . $method . ' does not exist.');
            }
        }
    }

    /**
     * Get the params to initialize the buffer
     *
     * @return array
     */
    protected function getBufferParams()
    {
        return $this->params;
    }

    /**
     * Overridden to perform EHLO instead
     *
     * @return mixed
     */
    protected function doHeloCommand()
    {
        try {
            $response = $this->executeCommand(sprintf("EHLO %s\r\n", $this->domain), [250]);
        } catch (Exception $e) {
            return parent::doHeloCommand();
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
                    return parent::doHeloCommand();
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
     * @param string $address
     */
    protected function doMailFromCommand($address)
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
    protected function doRcptToCommand($address)
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
    private function getCapabilities($ehloResponse)
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
    private function setHandlerParams()
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
    private function getActiveHandlers()
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
    private function sortHandlers(HandlerInterface $a, HandlerInterface $b)
    {
        return $a->getPriorityOver($b->getHandledKeyword());
    }
}
