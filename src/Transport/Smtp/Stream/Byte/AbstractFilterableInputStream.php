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
namespace Pop\Mail\Transport\Smtp\Stream\Byte;

use Pop\Mail\Transport\Smtp\Stream\Filter\FilterableInterface;
use Pop\Mail\Transport\Smtp\Stream\FilterInterface;

/**
 * Abstract filterable input stream class
 *
 * @category   Pop
 * @package    Pop\Mail
 * @author     Chris Corbyn, from the SwiftMailer library https://github.com/swiftmailer/swiftmailer
 * @version    3.5.0
 */
abstract class AbstractFilterableInputStream implements InputInterface, FilterableInterface
{
    /**
     * Write sequence
     * @var int
     */
    protected $sequence = 0;

    /**
     * StreamFilters
     * @var array
     */
    private $filters = [];

    /**
     * A buffer for writing
     * @var string
     */
    private $writeBuffer = '';

    /**
     * Bound streams
     * @var array
     */
    private $mirrors = [];

    /**
     * Commit the given bytes to the storage medium immediately
     *
     * @param string $bytes
     */
    abstract protected function commitBytes($bytes);

    /**
     * Flush any buffers/content with immediate effect
     */
    abstract protected function flush();

    /**
     * Add a StreamFilter to this InputByteStream
     *
     * @param FilterInterface $filter
     * @param string          $key
     */
    public function addFilter(FilterInterface $filter, $key)
    {
        $this->filters[$key] = $filter;
    }

    /**
     * Remove an already present StreamFilter based on its $key
     *
     * @param string $key
     */
    public function removeFilter($key)
    {
        unset($this->filters[$key]);
    }

    /**
     * Writes $bytes to the end of the stream
     *
     * @param string $bytes
     * @return int
     */
    public function write($bytes)
    {
        $this->writeBuffer .= $bytes;
        foreach ($this->filters as $filter) {
            if ($filter->shouldBuffer($this->writeBuffer)) {
                return;
            }
        }
        $this->doWrite($this->writeBuffer);

        return ++$this->sequence;
    }

    /**
     * For any bytes that are currently buffered inside the stream,
     * force them off the buffer
     */
    public function commit()
    {
        $this->doWrite($this->writeBuffer);
    }

    /**
     * Attach $is to this stream.
     *
     * The stream acts as an observer, receiving all data that is written.
     * All {@link write()} and {@link flushBuffers()} operations will be mirrored.
     *
     * @param InputInterface $is
     */
    public function bind(InputInterface $is)
    {
        $this->mirrors[] = $is;
    }

    /**
     * Remove an already bound stream.
     *
     * If $is is not bound, no errors will be raised.
     * If the stream currently has any buffered data it will be written to $is
     * before unbinding occurs.
     *
     * @param InputInterface $is
     */
    public function unbind(InputInterface $is)
    {
        foreach ($this->mirrors as $k => $stream) {
            if ($is === $stream) {
                if ($this->writeBuffer !== '') {
                    $stream->write($this->writeBuffer);
                }
                unset($this->mirrors[$k]);
            }
        }
    }

    /**
     * Flush the contents of the stream (empty it) and set the internal pointer
     * to the beginning.
     */
    public function flushBuffers()
    {
        if ($this->writeBuffer !== '') {
            $this->doWrite($this->writeBuffer);
        }
        $this->flush();

        foreach ($this->mirrors as $stream) {
            $stream->flushBuffers();
        }
    }

    /**
     * Run $bytes through all filters
     *
     * @param  int $bytes
     * @return int
     */
    private function filter($bytes)
    {
        foreach ($this->filters as $filter) {
            $bytes = $filter->filter($bytes);
        }

        return $bytes;
    }

    /**
     * Just write the bytes to the stream
     *
     * @param int $bytes
     */
    private function doWrite($bytes)
    {
        $this->commitBytes($this->filter($bytes));

        foreach ($this->mirrors as $stream) {
            $stream->write($bytes);
        }

        $this->writeBuffer = '';
    }

}