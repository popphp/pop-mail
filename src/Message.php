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
 * Mail message class
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
     * Subject
     * @var string
     */
    protected $subject = null;

    /**
     * Message text body
     * @var Message\Text
     */
    protected $text = null;

    /**
     * Message HTML body
     * @var Message\Html
     */
    protected $html = null;

    /**
     * Message attachments
     * @var array
     */
    protected $attachments = [];

    /**
     * Character set
     * @var string
     */
    protected $charset = 'utf-8';

    /**
     * MIME version
     * @var string
     */
    protected $mimeVersion = '1.0';

    /**
     * MIME type
     * @var string
     */
    protected $mimeType = 'multipart/mixed';

    /**
     * Boundary
     * @var string
     */
    protected $boundary = null;

    /**
     * Message
     * @var string
     */
    protected $body = null;

    /**
     * Constructor
     *
     * Instantiate the mail message object
     *
     * @param  string $subject
     */
    public function __construct($subject = null)
    {
        if (null !== $subject) {
            $this->setSubject($subject);
        }
    }

    /**
     * Set the subject
     *
     * @param  string $subject
     * @return Message
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;
        return $this;
    }

    /**
     * Set the text body
     *
     * @param  Message\Text $text
     * @return Message
     */
    public function setText(Message\Text $text)
    {
        $this->text = $text;
        return $this;
    }

    /**
     * Set the HTML body
     *
     * @param  Message\Html $html
     * @return Message
     */
    public function setHtml(Message\Html $html)
    {
        $this->html = $html;
        return $this;
    }

    /**
     * Set the charset
     *
     * @param  string $charset
     * @return Message
     */
    public function setCharset($charset)
    {
        $this->charset = $charset;
        return $this;
    }

    /**
     * Set the MIME version
     *
     * @param  string $version
     * @return Message
     */
    public function setMimeVersion($version)
    {
        $this->mimeVersion = $version;
        return $this;
    }

    /**
     * Set the MIME type
     *
     * @param  string $type
     * @return Message
     */
    public function setMimeType($type)
    {
        $this->mimeType = $type;
        return $this;
    }

    /**
     * Set the boundary
     *
     * @param  string $boundary
     * @return Message
     */
    public function setBoundary($boundary = null)
    {
        $this->boundary = (null !== $boundary) ? $boundary : sha1(time());
        return $this;
    }

    /**
     * Add an attachment
     *
     * @param  Message\Attachment $attachment
     * @return Message
     */
    public function addAttachment(Message\Attachment $attachment)
    {
        $this->attachments[] = $attachment;
        return $this;
    }

    /**
     * Add attachments
     *
     * @param  array $attachments
     * @return Message
     */
    public function addAttachments(array $attachments)
    {
        foreach ($attachments as $attachment) {
            $this->addAttachment($attachment);
        }
        return $this;
    }

    /**
     * Determine if the message object has a subject
     *
     * @return boolean
     */
    public function hasSubject()
    {
        return (null !== $this->subject);
    }

    /**
     * Determine if the message object has text content
     *
     * @return boolean
     */
    public function hasText()
    {
        return (null !== $this->text);
    }

    /**
     * Determine if the message object has HTML content
     *
     * @return boolean
     */
    public function hasHtml()
    {
        return (null !== $this->html);
    }

    /**
     * Determine if the message object has attachments
     *
     * @return boolean
     */
    public function hasAttachments()
    {
        return (count($this->attachments) > 0);
    }

    /**
     * Get the subject
     *
     * @return string
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * Get the text body
     *
     * @return Message\Text
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * Get the HTML body
     *
     * @return Message\Html
     */
    public function getHtml()
    {
        return $this->html;
    }

    /**
     * Get the charset
     *
     * @return string
     */
    public function getCharset()
    {
        return $this->charset;
    }

    /**
     * Get the MIME version
     *
     * @return string
     */
    public function getMimeVersion()
    {
        return $this->mimeVersion;
    }

    /**
     * Get the MIME type
     *
     * @return string
     */
    public function getMimeType()
    {
        return $this->mimeType;
    }

    /**
     * Get the boundary
     *
     * @return string
     */
    public function getBoundary()
    {
        return $this->boundary;
    }

    /**
     * Get the attachments
     *
     * @return array
     */
    public function getAttachments()
    {
        return $this->attachments;
    }

    /**
     * Get the message body
     *
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * Initialize message contain
     *
     * @throws Exception
     * @return void
     */
    public function initialize()
    {
        if ((null === $this->text) && (null === $this->html) && (count($this->attachments) == 0)) {
            throw new Exception('Error: There is no message body content.');
        }

        if (null === $this->boundary) {
            $this->setBoundary();
        }

        $closeBoundary = false;

        if (count($this->attachments) > 0) {
            foreach ($this->attachments as $attachment) {
                $this->body .= "\r\n--" . $this->boundary . "\r\n" . $attachment->getContent();
            }
            $this->mimeType = 'multipart/mixed';
            $closeBoundary  = true;
        }

        if (null !== $this->html) {
            $this->body .= '--' . $this->boundary . "\r\n" .
                'Content-type: text/html; charset=' . $this->getCharset() .
                "\r\n" . "\r\n" . $this->html->getContent() . "\r\n" . "\r\n";

            $this->mimeType = (count($this->attachments) > 0) ? 'multipart/mixed' : 'multipart/alternative';
            $closeBoundary  = true;
        }

        if (null !== $this->text) {
            if ((null === $this->html) && (count($this->attachments) == 0)) {
                $this->body .= $this->text->getContent() . "\r\n";
            } else {
                $this->body .= '--' . $this->boundary . "\r\n" .
                    'Content-type: text/plain; charset=' . $this->getCharset() .
                    "\r\n" . "\r\n" . $this->text->getContent() . "\r\n" . "\r\n";

                $closeBoundary = true;
            }
        }

        if ($closeBoundary) {
            $this->body .= '--' . $this->boundary . '--' . "\r\n" . "\r\n";
        }
    }

}
