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
     * Message addresses
     * @var array
     */
    protected $addresses = [
        'To'          => [],
        'CC'          => [],
        'BCC'         => [],
        'From'        => [],
        'Reply-To'    => [],
        'Sender'      => [],
        'Return-Path' => []
    ];

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
     * Load a message from a string source or file on disk
     *
     * @param  string $message
     * @throws Exception
     * @return Message
     */
    public static function load($message)
    {
        if (is_string($message) && (strpos($message, 'Subject:') !== false)) {
            return self::parse($message);
        } else if (file_exists($message)) {
            return self::parse(file_get_contents($message));
        } else {
            throw new Exception('Error: Unable to parse message content');
        }
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
        $this->addresses['To'] = $this->parseAddresses($to, true);
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
        $this->addresses['CC'] = $this->parseAddresses($cc, true);
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
        $this->addresses['BCC'] = $this->parseAddresses($bcc, true);
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
        $this->addresses['From'] = $this->parseAddresses($from, true);
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
        $this->addresses['Reply-To'] = $this->parseAddresses($replyTo, true);
        return $this->addHeader('Reply-To', $this->parseAddresses($replyTo));
    }

    /**
     * Set Sender
     *
     * @param  mixed $sender
     * @return Message
     */
    public function setSender($sender)
    {
        $this->addresses['Sender'] = $this->parseAddresses($sender, true);
        return $this->addHeader('Sender', $this->parseAddresses($sender));
    }

    /**
     * Set Return-Path
     *
     * @param  mixed $returnPath
     * @return Message
     */
    public function setReturnPath($returnPath)
    {
        $this->addresses['Return-Path'] = $this->parseAddresses($returnPath, true);
        return $this->addHeader('Return-Path', $this->parseAddresses($returnPath));
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
        return $this->addresses['To'];
    }

    /**
     * Get CC
     *
     * @return array
     */
    public function getCc()
    {
        return $this->addresses['CC'];
    }

    /**
     * Get BCC
     *
     * @return array
     */
    public function getBcc()
    {
        return $this->addresses['BCC'];
    }

    /**
     * Get From
     *
     * @return array
     */
    public function getFrom()
    {
        return $this->addresses['From'];
    }

    /**
     * Get Reply-To
     *
     * @return array
     */
    public function getReplyTo()
    {
        return $this->addresses['Reply-To'];
    }

    /**
     * Get Sender
     *
     * @return array
     */
    public function getSender()
    {
        return $this->addresses['Sender'];
    }

    /**
     * Get Return-Path
     *
     * @return array
     */
    public function getReturnPath()
    {
        return $this->addresses['Return-Path'];
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
     * Get message part
     *
     * @param  int $i
     * @return Message\PartInterface
     */
    public function getPart($i)
    {
        return (isset($this->parts[(int)$i])) ? $this->parts[(int)$i] : null;
    }

    /**
     * Get message parts
     *
     * @return array
     */
    public function getParts()
    {
        return $this->parts;
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
        return $this->getHeadersAsString() . self::CRLF . $this->getBody();
    }

    /**
     * Save message to file on disk
     *
     * @param  string $to
     * @return void
     */
    public function save($to)
    {
        file_put_contents($to, $this->render());
    }

    /**
     * Render as an array of lines
     *
     * @return array
     */
    public function renderAsLines()
    {
        $lines = [];

        $headers = explode(Message::CRLF, $this->getHeadersAsString() . Message::CRLF);
        $body    = explode("\n", $this->getBody());

        foreach ($headers as $header) {
            $lines[] = trim($header);
        }

        foreach ($body as $line) {
            $lines[] = trim($line);
        }

        return $lines;
    }

    /**
     * Write this entire entity to a buffer
     *
     * @param Transport\Smtp\Stream\BufferInterface $is
     */
    public function toByteStream(Transport\Smtp\Stream\BufferInterface $is)
    {
        $lines = $this->renderAsLines();
        foreach ($lines as $line) {
            $is->write($line . self::CRLF);
        }
        $is->commit();
    }

    /**
     * Parse message from string
     *
     * @param  string $stream
     * @throws Exception
     * @return Message
     */
    public static function parse($stream)
    {

        if (strpos($stream, 'Subject:') === false) {
            throw new Exception('Error: There is no subject in the message contents');
        }

        if (strpos($stream, 'To:') === false) {
            throw new Exception('Error: There is no recipient in the message contents');
        }

        $headers = trim(substr($stream, 0, strpos($stream, Message::CRLF . Message::CRLF)));
        $body    = trim(str_replace($headers, '', $stream));
        $headers = imap_rfc822_parse_headers($headers);
        $subject = substr($stream, (strpos($stream, 'Subject: ') + 9));
        $subject = substr($subject, 0, strpos($subject, Message::CRLF));

        $message = new self($subject);

        foreach ($headers as $header => $value) {
            switch ($header) {
                case 'to':
                    $message->setTo($value);
                    break;
                case 'cc':
                    $message->setCc($value);
                    break;
                case 'bcc':
                    $message->setBcc($value);
                    break;
                case 'from':
                    $message->setFrom($value);
                    break;
                case 'reply_to':
                    $message->setReplyTo($value);
                    break;
                case 'sender':
                    $message->setSender($value);
                    break;
                case 'return_path':
                    $message->setReturnPath($value);
                    break;
                default:
                    if (substr($header, -7) != 'address') {
                        $header = ((strpos($header, '-') != false) || (strpos($header, '_') != false)) ?
                            str_replace(' ', '-', ucwords(str_replace(['-', '_'], [' ', ' '], strtolower($header)))) :
                            ucfirst(strtolower($header));
                        $message->addHeader($header, $value);
                    }
            }
        }

        if (substr($body, 0, 2) == '--') {
            $boundary = substr($body, 2);
            $boundary = trim(substr($boundary, 0, strpos($boundary, Message::CRLF)));
            $parts    = (strpos($body, $boundary) !== false) ?
                explode($boundary, $body) : [$body];
        } else {
            $parts = [$body];
        }

        $parts = self::parseMessageParts($parts);

        foreach ($parts as $part) {
            if ($part->attachment) {
                $message->addPart(new Message\Attachment($part->content, $part->type, $part->basename));
            } else if (stripos($part->type, 'html') !== false) {
                $message->addPart(new Message\Html($part->content));
            } else if (stripos($part->type, 'text') !== false) {
                $message->addPart(new Message\Text($part->content));
            } else {
                $message->addPart(new Message\Simple($part->content));
            }
        }

        return $message;
    }

    /**
     * Parse message parts from string
     *
     * @param  array $parts
     * @throws Exception
     * @return array
     */
    public static function parseMessageParts($parts)
    {
        foreach ($parts as $i => $part) {
            $part = trim($part);
            if (($part == '--') || empty($part)) {
                unset($parts[$i]);
            } else {
                $headers    = substr($part, 0, strpos($part, "\r\n\r\n"));
                $headers    = explode("\r\n", $headers);
                $headersAry = [];
                $part       = trim(substr($part, (strpos($part, "\r\n\r\n") + 4)));
                foreach ($headers as $header) {
                    if (strpos($header, ':') !== false) {
                        $name  = trim(substr($header, 0, strpos($header, ':')));
                        $value = trim(substr($header, (strpos($header, ': ') + 2)));
                    } else if (strpos($header, '=') !== false) {
                        $name  = trim(substr($header, 0, strpos($header, '=')));
                        $value = trim(substr($header, (strpos($header, '=') + 1)));
                    } else {
                        $name  = null;
                        $value = null;
                    }
                    if ((null !== $name) && (null !== $value)) {
                        if ((substr($value, 0, 1) == '"') && (substr($value, -1) == '"')) {
                            $value = substr($value, 1);
                            $value = substr($value, 0, -1);
                        }
                        $headersAry[$name] = $value;
                    }
                }

                if (substr($part, -2) == '--') {
                    $part = trim(substr($part, 0, -2));
                }

                if (isset($headersAry['Content-Transfer-Encoding'])) {
                    switch (strtolower($headersAry['Content-Transfer-Encoding'])) {
                        case 'base64':
                            $part = base64_decode($part);
                            break;
                        default:
                            $part = quoted_printable_decode($part);
                    }
                }

                $type = null;
                if (isset($headersAry['Content-Type'])) {
                    $type = $headersAry['Content-Type'];
                    if (strpos($type, ';') !== false) {
                        $type = trim(substr($type, 0, strpos($type, ';')));
                    }
                }

                $basename = null;
                if (isset($headersAry['Content-Disposition']) && (stripos($headersAry['Content-Disposition'], 'filename=') !== false)) {
                    $basename = substr($headersAry['Content-Disposition'], (stripos($headersAry['Content-Disposition'], 'filename=') + 9));
                    if (strpos($basename, ';') !== false) {
                        $basename = substr($basename, 0, strpos($basename, ';'));
                    }
                } else if (isset($headersAry['Content-Description'])) {
                    $basename = $headersAry['Content-Description'];
                } else if (isset($headersAry['name'])) {
                    $basename = $headersAry['name'];
                }

                if ((substr($basename, 0, 1) == '"') && (substr($basename, -1) == '"')) {
                    $basename = substr($basename, 1);
                    $basename = substr($basename, 0, -1);
                }

                $parts[$i] = new \ArrayObject([
                    'headers'    => $headersAry,
                    'type'       => $type,
                    'attachment' => (isset($headersAry['Content-Disposition']) && (stripos($headersAry['Content-Disposition'], 'attachment') !== false)),
                    'basename'   => $basename,
                    'content'    => $part
                ], \ArrayObject::ARRAY_AS_PROPS);
            }
        }

        return array_values($parts);
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
                if ($value instanceof \stdClass) {
                    $formatted[]    = $value->mailbox . '@' . $value->host;
                    $emails[$value->mailbox . '@' . $value->host] = null;
                } else {
                    // $key is email
                    if (strpos($key, '@') !== false) {
                        if (!empty($value)) {
                            $formatted[]  = '"' . $value . '" <' . $key . '>';
                            $emails[$key] = $value;

                        } else {
                            $formatted[]  = $key;
                            $emails[$key] = null;
                        }
                    // $value is email
                    } else if (strpos($value, '@') !== false) {
                        if (!empty($key)) {
                            $formatted[]    = '"' . $key . '" <' . $value . '>';
                            $emails[$value] = $key;
                        } else {
                            $formatted[]    = $value;
                            $emails[$value] = null;
                        }
                    }
                }
            }
        } else if (is_string($addresses) && (strpos($addresses, '@') !== false)) {
            $formatted          = [$addresses];
            if (strpos($addresses, ',') !== false) {
                $addresses = explode(',', $addresses);
                foreach ($addresses as $address) {
                    $address = $this->parseNameAndEmail(trim($address));
                    $emails[$address['email']] = $address['name'];
                }
            } else {
                $address = $this->parseNameAndEmail(trim($addresses));
                $emails[$address['email']] = $address['name'];
            }
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

    /**
     * Parse a name and email from an address string
     *
     * @param  string $address
     * @return array
     */
    protected function parseNameAndEmail($address)
    {
        $name  = null;
        $email = null;

        if ((strpos($address, '<') !== false) && (strpos($address, '>') !== false)) {
            $name  = trim(substr($address, 0, strpos($address, '<')));
            $email = substr($address, (strpos($address, '<') + 1));
            $email = trim(substr($email, 0, -1));
        } else if (strpos($address, '@') !== false) {
            $email = trim($address);
        }

        return ['name' => $name, 'email' => $email];
    }

    /**
     * Perform a "deep" clone of a message object
     *
     * @return void
     */
    public function __clone() {
        foreach($this as $key => $val) {
            if (is_object($val) || (is_array($val))) {
                $this->{$key} = unserialize(serialize($val));
            }
        }
    }

}
