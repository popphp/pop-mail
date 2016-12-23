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
namespace Pop\Mail\Message;

/**
 * Mail message part interface
 *
 * @category   Pop
 * @package    Pop\Mail
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2016 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    3.0.0
 */
interface PartInterface
{

    /**
     * Add message part header
     *
     * @param  string $header
     * @param  string $value
     * @return AbstractPart
     */
    public function addHeader($header, $value);

    /**
     * Add message part headers
     *
     * @param  array $headers
     * @return AbstractPart
     */
    public function addHeaders(array $headers);

    /**
     * Determine if message part has header
     *
     * @param  string $header
     * @return boolean
     */
    public function hasHeader($header);

    /**
     * Get message part header
     *
     * @param  string $header
     * @return string
     */
    public function getHeader($header);

    /**
     * Get all message part headers
     *
     * @return array
     */
    public function getHeaders();

    /**
     * Get message part content
     *
     * @return string
     */
    public function getContent();

    /**
     * Get message part content type
     *
     * @return string
     */
    public function getContentType();

    /**
     * Get message part character set
     *
     * @return string
     */
    public function getCharSet();

    /**
     * Set message part content
     *
     * @param  string $content
     * @return PartInterface
     */
    public function setContent($content);

    /**
     * Set message part content type
     *
     * @param  string $contentType
     * @return PartInterface
     */
    public function setContentType($contentType);

    /**
     * Set message part character set
     *
     * @param  string $charSet
     * @return PartInterface
     */
    public function setCharSet($charSet);

}
