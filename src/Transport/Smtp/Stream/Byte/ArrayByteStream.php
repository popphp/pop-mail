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
namespace Pop\Mail\Transport\Smtp\Stream\Byte;

/**
 * Array byte stream class
 *
 * @category   Pop
 * @package    Pop\Mail
 * @author     Chris Corbyn, from the SwiftMailer library https://github.com/swiftmailer/swiftmailer
 * @version    4.0.0
 */
class ArrayByteStream implements InputInterface, OutputInterface
{
    /**
     * The internal stack of bytes
     * @var array
     */
    private array $array = [];

    /**
     * The size of the stack
     * @var int
     */
    private int $arraySize = 0;

    /**
     * The internal pointer offset
     * @var int
     */
    private int $offset = 0;

    /**
     * Bound streams
     * @var array
     */
    private array $mirrors = [];

    /**
     * Create a new ArrayByteStream.
     *
     * If $stack is given the stream will be populated with the bytes it contains.
     *
     * @param mixed $stack of bytes in string or array form, optional
     */
    public function __construct(mixed $stack = null)
    {
        if (is_array($stack)) {
            $this->array = $stack;
            $this->arraySize = count($stack);
        } elseif (is_string($stack)) {
            $this->write($stack);
        } else {
            $this->array = [];
        }
    }

    /**
     * Reads $length bytes from the stream into a string and moves the pointer
     * through the stream by $length.
     *
     * If less bytes exist than are requested the
     * remaining bytes are given instead. If no bytes are remaining at all, bool
     * false is returned.
     *
     * @param  int|string $length
     * @return string|bool
     */
    public function read(int|string $length): string|bool
    {
        if ($this->offset == $this->arraySize) {
            return false;
        }

        // Don't use array slice
        $end = $length + $this->offset;
        $end = $this->arraySize < $end ? $this->arraySize : $end;
        $ret = '';
        for (; $this->offset < $end; ++$this->offset) {
            $ret .= $this->array[$this->offset];
        }

        return $ret;
    }

    /**
     * Writes $bytes to the end of the stream
     *
     * @param  string $bytes
     * @return mixed
     */
    public function write(string $bytes): mixed
    {
        $to_add = str_split($bytes);
        foreach ($to_add as $value) {
            $this->array[] = $value;
        }
        $this->arraySize = count($this->array);

        foreach ($this->mirrors as $stream) {
            $stream->write($bytes);
        }

        return null;
    }

    /**
     * Not used
     */
    public function commit(): void
    {
    }

    /**
     * Attach $is to this stream.
     *
     * The stream acts as an observer, receiving all data that is written.
     * All {@link write()} and {@link flushBuffers()} operations will be mirrored.
     *
     * @param InputInterface $is
     */
    public function bind(InputInterface $is): void
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
    public function unbind(InputInterface $is): void
    {
        foreach ($this->mirrors as $k => $stream) {
            if ($is === $stream) {
                unset($this->mirrors[$k]);
            }
        }
    }

    /**
     * Move the internal read pointer to $byteOffset in the stream.
     *
     * @param  int|string $byteOffset
     * @return void
     */
    public function setReadPointer(int|string $byteOffset): void
    {
        if ($byteOffset > $this->arraySize) {
            $byteOffset = $this->arraySize;
        } elseif ($byteOffset < 0) {
            $byteOffset = 0;
        }

        $this->offset = $byteOffset;
    }

    /**
     * Flush the contents of the stream (empty it) and set the internal pointer
     * to the beginning.
     */
    public function flushBuffers(): void
    {
        $this->offset    = 0;
        $this->array     = [];
        $this->arraySize = 0;

        foreach ($this->mirrors as $stream) {
            $stream->flushBuffers();
        }
    }

}
