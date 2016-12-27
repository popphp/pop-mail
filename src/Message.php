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
class Message extends Message\AbstractMessage
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
     * Message send To
     * @var array
     */
    protected $to = [];

    /**
     * Message send CC
     * @var array
     */
    protected $cc = [];

    /**
     * Message send BCC
     * @var array
     */
    protected $bcc = [];

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
        parent::__construct();
        $this->setSubject($subject);
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
        $this->to = array_unique(array_merge($this->to, $this->parseAddresses($to, true)));

        $to = (isset($this->headers['To'])) ?
            $this->getHeader('To') . ', ' . $this->parseAddresses($to) :
            $this->parseAddresses($to);

        return $this->addHeader('To', $to);
    }

    /**
     * Set CC
     *
     * @param  string $cc
     * @return Message
     */
    public function setCc($cc)
    {
        $this->cc = array_unique(array_merge($this->cc, $this->parseAddresses($cc, true)));

        $cc = (isset($this->headers['CC'])) ?
            $this->getHeader('CC') . ', ' . $this->parseAddresses($cc) :
            $this->parseAddresses($cc);

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
        $this->bcc = array_unique(array_merge($this->bcc, $this->parseAddresses($bcc, true)));

        $bcc = (isset($this->headers['BCC'])) ?
            $this->getHeader('BCC') . ', ' . $this->parseAddresses($bcc) :
            $this->parseAddresses($bcc);

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
     * Remove header
     *
     * @param  string $header
     * @return Message
     */
    public function removeHeader($header)
    {
        if (isset($this->headers[$header])) {
            unset($this->headers[$header]);
        }
        return $this;
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
     * @return array
     */
    public function getTo()
    {
        return $this->to;
    }

    /**
     * Get CC
     *
     * @return array
     */
    public function getCc()
    {
        return $this->cc;
    }

    /**
     * Get BCC
     *
     * @return array
     */
    public function getBcc()
    {
        return $this->bcc;
    }

    /**
     * Get message body
     *
     * @return string
     */
    public function getBody()
    {
        $body  = null;

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
     * Render as an array of lines
     *
     * @return array
     */
    public function renderAsLines()
    {
        $lines = [];

        $headers = explode(Message::CRLF, $this->getHeadersAsString());
        $body    = explode("\n", $this->getBody());

        foreach ($headers as $header) {
            $lines[] = trim($header);
        }

        if (count($lines) > 0) {
            $lines[] = Message::CRLF;
            $lines[] = Message::CRLF;
        }

        foreach ($body as $line) {
            $lines[] = trim($line);
        }

        return $lines;
    }

    /**
     * Write this entire entity to a {@see Swift\InputByteStream}.
     *
     * @param Transport\Smtp\InputByteStreamInterface $is
     */
    public function toByteStream(Transport\Smtp\InputByteStreamInterface $is)
    {
        $is->write($this->getHeadersAsString());
        $is->commit();
        $this->bodyToByteStream($is);
    }

    /**
     * Write this entire entity to a {@link Swift\InputByteStream}.
     *
     * @param Transport\Smtp\InputByteStreamInterface $is
     */
    protected function bodyToByteStream(Transport\Smtp\InputByteStreamInterface $is)
    {
        $lines = $this->renderAsLines();
        foreach ($lines as $line) {
            $is->write($line . self::CRLF);
        }
        /*
        if (empty($this->immediateChildren)) {
            if (isset($this->body)) {
                if ($this->cache->hasKey($this->cacheKey, 'body')) {
                    $this->cache->exportToByteStream($this->cacheKey, 'body', $is);
                } else {
                    $cacheIs = $this->cache->getInputByteStream($this->cacheKey, 'body');
                    if ($cacheIs) {
                        $is->bind($cacheIs);
                    }
                    $is->write("\r\n");
                    if ($this->body instanceof \Swift\OutputByteStream) {
                        $this->body->setReadPointer(0);
                        $this->encoder->encodeByteStream($this->body, $is, 0, $this->getMaxLineLength());
                    } else {
                        $is->write($this->encoder->encodeString($this->getBody(), 0, $this->getMaxLineLength()));
                    }
                    if ($cacheIs) {
                        $is->unbind($cacheIs);
                    }
                }
            }
        }
        if (!empty($this->immediateChildren)) {
            foreach ($this->immediateChildren as $child) {
                $is->write("\r\n\r\n--".$this->getBoundary()."\r\n");
                $child->toByteStream($is);
            }
            $is->write("\r\n\r\n--".$this->getBoundary()."--\r\n");
        }
        */
    }

    /**
     * Parse addresses
     *
     * @param  mixed   $addresses
     * @param  boolean $asArray
     * @return string
     */
    protected function parseAddresses($addresses, $asArray = false)
    {
        $formatted = [];
        $emails    = [];

        if (is_array($addresses)) {
            foreach ($addresses as $key => $value) {
                // $key is email
                if (strpos($key, '@') !== false) {
                    $formatted[] = (!empty($value)) ? '"' . $value . '" <' . $key . '>' : $key;
                    $emails[]    = $key;
                // $value is email
                } else if (strpos($value, '@') !== false) {
                    $formatted[] = (!empty($key)) ? '"' . $key . '" <' . $value . '>' : $value;
                    $emails[]    = $value;
                }
            }
        } else {
            $formatted = [$addresses];
            $emails    = [$addresses];
        }

        return ($asArray) ? $emails : implode(', ', $formatted);
    }

    /**
     * Validate content type based on message parts added to the message
     *
     * @return void
     */
    protected function validateContentType()
    {
        $hasText = false;
        $hasHtml = false;
        $hasFile = false;

        foreach ($this->parts as $part) {
            if ($part instanceof Message\Text) {
                $hasText = true;
            }
            if ($part instanceof Message\Html) {
                $hasHtml = true;
            }
            if ($part instanceof Message\Attachment) {
                $hasFile = true;
            }
        }

        if (($hasText) && ($hasHtml)) {
            $this->setContentType(
                'multipart/alternative; boundary="' . $this->getBoundary() . '"' . self::CRLF .
                'This is a multi-part message in MIME format.'
            );
            $this->setCharSet('');
        } else if ($hasFile) {
            $this->setContentType(
                'multipart/mixed; boundary="' . $this->getBoundary() . '"' . self::CRLF .
                'This is a multi-part message in MIME format.'
            );
            $this->setCharSet('');
        }
    }

}
