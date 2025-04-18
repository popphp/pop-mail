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
namespace Pop\Mail\Transport\Smtp\Stream\Filter;

use Pop\Mail\Transport\Smtp\Stream\FilterInterface;

/**
 * String replacement filter
 *
 * @category   Pop
 * @package    Pop\Mail
 * @author     Chris Corbyn, from the SwiftMailer library https://github.com/swiftmailer/swiftmailer
 * @version    4.0.4
 */
class StringReplacement implements FilterInterface
{
    /**
     * The needle(s) to search for
     * @var string|array
     */
    private string|array $search;

    /**
     * The replacement(s) to make
     * @var string|array
     */
    private string|array $replace;

    /**
     * Create a new StringReplacementFilter with $search and $replace
     *
     * @param string|array $search
     * @param string|array $replace
     */
    public function __construct(string|array $search, string|array $replace)
    {
        $this->search  = $search;
        $this->replace = $replace;
    }

    /**
     * Returns true if based on the buffer passed more bytes should be buffered
     *
     * @param  mixed $buffer
     * @return bool
     */
    public function shouldBuffer(mixed $buffer): bool
    {
        $endOfBuffer = substr($buffer, -1);
        foreach ((array) $this->search as $needle) {
            if (str_contains($needle, $endOfBuffer)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Perform the actual replacements on $buffer and return the result
     *
     * @param  mixed $buffer
     * @return mixed
     */
    public function filter(mixed $buffer): mixed
    {
        return str_replace($this->search, $this->replace, $buffer);
    }
}
