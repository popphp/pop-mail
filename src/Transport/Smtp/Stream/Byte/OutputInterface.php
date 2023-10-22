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
 * Output byte stream interface
 *
 * @category   Pop
 * @package    Pop\Mail
 * @author     Chris Corbyn, from the SwiftMailer library https://github.com/swiftmailer/swiftmailer
 * @version    4.0.0
 */
interface OutputInterface
{

    /**
     * Reads $length bytes from the stream into a string and moves the pointer
     * through the stream by $length.
     *
     * If less bytes exist than are requested the remaining bytes are given instead.
     * If no bytes are remaining at all, bool false is returned.
     *
     * @param  int $length
     * @throws Exception
     * @return string|bool
     */
    public function read(int $length): string|bool;

    /**
     * Move the internal read pointer to $byteOffset in the stream.
     *
     * @param  int $byteOffset
     * @throws Exception
     * @return void
     */
    public function setReadPointer(int $byteOffset): void;

}