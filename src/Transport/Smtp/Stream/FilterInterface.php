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
namespace Pop\Mail\Transport\Smtp\Stream;

/**
 * Stream filter interface
 *
 * @category   Pop
 * @package    Pop\Mail
 * @author     Chris Corbyn, from the SwiftMailer library https://github.com/swiftmailer/swiftmailer
 * @version    3.5.0
 */
interface FilterInterface
{

    /**
     * Based on the buffer given, this returns true if more buffering is needed.
     *
     * @param  mixed $buffer
     * @return bool
     */
    public function shouldBuffer($buffer);

    /**
     * Filters $buffer and returns the changes.
     *
     * @param  mixed $buffer
     * @return mixed
     */
    public function filter($buffer);

}