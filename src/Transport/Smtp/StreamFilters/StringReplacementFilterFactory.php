<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp-framework
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2016 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Pop\Mail\Transport\Smtp\StreamFilters;

use Pop\Mail\Transport\Smtp\ReplacementFilterFactoryInterface;
use Pop\Mail\Transport\Smtp\StreamFilterInterface;

/**
 * Mail transport SMTP stream filters exception class
 *
 * @category   Pop
 * @package    Pop\Mail
 * @author     Chris Corbyn, from the SwiftMailer library https://github.com/swiftmailer/swiftmailer
 * @version    3.0.0
 */
class StringReplacementFilterFactory implements ReplacementFilterFactoryInterface
{
    /**
     * Lazy-loaded filters
     * @var array
     */
    private $filters = [];

    /**
     * Create a new StreamFilter to replace $search with $replace in a string
     *
     * @param  string $search
     * @param  string $replace
     * @return StreamFilterInterface
     */
    public function createFilter($search, $replace)
    {
        if (!isset($this->filters[$search][$replace])) {
            if (!isset($this->filters[$search])) {
                $this->filters[$search] = [];
            }

            if (!isset($this->filters[$search][$replace])) {
                $this->filters[$search][$replace] = [];
            }

            $this->filters[$search][$replace] = new StringReplacementFilter($search, $replace);
        }

        return $this->filters[$search][$replace];
    }
}
