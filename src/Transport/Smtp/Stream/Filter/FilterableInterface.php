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
namespace Pop\Mail\Transport\Smtp\Stream\Filter;

use Pop\Mail\Transport\Smtp\Stream\FilterInterface;

/**
 * Filterable interface
 *
 * @category   Pop
 * @package    Pop\Mail
 * @author     Chris Corbyn, from the SwiftMailer library https://github.com/swiftmailer/swiftmailer
 * @version    3.5.0
 */
interface FilterableInterface
{

    /**
     * Add a new StreamFilter, referenced by $key
     *
     * @param FilterInterface $filter
     * @param string                $key
     */
    public function addFilter(FilterInterface $filter, $key);

    /**
     * Remove an existing filter using $key
     *
     * @param string $key
     */
    public function removeFilter($key);

}