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
        $this->addHeader('Subject', $subject);
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
     * Set To
     *
     * @param  string $to
     * @return Message
     */
    public function setTo($to)
    {
        return $this->addHeader('To', $this->parseRecipients($to));
    }

    /**
     * Set CC
     *
     * @param  string $cc
     * @return Message
     */
    public function setCc($cc)
    {
        return $this->addHeader('CC', $this->parseRecipients($cc));
    }

    /**
     * Set BCC
     *
     * @param  string $bcc
     * @return Message
     */
    public function setBcc($bcc)
    {
        return $this->addHeader('BCC', $this->parseRecipients($bcc));
    }

    /**
     * Set From
     *
     * @param  string $from
     * @return Message
     */
    public function setFrom($from)
    {
        return $this->addHeader('From', $this->parseRecipients($from));
    }

    /**
     * Set Reply-To
     *
     * @param  string $replyTo
     * @return Message
     */
    public function setReplyTo($replyTo)
    {
        return $this->addHeader('Reply-To', $this->parseRecipients($replyTo));
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
     * Attach file
     *
     * @param  Message\Attachment $file
     * @return Message
     */
    public function attach(Message\Attachment $file)
    {
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
     * Parse recipients
     *
     * @param  string $recipients
     * @return string
     */
    protected function parseRecipients($recipients)
    {
        $result = [];

        if (is_array($recipients)) {
            foreach ($recipients as $key => $value) {
                // $key is email
                if (strpos($key, '@') !== false) {
                    $result[] = (!empty($value)) ? '"' . $value . '" <' . $key . '>' : $key;
                // $value is email
                } else if (strpos($value, '@') !== false) {
                    $result[] = (!empty($key)) ? '"' . $key . '" <' . $value . '>' : $value;
                }
            }
        } else {
            $result = [$recipients];
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
