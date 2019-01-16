<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp-framework
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2019 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Pop\Mail\Transport\Smtp\Stream;

/**
 * SMTP buffer interface
 *
 * @category   Pop
 * @package    Pop\Mail
 * @author     Chris Corbyn, from the SwiftMailer library https://github.com/swiftmailer/swiftmailer
 * @version    3.1.0
 */
interface BufferInterface
{

    /**
     * A socket buffer over TCP
     */
    const TYPE_SOCKET = 0x0001;

    /**
     * A process buffer with I/O support
     */
    const TYPE_PROCESS = 0x0010;

    /**
     * Perform any initialization needed, using the given $params.
     *
     * Parameters will vary depending upon the type of IoBuffer used.
     *
     * @param array $params
     */
    public function initialize(array $params);

    /**
     * Start TLS
     *
     * @return boolean
     */
    public function startTls();

    /**
     * Set an individual param on the buffer (e.g. switching to SSL).
     *
     * @param string $param
     * @param mixed  $value
     */
    public function setParam($param, $value);

    /**
     * Perform any shutdown logic needed.
     */
    public function terminate();

    /**
     * Set an array of string replacements which should be made on data written
     * to the buffer.
     *
     * This could replace LF with CRLF for example.
     *
     * @param array $replacements
     */
    public function setWriteTranslations(array $replacements);

    /**
     * Get a line of output (including any CRLF).
     *
     * The $sequence number comes from any writes and may or may not be used
     * depending upon the implementation.
     *
     * @param int $sequence of last write to scan from
     * @return string
     */
    public function readLine($sequence);

    /**
     * Writes $bytes to the end of the stream.
     *
     * Writing may not happen immediately if the stream chooses to buffer.  If
     * you want to write these bytes with immediate effect, call {@link commit()}
     * after calling write().
     *
     * This method returns the sequence ID of the write (i.e. 1 for first, 2 for
     * second, etc etc).
     *
     * @param  string $bytes
     * @throws Exception
     * @return int
     */
    public function write($bytes);

    /**
     * For any bytes that are currently buffered inside the stream, force them
     * off the buffer.
     *
     * @throws Exception
     */
    public function commit();

    /**
     * Attach $is to this stream.
     *
     * The stream acts as an observer, receiving all data that is written.
     * All {@link write()} and {@link flushBuffers()} operations will be mirrored.
     *
     * @param Byte\InputInterface $is
     */
    public function bind(Byte\InputInterface $is);

    /**
     * Remove an already bound stream.
     *
     * If $is is not bound, no errors will be raised.
     * If the stream currently has any buffered data it will be written to $is
     * before unbinding occurs.
     *
     * @param Byte\InputInterface $is
     */
    public function unbind(Byte\InputInterface $is);

    /**
     * Flush the contents of the stream (empty it) and set the internal pointer
     * to the beginning.
     *
     * @throws Exception
     */
    public function flushBuffers();

    /**
     * Reads $length bytes from the stream into a string and moves the pointer
     * through the stream by $length.
     *
     * If less bytes exist than are requested the remaining bytes are given instead.
     * If no bytes are remaining at all, boolean false is returned.
     *
     * @param  int $length
     * @throws Exception
     * @return string|bool
     */
    public function read($length);

    /**
     * Move the internal read pointer to $byteOffset in the stream.
     *
     * @param  int $byteOffset
     * @throws Exception
     * @return bool
     */
    public function setReadPointer($byteOffset);

}