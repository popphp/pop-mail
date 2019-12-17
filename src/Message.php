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
namespace Pop\Mail;

use Pop\Mail\Message\AbstractPart;

/**
 * Message class
 *
 * @category   Pop
 * @package    Pop\Mail
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2020 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    3.5.0
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
    public function __construct($subject = null)
    {
        parent::__construct();

        if (null !== $subject) {
            $this->setSubject($subject);
        }
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
            return self::parseFromFile($message);
        } else {
            throw new Exception('Error: Unable to parse message content');
        }
    }

    /**
     * Set Subject
     *
     * @param  string $subject
     * @return Message\AbstractMessage
     */
    public function setSubject($subject)
    {
        return $this->addHeader('Subject', $subject);
    }

    /**
     * Set To
     *
     * @param  string $to
     * @return Message\AbstractMessage
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
     * @return Message\AbstractMessage
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
     * @return Message\AbstractMessage
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
     * @return Message\AbstractMessage
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
     * @return Message\AbstractMessage
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
     * @return Message\AbstractMessage
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
     * @return Message\AbstractMessage
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
     * @param  string $file
     * @param  string $encoding
     * @return Message
     */
    public function attachFile($file, $encoding = AbstractPart::BASE64)
    {
        if (!($file instanceof Message\Attachment)) {
            $options = [
                'encoding' => $encoding,
                'chunk'    => true
            ];
            $file = Message\Attachment::createFromFile($file, $options);
        }
        return $this->addPart($file);
    }

    /**
     * Attach file message part from stream
     *
     * @param  string $stream
     * @param  string $basename
     * @param  string $encoding
     * @return Message
     */
    public function attachFileFromStream($stream, $basename = 'file.tmp', $encoding = AbstractPart::BASE64)
    {
        $options = [
            'basename' => $basename,
            'encoding' => $encoding,
            'chunk'    => true
        ];
        $file = Message\Attachment::createFromStream($stream, $options);
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
            $part  = $this->parts[0];
            if (($part instanceof Message\Text) || ($part instanceof Message\Html)) {
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
     * @param  array $omitHeaders
     * @return string
     */
    public function render(array $omitHeaders = [])
    {
        return $this->getHeadersAsString($omitHeaders) . self::CRLF . $this->getBody();
    }

    /**
     * Save message to file on disk
     *
     * @param  string $to
     * @param  array  $omitHeaders
     * @return void
     */
    public function save($to, array $omitHeaders = [])
    {
        file_put_contents($to, $this->render($omitHeaders));
    }

    /**
     * Render as an array of lines
     *
     * @param  array $omitHeaders
     * @return array
     */
    public function renderAsLines(array $omitHeaders = [])
    {
        $lines   = [];
        $headers = explode(Message::CRLF, $this->getHeadersAsString($omitHeaders) . Message::CRLF);
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
     * @param  array $omitHeaders
     */
    public function toByteStream(Transport\Smtp\Stream\BufferInterface $is, array $omitHeaders = [])
    {
        $lines = $this->renderAsLines($omitHeaders);
        foreach ($lines as $line) {
            $is->write($line . self::CRLF);
        }
        $is->commit();
    }

    /**
     * Parse message from file
     *
     * @param  string $file
     * @throws Exception
     * @return Message
     */
    public static function parseFromFile($file)
    {
        if (!file_exists($file)) {
            throw new Exception("Error: The file '" . $file . "' does not exist.");
        }

        return self::parse(file_get_contents($file));
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
        $parsedMessage = \Pop\Mime\Message::parseMessage($stream);
        $message       = new self();

        if ($parsedMessage->hasHeaders()) {
            $headers = $parsedMessage->getHeaders();
            foreach ($headers as $header => $value) {
                switch (strtolower($header)) {
                    case 'subject':
                        $message->setSubject($value->getValue());
                        break;
                    case 'to':
                        $message->setTo($value->getValue());
                        break;
                    case 'cc':
                        $message->setCc($value->getValue());
                        break;
                    case 'bcc':
                        $message->setBcc($value->getValue());
                        break;
                    case 'from':
                        $message->setFrom($value->getValue());
                        break;
                    case 'reply-to':
                        $message->setReplyTo($value->getValue());
                        break;
                    case 'sender':
                        $message->setSender($value->getValue());
                        break;
                    case 'return-path':
                        $message->setReturnPath($value->getValue());
                        break;
                    default:
                        $message->addHeader($header, $value->getValue());
                }
            }
        }

        if (empty($message->getSubject())) {
            throw new Exception('Error: There is no subject in the message contents');
        }

        if (empty($message->getTo())) {
            throw new Exception('Error: There is no to address in the message contents');
        }

        if ($parsedMessage->hasParts()) {
            $parts = Message\Part::parseParts($parsedMessage->getParts());

            foreach ($parts as $part) {
                if ($part->attachment) {
                    $options = [
                        'contentType' => $part->type,
                        'basename'    => $part->basename,
                        'encoding'    => AbstractPart::BASE64,
                        'chunk'       => true
                    ];
                    $message->addPart(Message\Attachment::createFromStream($part->content, $options));
                } else if (stripos($part->type, 'html') !== false) {
                    $message->addPart(new Message\Html($part->content));
                } else if (stripos($part->type, 'text') !== false) {
                    $message->addPart(new Message\Text($part->content));
                } else {
                    $message->addPart(new Message\Simple($part->content));
                }
            }
        }

        return $message;
    }

    /**
     * Decode text
     *
     * @param  string $text
     * @return string
     */
    public static function decodeText($text)
    {
        $decodedValues = imap_mime_header_decode($text);
        $decoded       = '';

        foreach ($decodedValues as $string) {
            $decoded .= $string->text;
        }

        return $decoded;
    }

    /**
     * Parse addresses
     *
     * @param  mixed   $addresses
     * @param  boolean $asArray
     * @return string
     */
    public function parseAddresses($addresses, $asArray = false)
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
     * Parse a name and email from an address string
     *
     * @param  string $address
     * @return array
     */
    public function parseNameAndEmail($address)
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
