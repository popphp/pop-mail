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

use Pop\Mail\Transport\Smtp\Stream\FileInterface;

/**
 * File byte stream class
 *
 * @category   Pop
 * @package    Pop\Mail
 * @author     Chris Corbyn, from the SwiftMailer library https://github.com/swiftmailer/swiftmailer
 * @version    4.0.0
 */
class FileByteStream extends AbstractFilterableInputStream implements FileInterface
{

    /**
     * The internal pointer offset
     * @var int
     */
    private int $offset = 0;

    /**
     * The path to the file
     * @var string
     */
    private string $path;

    /**
     * The mode this file is opened in for writing
     * @var string
     */
    private string $mode;

    /**
     * A lazy-loaded resource handle for reading the file
     * @var mixed
     */
    private mixed $reader;

    /**
     * A lazy-loaded resource handle for writing the file
     * @var mixed
     */
    private mixed $writer;

    /** If stream is seekable true/false, or null if not known */
    private mixed $seekable = null;

    /**
     * Create a new FileByteStream for $path.
     *
     * @param  string $path
     * @param  bool $writable if true
     * @throws Exception
     */
    public function __construct(string $path, bool $writable = false)
    {
        if (empty($path)) {
            throw new Exception('The path cannot be empty');
        }
        $this->path = $path;
        $this->mode = $writable ? 'w+b' : 'rb';
    }

    /**
     * Get the complete path to the file
     *
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
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
     * @throws Exception
     * @return string|bool
     */
    public function read(int|string $length): string|bool
    {
        $fp = $this->getReadHandle();
        if (!feof($fp)) {
            $bytes = fread($fp, $length);
            $this->offset = ftell($fp);

            // If we read one byte after reaching the end of the file
            // feof() will return false and an empty string is returned
            if ($bytes === '' && feof($fp)) {
                $this->resetReadHandle();

                return false;
            }

            return $bytes;
        }

        $this->resetReadHandle();

        return false;
    }

    /**
     * Move the internal read pointer to $byteOffset in the stream
     *
     * @param  int|string $byteOffset
     * @return void
     */
    public function setReadPointer(int|string $byteOffset): void
    {
        if (isset($this->reader)) {
            $this->seekReadStreamToPosition($byteOffset);
        }
        $this->offset = $byteOffset;
    }

    /**
     * Just write the bytes to the file
     *
     * @param string $bytes
     * @return int
     */
    protected function commitBytes(string $bytes): int
    {
        fwrite($this->getWriteHandle(), $bytes);
        $this->resetReadHandle();

        return 0;
    }

    /**
     * Not used
     */
    protected function flush(): void
    {
    }

    /**
     * Get the resource for reading
     *
     * @throws Exception
     * @return mixed
     */
    private function getReadHandle(): mixed
    {
        if (!isset($this->reader)) {
            $pointer = @fopen($this->path, 'rb');
            if (!$pointer) {
                throw new Exception('Unable to open file for reading [' . $this->path . ']');
            }
            $this->reader = $pointer;
            if ($this->offset != 0) {
                $this->getReadStreamSeekableStatus();
                $this->seekReadStreamToPosition($this->offset);
            }
        }

        return $this->reader;
    }

    /**
     * Get the resource for writing
     *
     * @throws Exception
     * @return mixed
     */
    private function getWriteHandle(): mixed
    {
        if (!isset($this->writer)) {
            if (!$this->writer = fopen($this->path, $this->mode)) {
                throw new Exception('Unable to open file for writing [' . $this->path . ']');
            }
        }

        return $this->writer;
    }

    /**
     * Force a reload of the resource for reading
     */
    private function resetReadHandle(): void
    {
        if (isset($this->reader)) {
            fclose($this->reader);
            $this->reader = null;
        }
    }

    /**
     * Check if ReadOnly Stream is seekable
     */
    private function getReadStreamSeekableStatus(): void
    {
        $metas = stream_get_meta_data($this->reader);
        $this->seekable = $metas['seekable'];
    }

    /**
     * Streams in a readOnly stream ensuring copy if needed
     *
     * @param int|string $offset
     */
    private function seekReadStreamToPosition(int|string $offset): void
    {
        if ($this->seekable === null) {
            $this->getReadStreamSeekableStatus();
        }
        if ($this->seekable === false) {
            $currentPos = ftell($this->reader);
            if ($currentPos < $offset) {
                $toDiscard = $offset - $currentPos;
                fread($this->reader, $toDiscard);

                return;
            }
            $this->copyReadStream();
        }
        fseek($this->reader, $offset, SEEK_SET);
    }

    /**
     * Copy a readOnly Stream to ensure seekability
     */
    private function copyReadStream(): void
    {
        if ($tmpFile = fopen('php://temp/maxmemory:4096', 'w+b')) {
            /* We have opened a php:// Stream Should work without problem */
        } elseif (function_exists('sys_get_temp_dir') && is_writable(sys_get_temp_dir()) && ($tmpFile = tmpfile())) {
            /* We have opened a tmpfile */
        } else {
            throw new Exception('Unable to copy the file to make it seekable, sys_temp_dir is not writable, php://memory not available');
        }
        $currentPos = ftell($this->reader);
        fclose($this->reader);
        $source = fopen($this->path, 'rb');
        if (!$source) {
            throw new Exception('Unable to open file for copying ['.$this->path.']');
        }
        fseek($tmpFile, 0, SEEK_SET);
        while (!feof($source)) {
            fwrite($tmpFile, fread($source, 4096));
        }
        fseek($tmpFile, $currentPos, SEEK_SET);
        fclose($source);
        $this->reader = $tmpFile;
    }
}
