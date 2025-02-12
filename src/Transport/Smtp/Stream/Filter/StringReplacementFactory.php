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
 * String replacement filter factory
 *
 * @category   Pop
 * @package    Pop\Mail
 * @author     Chris Corbyn, from the SwiftMailer library https://github.com/swiftmailer/swiftmailer
 * @version    4.0.4
 */
class StringReplacementFactory implements ReplacementFactoryInterface
{
    /**
     * Lazy-loaded filters
     * @var array
     */
    private array $filters = [];

    /**
     * Create a new StreamFilter to replace $search with $replace in a string
     *
     * @param  mixed $search
     * @param  mixed $replace
     * @return FilterInterface
     */
    public function createFilter(mixed $search, mixed $replace): FilterInterface
    {
        if (!isset($this->filters[$search][$replace])) {
            if (!isset($this->filters[$search])) {
                $this->filters[$search] = [];
            }

            if (!isset($this->filters[$search][$replace])) {
                $this->filters[$search][$replace] = [];
            }

            $this->filters[$search][$replace] = new StringReplacement($search, $replace);
        }

        return $this->filters[$search][$replace];
    }
}
