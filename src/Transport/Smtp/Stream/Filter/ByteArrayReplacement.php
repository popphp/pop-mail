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
 * Byte array replacement filter class
 *
 * @category   Pop
 * @package    Pop\Mail
 * @author     Chris Corbyn, from the SwiftMailer library https://github.com/swiftmailer/swiftmailer
 * @version    3.5.0
 */
class ByteArrayReplacement implements FilterInterface
{
    /**
     * The needle(s) to search for
     * @var array
     */
    private $search;

    /**
     * The replacement(s) to make
     * @var array
     */
    private $replace;

    /**
     * The Index for searching
     * @var int
     */
    private $index;

    /**
     * The Search Tree
     * @var array
     */
    private $tree = [];

    /**
     * Gives the size of the largest search
     * @var int
     */
    private $treeMaxLen = 0;

    /**
     * Replace size
     * @var array
     */
    private $repSize;

    /**
     * Create a new ByteArrayReplacementFilter with $search and $replace
     *
     * @param array $search
     * @param array $replace
     */
    public function __construct($search, $replace)
    {
        $this->search  = $search;
        $this->index   = [];
        $this->tree    = [];
        $this->replace = [];
        $this->repSize = [];

        $tree     = null;
        $i        = null;
        $lastSize = $size = 0;

        foreach ($search as $i => $search_element) {
            if ($tree !== null) {
                $tree[-1] = min(count($replace) - 1, $i - 1);
                $tree[-2] = $lastSize;
            }
            $tree = &$this->tree;
            if (is_array($search_element)) {
                foreach ($search_element as $k => $char) {
                    $this->index[$char] = true;
                    if (!isset($tree[$char])) {
                        $tree[$char] = [];
                    }
                    $tree = &$tree[$char];
                }
                $lastSize = $k + 1;
                $size     = max($size, $lastSize);
            } else {
                $lastSize = 1;
                if (!isset($tree[$search_element])) {
                    $tree[$search_element] = [];
                }
                $tree = &$tree[$search_element];
                $size = max($lastSize, $size);
                $this->index[$search_element] = true;
            }
        }
        if ($i !== null) {
            $tree[-1] = min(count($replace) - 1, $i);
            $tree[-2] = $lastSize;
            $this->treeMaxLen = $size;
        }
        foreach ($replace as $rep) {
            if (!is_array($rep)) {
                $rep = [$rep];
            }
            $this->replace[] = $rep;
        }
        for ($i = count($this->replace) - 1; $i >= 0; --$i) {
            $this->replace[$i] = $rep = $this->filter($this->replace[$i], $i);
            $this->repSize[$i] = count($rep);
        }
    }

    /**
     * Returns true if based on the buffer passed more bytes should be buffered
     *
     * @param array $buffer
     * @return bool
     */
    public function shouldBuffer($buffer)
    {
        $endOfBuffer = end($buffer);
        return isset($this->index[$endOfBuffer]);
    }

    /**
     * Perform the actual replacements on $buffer and return the result
     *
     * @param  array $buffer
     * @param  int   $minReplaces
     * @return array
     */
    public function filter($buffer, $minReplaces = -1)
    {
        if ($this->treeMaxLen == 0) {
            return $buffer;
        }

        $newBuffer = [];
        $buf_size = count($buffer);
        for ($i = 0; $i < $buf_size; ++$i) {
            $search_pos = $this->tree;
            $last_found = PHP_INT_MAX;
            // We try to find if the next byte is part of a search pattern
            for ($j = 0; $j <= $this->treeMaxLen; ++$j) {
                // We have a new byte for a search pattern
                if (isset($buffer [$p = $i + $j]) && isset($search_pos[$buffer[$p]])) {
                    $search_pos = $search_pos[$buffer[$p]];
                    // We have a complete pattern, save, in case we don't find a better match later
                    if (isset($search_pos[-1]) && $search_pos[-1] < $last_found
                        && $search_pos[-1] > $minReplaces) {
                        $last_found = $search_pos[-1];
                        $lastSize   = $search_pos[-2];
                    }
                }
                // We got a complete pattern
                elseif ($last_found !== PHP_INT_MAX) {
                    // Adding replacement datas to output buffer
                    $rep_size = $this->repSize[$last_found];
                    for ($j = 0; $j < $rep_size; ++$j) {
                        $newBuffer[] = $this->replace[$last_found][$j];
                    }
                    // We Move cursor forward
                    $i += $lastSize - 1;
                    // Edge Case, last position in buffer
                    if ($i >= $buf_size) {
                        $newBuffer[] = $buffer[$i];
                    }

                    // We start the next loop
                    continue 2;
                } else {
                    // this byte is not in a pattern and we haven't found another pattern
                    break;
                }
            }
            // Normal byte, move it to output buffer
            $newBuffer[] = $buffer[$i];
        }

        return $newBuffer;
    }
}
