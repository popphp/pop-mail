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
 * Abstract mail message part class
 *
 * @category   Pop
 * @package    Pop\Mail
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2016 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    3.0.0
 */
abstract class AbstractPart implements PartInterface
{

    /**
     * Message part headers
     * @var array
     */
    protected $headers = [];

    /**
     * Message part content
     * @var string
     */
    protected $content = null;

    /**
     * Message part content type
     * @var string
     */
    protected $contentType = 'text/plain';

    /**
     * Message part character set
     * @var string
     */
    protected $charSet = 'utf-8';

    /**
     * Constructor
     *
     * Instantiate the message part object
     *
     * @param  string $content
     * @param  string $contentType
     */
    public function __construct($content, $contentType = 'text/plain')
    {
        $this->setContent($content);
        $this->setContentType($contentType);
    }

    /**
     * Add message part header
     *
     * @param  string $header
     * @param  string $value
     * @return AbstractPart
     */
    public function addHeader($header, $value)
    {
        $this->headers[$header] = $value;
        return $this;
    }

    /**
     * Add message part headers
     *
     * @param  array $headers
     * @return AbstractPart
     */
    public function addHeaders(array $headers)
    {
        foreach ($headers as $header => $value) {
            $this->addHeader($header, $value);
        }
        return $this;
    }

    /**
     * Determine if message part has header
     *
     * @param  string $header
     * @return boolean
     */
    public function hasHeader($header)
    {
        return isset($this->headers[$header]);
    }

    /**
     * Get message part header
     *
     * @param  string $header
     * @return string
     */
    public function getHeader($header)
    {
        return (isset($this->headers[$header])) ? $this->headers[$header] : null;
    }

    /**
     * Get all message part headers
     *
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * Get message part content
     *
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Get message part content type
     *
     * @return string
     */
    public function getContentType()
    {
        return $this->contentType;
    }

    /**
     * Get message part character set
     *
     * @return string
     */
    public function getCharSet()
    {
        return $this->charSet;
    }

    /**
     * Set message part content
     *
     * @param  string $content
     * @return AbstractPart
     */
    public function setContent($content)
    {
        $this->content = $content;
        return $this;
    }

    /**
     * Set message part content type
     *
     * @param  string $contentType
     * @return AbstractPart
     */
    public function setContentType($contentType)
    {
        $this->contentType = $contentType;
        return $this;
    }

    /**
     * Set message part character set
     *
     * @param  string $charSet
     * @return AbstractPart
     */
    public function setCharSet($charSet)
    {
        $this->charSet = $charSet;
        return $this;
    }

}
