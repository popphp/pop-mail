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
namespace Pop\Mail\Transport\Smtp\Stream;

/**
 * Stream buffer class
 *
 * @category   Pop
 * @package    Pop\Mail
 * @author     Chris Corbyn, from the SwiftMailer library https://github.com/swiftmailer/swiftmailer
 * @version    4.0.0
 */
class Buffer extends Byte\AbstractFilterableInputStream implements BufferInterface
{

    /**
     * A primary socket
     * @var mixed
     */
    private mixed $stream;

    /**
     * The input stream
     * @var mixed
     */
    private mixed $in;

    /**
     * The output stream
     * @var mixed
     */
    private mixed $out;

    /**
     * Buffer initialization parameters
     * $var array
     */
    private array $params = [];

    /**
     * The ReplacementFilterFactory
     * @var Filter\ReplacementFactoryInterface
     */
    private Filter\ReplacementFactoryInterface $replacementFactory;

    /**
     * Translations performed on data being streamed into the buffer
     * @var array
     */
    private array $translations = [];

    /**
     * Create a new StreamBuffer using $replacementFactory for transformations.
     *
     * @param Filter\ReplacementFactoryInterface $replacementFactory
     */
    public function __construct(Filter\ReplacementFactoryInterface $replacementFactory)
    {
        $this->replacementFactory = $replacementFactory;
    }

    /**
     * Perform any initialization needed, using the given $params.
     *
     * Parameters will vary depending upon the type of IoBuffer used.
     *
     * @param array $params
     */
    public function initialize(array $params): void
    {
        $this->params = $params;
        switch ($params['type']) {
            case self::TYPE_PROCESS:
                $this->establishProcessConnection();
                break;
            case self::TYPE_SOCKET:
            default:
                $this->establishSocketConnection();
                break;
        }
    }

    /**
     * Set an individual param on the buffer (e.g. switching to SSL)
     *
     * @param string $param
     * @param mixed  $value
     */
    public function setParam(string $param, mixed $value): void
    {
        if (isset($this->stream)) {
            switch ($param) {
                case 'timeout':
                    if ($this->stream) {
                        stream_set_timeout($this->stream, $value);
                    }
                    break;

                case 'blocking':
                    if ($this->stream) {
                        stream_set_blocking($this->stream, 1);
                    }

            }
        }
        $this->params[$param] = $value;
    }

