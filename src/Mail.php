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
 * Mail class
 *
 * @category   Pop
 * @package    Pop_Mail
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2016 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    3.0.0
 */
class Mail
{

    /**
     * Mail headers
     * @var array
     */
    protected $headers = [];

    /**
     * TO: queue
     * @var Queue
     */
    protected $to = null;

    /**
     * CC: queue
     * @var Queue
     */
    protected $cc = null;

    /**
     * BCC: queue
     * @var Queue
     */
    protected $bcc = null;

    /**
     * Message object
     * @var Message
     */
    protected $message = null;

    /**
     * Message transport
     * @var Transport\AbstractTransport
     */
    protected $transport = null;

    /**
     * Mail header string
     * @var string
     */
    protected $headerString = null;

    /**
     * Send as group flag
     * @var boolean
     */
    protected $group = false;

    /**
     * Constructor
     *
     * Instantiate the mail object
     *
     * @return Mail
     */
    public function __construct()
    {

    }

    /**
     * Add a To address
     *
     * @param  string $email
     * @param  string $name
     * @return Mail
     */
    public function to($email, $name = null)
    {
        if (null === $this->to) {
            $this->to = new Queue($email, $name);
        } else {
            $this->to->addRecipient($email, $name);
        }
        return $this;
    }

    /**
     * Add a CC address
     *
     * @param  string $email
     * @param  string $name
     * @return Mail
     */
    public function cc($email, $name = null)
    {
        if (null === $this->cc) {
            $this->cc = new Queue($email, $name);
        } else {
            $this->cc->addRecipient($email, $name);
        }
        return $this;
    }

    /**
     * Add a BCC address
     *
     * @param  string $email
     * @param  string $name
     * @return Mail
     */
    public function bcc($email, $name = null)
    {
        if (null === $this->bcc) {
            $this->bcc = new Queue($email, $name);
        } else {
            $this->bcc->addRecipient($email, $name);
        }
        return $this;
    }

    /**
     * Add a From address
     *
     * @param  string  $email
     * @param  string  $name
     * @param  boolean $replyTo
     * @return Mail
     */
    public function from($email, $name = null, $replyTo = true)
    {
        $from = (null !== $name) ? $name . ' <' . $email . '>' : $email;
        $this->setHeader('From', $from);
        if ($replyTo) {
            $this->setHeader('Reply-To', $from);
        }
        return $this;
    }

    /**
     * Add a Reply-To address
     *
     * @param  string  $email
     * @param  string  $name
     * @param  boolean $from
     * @return Mail
     */
    public function replyTo($email, $name = null, $from = true)
    {
        $replyTo = (null !== $name) ? $name . ' <' . $email . '>' : $email;
        $this->setHeader('Reply-To', $replyTo);
        if ($from) {
            $this->setHeader('From', $replyTo);
        }
        return $this;
    }

    /**
     * Set a mail header
     *
     * @param  string $name
     * @param  string $value
     * @return Mail
     */
    public function setHeader($name, $value)
    {
        $this->headers[$name] = $value;
        return $this;
    }

    /**
     * Set mail headers
     *
     * @param  array $headers
     * @return Mail
     */
    public function setHeaders(array $headers)
    {
        foreach ($headers as $name => $value) {
            $this->setHeader($name, $value);
        }

        return $this;
    }

    /**
     * Set the mail message object
     *
     * @param  Message $message
     * @return Mail
     */
    public function setMessage(Message $message)
    {
        $this->message = $message;
        return $this;
    }

    /**
     * Set the mail transport object
     *
     * @param  Transport\AbstractTransport $transport
     * @return Mail
     */
    public function setTransport(Transport\AbstractTransport $transport)
    {
        $this->transport = $transport;
        return $this;
    }

    /**
     * Set the send as group flag
     *
     * @param  boolean $group
     * @return Mail
     */
    public function sendAsGroup($group)
    {
        $this->group = (bool)$group;
        return $this;
    }

    /**
     * Determine if a mail header has been set
     *
     * @param  string $name
     * @return boolean
     */
    public function hasHeader($name)
    {
        return (isset($this->headers[$name]));
    }

    /**
     * Determine if a mail object has a message object
     *
     * @return boolean
     */
    public function hasMessage()
    {
        return (null !== $this->message);
    }

    /**
     * Determine if a mail object has a transport object
     *
     * @return boolean
     */
    public function hasTransport()
    {
        return (null !== $this->transport);
    }

    /**
     * Determine if it's set to send as group
     *
     * @return boolean
     */
    public function isSendAsGroup()
    {
        return $this->group;
    }

    /**
     * Get a mail header
     *
     * @param  string $name
     * @return string
     */
    public function getHeader($name)
    {
        return (isset($this->headers[$name])) ? $this->headers[$name] : null;
    }

    /**
     * Get mail headers
     *
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * Get the mail message object
     *
     * @return Message
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Get the mail transport object
     *
     * @return Transport\AbstractTransport
     */
    public function getTransport()
    {
        return $this->transport;
    }

    /**
     * Send the mail
     *
     * @throws Exception
     * @return array
     */
    public function send()
    {
        if (null === $this->to) {
            throw new Exception('Error: No recipients have been set.');
        }

        if (null === $this->message) {
            throw new Exception('Error: No message has been set.');
        }

        if (null === $this->transport) {
            throw new Exception('Error: No mail transport has been set.');
        }

        $this->message->initialize();

        if ($this->message->hasAttachments()) {
            $this->setHeaders([
                'MIME-Version' => $this->message->getMimeVersion(),
                'Content-Type' => $this->message->getMimeType() . '; boundary="' . $this->message->getBoundary() . '"' . "\r\n" .
                    "This is a multi-part message in MIME format.",
            ]);
        } else if ($this->message->hasHtml()) {
            $this->setHeaders([
                'MIME-Version' => $this->message->getMimeVersion(),
                'Content-Type' => $this->message->getMimeType() . '; boundary="' . $this->message->getBoundary() . '"' . "\r\n" .
                    "This is a multi-part message in MIME format.",
            ]);
        } else {
            $this->setHeaders([
                'Content-Type' => 'text/plain; charset=' . $this->message->getCharset()
            ]);
        }

        $subject = $this->message->getSubject();
        $message = $this->message->getBody();
        $results = [];

        $this->transport->setHeaders($this->buildHeaderString());

        if ($this->group) {
            $to           = (string)$this->to;
            $results[$to] = $this->transport->send($to, $subject, $message);
        } else {
            foreach ($this->to as $recipient) {
                $to           = (isset($recipient['name'])) ? $recipient['name'] . ' <' . $recipient['email'] . '>' : $recipient['email'];
                $results[$to] = $this->transport->send($to, $subject, $message);
            }
        }

        return $results;
    }

    /**
     * Build header string
     *
     * @param  boolean $to
     * @return string
     */
    public function buildHeaderString($to = false)
    {
        $this->headerString = null;

        if ((null !== $this->to) && ($to)) {
            $this->headerString .= 'To: ' . $this->to . "\r\n";
        }

        if (null !== $this->cc) {
            $this->headerString .= 'Cc: ' . $this->cc . "\r\n";
        }

        if (null !== $this->bcc) {
            $this->headerString .= 'Bcc: ' . $this->bcc . "\r\n";
        }

        foreach ($this->headers as $key => $value) {
            $this->headerString .= $key . ": " . $value . "\r\n";
        }

        return $this->headerString;
    }

    /**
     * Get header string
     *
     * @return string
     */
    public function getHeaderString()
    {
        return $this->headerString;
    }

}
