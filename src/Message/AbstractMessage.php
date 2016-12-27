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

use Pop\Mail\Message;

/**
 * Abstract mail message class
 *
 * @category   Pop
 * @package    Pop\Mail
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2016 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    3.0.0
 */
abstract class AbstractMessage implements MessageInterface
{

    /**
     * Headers
     * @var array
     */
    protected $headers = [];

    /**
     * Content type
     * @var string
     */
    protected $contentType = 'text/plain';

    /**
     * Character set
     * @var string
     */
    protected $charSet = 'utf-8';

    /**
     * Add message part header
     *
     * @param  string $header
     * @param  string $value
     * @return AbstractMessage
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
     * @return AbstractMessage
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
     * Set message part content type
     *
     * @param  string $contentType
     * @return AbstractMessage
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
     * @return AbstractMessage
     */
    public function setCharSet($charSet)
    {
        $this->charSet = $charSet;
        return $this;
    }

    /**
     * Get header as string
     *
     * @param  string $header
     * @return string
     */
    public function getHeaderAsString($header)
    {
        return ($this->hasHeader($header)) ? $header . ': ' . $this->getHeader($header) : null;
    }

    /**
     * Get all message headers as string
     *
     * @param  array $omit
     * @return string
     */
    public function getHeadersAsString(array $omit = [])
    {
        $headers = null;
        $i       = 1;

        foreach ($this->headers as $header => $value) {
            if (!in_array($header, $omit)) {
                $headers .= $header . ': ' . $value . (($i < count($this->headers)) ? Message::CRLF : null);
            }
            $i++;
        }

        if ((null !== $this->contentType) && !in_array('Content-Type', $omit)) {
            $headers .= ((null !== $headers) ? Message::CRLF : null) . 'Content-Type: ' . $this->contentType;
            if (!empty($this->charSet)) {
                $headers .= '; charset="' . $this->charSet . '"';
            }
        }

        return $headers;
    }

    /**
     * Get body
     *
     * @return string
     */
    abstract public function getBody();

    /**
     * Render
     *
     * @return string
     */
    abstract public function render();

    /**
     * Render message to string
     *
     * @return string
     */
    public function __toString()
    {
        return $this->render();
    }

}