    /**
     * Start TLS
     *
     * @return bool
     */
    public function startTls(): bool
    {
        return stream_socket_enable_crypto($this->stream, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
    }

    /**
     * Perform any shutdown logic needed
     */
    public function terminate(): void
    {
        if (isset($this->stream)) {
            switch ($this->params['type']) {
                case self::TYPE_PROCESS:
                    fclose($this->in);
                    fclose($this->out);
                    proc_close($this->stream);
                    break;
                case self::TYPE_SOCKET:
                default:
                    fclose($this->stream);
                    break;
            }
        }
        $this->stream = null;
        $this->out    = null;
        $this->in     = null;
    }

    /**
     * Set an array of string replacements which should be made on data written
     * to the buffer.
     *
     * This could replace LF with CRLF for example.
     *
     * @param array $replacements
     */
    public function setWriteTranslations(array $replacements): void
    {
        foreach ($this->translations as $search => $replace) {
            if (!isset($replacements[$search])) {
                $this->removeFilter($search);
                unset($this->translations[$search]);
            }
        }

        foreach ($replacements as $search => $replace) {
            if (!isset($this->translations[$search])) {
                $this->addFilter(
                    $this->replacementFactory->createFilter($search, $replace), $search
                );
                $this->translations[$search] = true;
            }
        }
    }

    /**
     * Get a line of output (including any CRLF).
     *
     * The $sequence number comes from any writes and may or may not be used
     * depending upon the implementation.
     *
     * @param  int|string $sequence of last write to scan from
     * @throws Exception
     * @return string
     */
    public function readLine(int|string $sequence): string
    {
        $line = '';

        if (isset($this->out) && !feof($this->out)) {
            $line = fgets($this->out);
            if (strlen($line) == 0) {
                $metas = stream_get_meta_data($this->out);
                if (isset($metas['timedout']) && ($metas['timedout'])) {
                    throw new Exception('Connection to ' . $this->getReadConnectionDescription() . ' Timed Out');
                }
            }
        }

        return $line;
    }

    /**
     * Reads $length bytes from the stream into a string and moves the pointer
     * through the stream by $length.
     *
     * If less bytes exist than are requested the remaining bytes are given instead.
     * If no bytes are remaining at all, bool false is returned.
     *
     * @param  int|string $length
     * @throws Exception
     * @return string|bool
     */
    public function read(int|string $length): string|bool
    {
        $ret = '';

        if (isset($this->out) && !feof($this->out)) {
            $ret = fread($this->out, $length);
            if (strlen($ret) == 0) {
                $metas = stream_get_meta_data($this->out);
                if ($metas['timedout']) {
                    throw new Exception('Connection to ' . $this->getReadConnectionDescription() . ' Timed Out');
                }
            }
        }

        return $ret;
    }

    /**
     * Not implemented
     *
     * @param  int|string $byteOffset
     * @return void
     */
    public function setReadPointer(int|string $byteOffset): void
    {
    }

    /**
     * Flush the stream contents
     */
    protected function flush(): void
    {
        if (isset($this->in)) {
            fflush($this->in);
        }
    }

    /**
     * Write bytes to the stream
     *
     * @param  string $bytes
     * @return int
     */
    protected function commitBytes(string $bytes): int
    {
        if (isset($this->in)) {
            $bytesToWrite = strlen($bytes);
            $totalBytesWritten = 0;

            while ($totalBytesWritten < $bytesToWrite) {
                $bytesWritten = fwrite($this->in, substr($bytes, $totalBytesWritten));
                if (false === $bytesWritten || 0 === $bytesWritten) {
                    break;
                }

                $totalBytesWritten += $bytesWritten;
            }

            if ($totalBytesWritten > 0) {
                return ++$this->sequence;
            }
        }
    }

    /**
     * Establishes a connection to a remote server
     */
    private function establishSocketConnection(): void
    {
        $host = $this->params['host'];
        if (!empty($this->params['protocol'])) {
            $host = $this->params['protocol'].'://'.$host;
        }
        $timeout = 15;
        if (!empty($this->params['timeout'])) {
            $timeout = $this->params['timeout'];
        }
        $options = [];
        if (!empty($this->params['sourceIp'])) {
            $options['socket']['bindto'] = $this->params['sourceIp'].':0';
        }
        if (isset($this->params['stream_context_options'])) {
            $options = array_merge($options, $this->params['stream_context_options']);
        }
        $streamContext = stream_context_create($options);
        $this->stream  = @stream_socket_client($host.':'.$this->params['port'], $errno, $errstr, $timeout, STREAM_CLIENT_CONNECT, $streamContext);
        if (false === $this->stream) {
            throw new Exception('Connection could not be established with host '.$this->params['host'] . ' [' . $errstr . ' #' . $errno . ']');
        }
        if (!empty($this->params['blocking'])) {
            stream_set_blocking($this->stream, 1);
        } else {
            stream_set_blocking($this->stream, 0);
        }
        stream_set_timeout($this->stream, $timeout);
        $this->in  = &$this->stream;
        $this->out = &$this->stream;
    }

    /**
     * Opens a process for input/output
     */
    private function establishProcessConnection(): void
    {
        $command = $this->params['command'];
        $descriptorSpec = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];
        $this->stream = proc_open($command, $descriptorSpec, $pipes);
        stream_set_blocking($pipes[2], 0);
        if ($err = stream_get_contents($pipes[2])) {
            throw new Exception(
                'Process could not be started ['.$err.']'
            );
        }
        $this->in  = &$pipes[0];
        $this->out = &$pipes[1];
    }

    /**
     * Get read connection description
     *
     * @return string
     */
    private function getReadConnectionDescription(): string
    {
        switch ($this->params['type']) {
            case self::TYPE_PROCESS:
                return 'Process ' . $this->params['command'];
                break;

            case self::TYPE_SOCKET:
            default:
                $host = $this->params['host'];
                if (!empty($this->params['protocol'])) {
                    $host = $this->params['protocol'] . '://' . $host;
                }
                $host .= ':' . $this->params['port'];

                return $host;
                break;
        }
    }

}