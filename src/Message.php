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
namespace Pop\Mail;

/**
 * Message class
 *
 * @category   Pop
 * @package    Pop\Mail
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2016 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    3.0.0
 */
class Message
{

    /**
     * Message newline constant
     * @var string
     */
    const CRLF = "\r\n";

    /**
     * Message parts
     * @var array
     */
    protected $parts = [];

    /**
     * Message headers
     * @var array
     */
    protected $headers = [];

    /**
     * Message content type
     * @var string
     */
    protected $contentType = 'text/plain';

    /**
     * Message part character set
     * @var string
     */
    protected $charSet = 'utf-8';

    /**
     * Message boundary
     * @var string
     */
    protected $boundary = null;

    /**
     * Constructor
     *
     * Instantiate the message object
     *
     * @param  string $subject
     */
    public function __construct($subject)
    {
        $this->setSubject($subject);
    }

    /**
     * Set content type
     *
     * @param  string $contentType
     * @return Message
     */
    public function setContentType($contentType)
    {
        $this->contentType = $contentType;
        return $this;
    }

    /**
     * Get content type
     *
     * @return string
     */
    public function getContentType()
    {
        return $this->contentType;
    }

    /**
     * Set message character set
     *
     * @param  string $charSet
     * @return Message
     */
    public function setCharSet($charSet)
    {
        $this->charSet = $charSet;
        return $this;
    }

    /**
     * Get message character set
     *
     * @return string
     */
    public function getCharSet()
    {
        return $this->charSet;
    }

    /**
     * Set Subject
     *
     * @param  string $subject
     * @return Message
     */
    public function setSubject($subject)
    {
        return $this->addHeader('Subject', $subject);
    }

    /**
     * Set To
     *
     * @param  string $to
     * @return Message
     */
    public function setTo($to)
    {
        return $this->addHeader('To', $this->parseAddresses($to));
    }

    /**
     * Set CC
     *
     * @param  string $cc
     * @return Message
     */
    public function setCc($cc)
    {
        return $this->addHeader('CC', $this->parseAddresses($cc));
    }

    /**
     * Set BCC
     *
     * @param  string $bcc
     * @return Message
     */
    public function setBcc($bcc)
    {
        return $this->addHeader('BCC', $this->parseAddresses($bcc));
    }

    /**
     * Set From
     *
     * @param  string $from
     * @return Message
     */
    public function setFrom($from)
    {
        return $this->addHeader('From', $this->parseAddresses($from));
    }

    /**
     * Set Reply-To
     *
     * @param  string $replyTo
     * @return Message
     */
    public function setReplyTo($replyTo)
    {
        return $this->addHeader('Reply-To', $this->parseAddresses($replyTo));
    }

    /**
     * Set body
     *
     * @param  mixed $body
     * @return Message
     */
    public function setBody($body)
    {
        if (!($body instanceof Message\PartInterface)) {
            $body = new Message\Text($body);
        }
        return $this->addPart($body);
    }

    /**
     * Add message part
     *
     * @param  Message\PartInterface $part
     * @return Message
     */
    public function addPart(Message\PartInterface $part)
    {
        $this->parts[] = $part;
        $this->validateContentType();
        return $this;
    }

    /**
     * Add text message part
     *
     * @param  mixed $text
     * @return Message
     */
    public function addText($text)
    {
        if (!($text instanceof Message\Text) && is_string($text)) {
            $text = new Message\Text($text);
        }
        return $this->addPart($text);
    }

    /**
     * Add HTML message part
     *
     * @param  mixed $html
     * @return Message
     */
    public function addHtml($html)
    {
        if (!($html instanceof Message\Html) && is_string($html)) {
            $html = new Message\Html($html);
        }
        return $this->addPart($html);
    }

    /**
     * Attach file message part
     *
     * @param  mixed $file
     * @return Message
     */
    public function attachFile($file)
    {
        if (!($file instanceof Message\Attachment) && is_string($file)) {
            $file = new Message\Attachment($file);
        }
        return $this->addPart($file);
    }

    /**
     * Add message header
     *
     * @param  string $header
     * @param  string $value
     * @return Message
     */
    public function addHeader($header, $value)
    {
        $this->headers[$header] = $value;
        return $this;
    }

