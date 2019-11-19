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
namespace Pop\Mail\Message;

use Pop\Mail\Message;

/**
 * Abstract mail message class
 *
 * @category   Pop
 * @package    Pop\Mail
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2020 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    3.5.0
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
     * Message or part ID
     * @var string
     */
    protected $id = null;

    /**
     * Message or part ID header name
     * @var string
     */
    protected $idHeader = null;

    /**
     * Instantiate the message object
     *
     */
    public function __construct()
    {
        $this->generateId();
    }

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
    public function setCharSet($charSet = null)
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
     * @param  array $omitHeaders
     * @return string
     */
    public function getHeadersAsString(array $omitHeaders = [])
    {
        $headers = null;

        foreach ($this->headers as $header => $value) {
            if (!in_array($header, $omitHeaders) && !empty($value)) {
                $headers .= $header . ': ' . $value . Message::CRLF;
            }
        }

        if (null !== $this->id) {
            if (null === $this->idHeader) {
                $this->setIdHeader((($this instanceof Message) ? 'Message-ID' : 'Content-ID'));
            }

            if (!in_array($this->idHeader, $omitHeaders)) {
                $headers .= $this->idHeader . ': ' . $this->id . Message::CRLF;
            }
        }

        if ((null !== $this->contentType) && !in_array('Content-Type', $omitHeaders)) {
            $headers .= 'Content-Type: ' . $this->contentType;
            if (!empty($this->charSet)) {
                $headers .= '; charset="' . $this->charSet . '"';
            }
            $headers .= Message::CRLF;
        }

        return $headers;
    }

    /**
     * Set the ID header name
     *
     * @param  string $header
     * @return AbstractMessage
     */
    public function setIdHeader($header)
    {
        $this->idHeader = $header;
        return $this;
    }

    /**
     * Get the ID
     *
     * @return string
     */
    public function getIdHeader()
    {
        return $this->idHeader;
    }

    /**
     * Set the ID
     *
     * @param  string $id
     * @return AbstractMessage
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * Get the ID
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Generate a new ID
     *
     * @return string
     */
    public function generateId()
    {
        $this->setId($this->getRandomId());
        return $this->id;
    }

    /**
     * Returns a random ID
     *
     * @return string
     */
    protected function getRandomId()
    {
        $idLeft  = md5(getmypid().'.'.time().'.'.uniqid(mt_rand(), true));
        $idRight = !empty($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : 'popmail.generated';
        return $idLeft . '@' . $idRight;
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
     * Render as an array of lines
     *
     * @return array
     */
    abstract public function renderAsLines();

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