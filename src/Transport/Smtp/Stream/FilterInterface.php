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
namespace Pop\Mail\Transport\Smtp\Stream;

/**
 * Stream filter interface
 *
 * @category   Pop
 * @package    Pop\Mail
 * @author     Chris Corbyn, from the SwiftMailer library https://github.com/swiftmailer/swiftmailer
 * @version    4.0.4
 */
interface FilterInterface
{

    /**
     * Based on the buffer given, this returns true if more buffering is needed.
     *
     * @param  mixed $buffer
     * @return bool
     */
    public function shouldBuffer(mixed $buffer): bool;

    /**
     * Filters $buffer and returns the changes.
     *
     * @param  mixed $buffer
     * @return mixed
     */
    public function filter(mixed $buffer): mixed;

}
