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
 * Filterable interface
 *
 * @category   Pop
 * @package    Pop\Mail
 * @author     Chris Corbyn, from the SwiftMailer library https://github.com/swiftmailer/swiftmailer
 * @version    4.0.4
 */
interface FilterableInterface
{

    /**
     * Add a new StreamFilter, referenced by $key
     *
     * @param FilterInterface $filter
     * @param string                $key
     */
    public function addFilter(FilterInterface $filter, string $key): void;

    /**
     * Remove an existing filter using $key
     *
     * @param string $key
     */
    public function removeFilter(string $key): void;

}