    /**
     * Add message headers
     *
     * @param  array $headers
     * @return Message
     */
    public function addHeaders(array $headers)
    {
        foreach ($headers as $header => $value) {
            $this->addHeader($header, $value);
        }
        return $this;
    }

    /**
     * Determine if message has header
     *
     * @param  string $header
     * @return boolean
     */
    public function hasHeader($header)
    {
        return isset($this->headers[$header]);
    }

    /**
     * Get subject
     *
     * @return string
     */
    public function getSubject()
    {
        return $this->getHeader('Subject');
    }

    /**
     * Get To
     *
     * @return string
     */
    public function getTo()
    {
        return $this->getHeader('To');
    }

    /**
     * Get message header
     *
     * @param  string $header
     * @return string
     */
    public function getHeader($header)
    {
        return (isset($this->headers[$header])) ? $this->headers[$header] : null;
    }

    /**
     * Get all message headers
     *
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
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
                $headers .= $header . ': ' . $value . (($i < count($this->headers)) ? self::CRLF : null);
            }
            $i++;
        }

        if (null !== $this->contentType) {
            $headers .= ((null !== $headers) ? self::CRLF : null) . 'Content-Type: ' . $this->contentType;
            if (null !== $this->charSet) {
                $headers .= '; charset="' . $this->charSet . '""';
            }
        }

        return $headers;
    }

    /**
     * Get message body
     *
     * @return string
     */
    public function getBody()
    {
        $body = null;

        if (count($this->parts) > 1) {
            foreach ($this->parts as $i => $part) {
                $body .= '--' . $this->getBoundary() . self::CRLF . $part;
            }
            $body .= '--' . $this->getBoundary() . '--' . self::CRLF . self::CRLF;
        } else if (count($this->parts) == 1) {
            $part = $this->parts[0];
            if ($part instanceof Message\Text) {
                $body .= $part->getBody() . self::CRLF . self::CRLF;
            } else {
                $body .= '--' . $this->getBoundary() . self::CRLF . $part;
                $body .= '--' . $this->getBoundary() . '--' . self::CRLF . self::CRLF;
            }
        }

        return $body;
    }

    /**
     * Get message MIME boundary
     *
     * @return string
     */
    public function getBoundary()
    {
        if (null === $this->boundary) {
            $this->generateBoundary();
        }
        return $this->boundary;
    }

    /**
     * Set message MIME boundary
     *
     * @param  string $boundary
     * @return Message
     */
    public function setBoundary($boundary)
    {
        $this->boundary = $boundary;
        $this->addHeader('MIME-Version', '1.0');
        return $this;
    }

    /**
     * Generate message MIME boundary
     *
     * @return Message
     */
    public function generateBoundary()
    {
        return $this->setBoundary(sha1(time()));
    }

    /**
     * Render message
     *
     * @return string
     */
    public function render()
    {
        return $this->getHeadersAsString() . self::CRLF . self::CRLF . $this->getBody();
    }

    /**
     * Render message to string
     *
     * @return string
     */
    public function __toString()
    {
        return $this->render();
    }

    /**
     * Parse addresses
     *
     * @param  mixed $addresses
     * @return string
     */
    protected function parseAddresses($addresses)
    {
        $result = [];

        if (is_array($addresses)) {
            foreach ($addresses as $key => $value) {
                // $key is email
                if (strpos($key, '@') !== false) {
                    $result[] = (!empty($value)) ? '"' . $value . '" <' . $key . '>' : $key;
                // $value is email
                } else if (strpos($value, '@') !== false) {
                    $result[] = (!empty($key)) ? '"' . $key . '" <' . $value . '>' : $value;
                }
            }
        } else {
            $result = [$addresses];
        }

        return implode(', ', $result);
    }

    /**
     * Validate content type based on message parts added to the message
     *
     * @return void
     */
    protected function validateContentType()
    {
        $hasFile = false;
        $hasHtml = false;

        foreach ($this->parts as $part) {
            if ($part instanceof Message\Attachment) {
                $hasFile = true;
            }
            if ($part instanceof Message\Html) {
                $hasHtml = true;
            }
        }

        if ($hasFile) {
            $this->setContentType('multipart/mixed; boundary="' . $this->getBoundary() . '"');
        } else if ((!$hasFile) && ($hasHtml)) {
            $this->setContentType('multipart/alternative; boundary="' . $this->getBoundary() . '"');
        }
    }

}
