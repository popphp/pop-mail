<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp-framework
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2019 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Pop\Mail\Message;

/**
 * Mail message interface
 *
 * @category   Pop
 * @package    Pop\Mail
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2019 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    3.1.0
 */
interface MessageInterface
{

    /**
     * Add header
     *
     * @param  string $header
     * @param  string $value
     * @return AbstractPart
     */
    public function addHeader($header, $value);

    /**
     * Add headers
     *
     * @param  array $headers
     * @return AbstractPart
     */
    public function addHeaders(array $headers);

    /**
     * Determine if message has header
     *
     * @param  string $header
     * @return boolean
     */
    public function hasHeader($header);

    /**
     * Get header
     *
     * @param  string $header
     * @return string
     */
    public function getHeader($header);

    /**
     * Get all headers
     *
     * @return array
     */
    public function getHeaders();

    /**
     * Get header as string
     *
     * @param  string $header
     * @return string
     */
    public function getHeaderAsString($header);

    /**
     * Get all headers as string
     *
     * @param  array $omit
     * @return string
     */
    public function getHeadersAsString(array $omit = []);

    /**
     * Get content type
     *
     * @return string
     */
    public function getContentType();

    /**
     * Get character set
     *
     * @return string
     */
    public function getCharSet();

    /**
     * Get body
     *
     * @return string
     */
    public function getBody();

    /**
     * Set content type
     *
     * @param  string $contentType
     * @return MessageInterface
     */
    public function setContentType($contentType);

    /**
     * Set character set
     *
     * @param  string $charSet
     * @return MessageInterface
     */
    public function setCharSet($charSet);

    /**
     * Render
     *
     * @return string
     */
    public function render();

    /**
     * Render as an array of lines
     *
     * @return array
     */
    public function renderAsLines();

    /**
     * Render to string
     *
     * @return string
     */
    public function __toString();

}