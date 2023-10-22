<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp-framework
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2024 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Pop\Mail;

use Pop\Mail\Message\Part;
use Pop\Mail\Message\Simple;
use Pop\Mail\Message\Text;
use Pop\Mail\Message\Html;
use Pop\Mail\Message\Attachment;
use Pop\Mail\Message\AbstractMessage;
use Pop\Mail\Message\AbstractPart;
use Pop\Mail\Message\PartInterface;
use Pop\Mime\Part\Header\Value;

/**
 * Message class
 *
 * @category   Pop
 * @package    Pop\Mail
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2024 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    4.0.0
 */
class Message extends AbstractMessage
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
    protected array $parts = [];

    /**
     * Message addresses
     * @var array
     */
    protected array $addresses = [
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
     * @var ?string
     */
    protected ?string $boundary = null;

    /**
     * Constructor
     *
     * Instantiate the message object
     *
     * @param ?string $subject
     */
    public function __construct(?string $subject = null)
    {
        parent::__construct();

        if ($subject !== null) {
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
    public static function load(string $message): Message
    {
        if (is_string($message) && (str_contains($message, 'Subject:'))) {
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
     * @return AbstractMessage
     */
    public function setSubject(string $subject): AbstractMessage
    {
        return $this->addHeader('Subject', $subject);
    }

    /**
     * Set To
     *
     * @param  mixed $to
     * @return AbstractMessage
     */
    public function setTo(mixed $to): AbstractMessage
    {
        if ($to instanceof Value) {
            $to = (string)$to;
        }
        $this->addresses['To'] = $this->parseAddresses($to, true);
        return $this->addHeader('To', $this->parseAddresses($to));
    }

    /**
     * Set CC
     *
     * @param  mixed $cc
     * @return AbstractMessage
     */
    public function setCc(mixed $cc): AbstractMessage
    {
        if ($cc instanceof Value) {
            $cc = (string)$cc;
        }
        $this->addresses['CC'] = $this->parseAddresses($cc, true);
        return $this->addHeader('CC', $this->parseAddresses($cc));
    }

    /**
     * Set BCC
     *
     * @param  mixed $bcc
     * @return AbstractMessage
     */
    public function setBcc(mixed $bcc): AbstractMessage
    {
        if ($bcc instanceof Value) {
            $bcc = (string)$bcc;
        }
        $this->addresses['BCC'] = $this->parseAddresses($bcc, true);
        return $this->addHeader('BCC', $this->parseAddresses($bcc));
    }

    /**
     * Set From
     *
     * @param  mixed $from
     * @return AbstractMessage
     */
    public function setFrom(mixed $from): AbstractMessage
    {
        if ($from instanceof Value) {
            $from = (string)$from;
        }
        $this->addresses['From'] = $this->parseAddresses($from, true);
        return $this->addHeader('From', $this->parseAddresses($from));
    }

    /**
     * Set Reply-To
     *
     * @param  mixed $replyTo
     * @return AbstractMessage
     */
    public function setReplyTo(mixed $replyTo): AbstractMessage
    {
        if ($replyTo instanceof Value) {
            $replyTo = (string)$replyTo;
        }
        $this->addresses['Reply-To'] = $this->parseAddresses($replyTo, true);
        return $this->addHeader('Reply-To', $this->parseAddresses($replyTo));
    }

    /**
     * Set Sender
     *
     * @param  mixed $sender
     * @return AbstractMessage
     */
    public function setSender(mixed $sender): AbstractMessage
    {
        if ($sender instanceof Value) {
            $sender = (string)$sender;
        }
        $this->addresses['Sender'] = $this->parseAddresses($sender, true);
        return $this->addHeader('Sender', $this->parseAddresses($sender));
    }

    /**
     * Set Return-Path
     *
     * @param  mixed $returnPath
     * @return AbstractMessage
     */
    public function setReturnPath(mixed $returnPath): AbstractMessage
    {
        if ($returnPath instanceof Value) {
            $returnPath = (string)$returnPath;
        }
        $this->addresses['Return-Path'] = $this->parseAddresses($returnPath, true);
        return $this->addHeader('Return-Path', $this->parseAddresses($returnPath));
    }

    /**
     * Set body
     *
     * @param  mixed $body
     * @return Message
     */
    public function setBody(mixed $body): Message
    {
        if (!($body instanceof PartInterface)) {
            $body = new Text($body);
        }
        return $this->addPart($body);
    }

    /**
     * Add text message part
     *
     * @param  mixed $text
     * @return Message
     */
    public function addText(mixed $text): Message
    {
        if (!($text instanceof Text) && is_string($text)) {
            $text = new Text($text);
        }
        return $this->addPart($text);
    }

    /**
     * Add HTML message part
     *
     * @param  mixed $html
     * @return Message
     */
    public function addHtml(mixed $html): Message
    {
        if (!($html instanceof Html) && is_string($html)) {
            $html = new Html($html);
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
    public function attachFile(string $file, string $encoding = AbstractPart::BASE64): Message
    {
        if (!($file instanceof Attachment)) {
            $options = [
                'encoding' => $encoding,
                'chunk'    => true
            ];
            $file = Attachment::createFromFile($file, $options);
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
    public function attachFileFromStream(
        string $stream, string $basename = 'file.tmp', string $encoding = AbstractPart::BASE64
    ): Message
    {
        $options = [
            'basename' => $basename,
            'encoding' => $encoding,
            'chunk'    => true
        ];
        $file = Attachment::createFromStream($stream, $options);
        return $this->addPart($file);
    }

    /**
     * Add message part
     *
     * @param  PartInterface $part
     * @return Message
     */
    public function addPart(PartInterface $part): Message
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
    public function removeHeader(string $header): Message
    {
        if (isset($this->headers[$header])) {
            unset($this->headers[$header]);
        }
        return $this;
    }

    /**
     * Get subject
     *
     * @return ?string
     */
    public function getSubject(): ?string
    {
        return $this->getHeader('Subject');
    }

    /**
     * Get To
     *
     * @return array
     */
    public function getTo(): array
    {
        return $this->addresses['To'];
    }

    /**
     * Get CC
     *
     * @return array
     */
    public function getCc(): array
    {
        return $this->addresses['CC'];
    }

    /**
     * Get BCC
     *
     * @return array
     */
    public function getBcc(): array
    {
        return $this->addresses['BCC'];
    }

    /**
     * Get From
     *
     * @return array
     */
    public function getFrom(): array
    {
        return $this->addresses['From'];
    }

    /**
     * Get Reply-To
     *
     * @return array
     */
    public function getReplyTo(): array
    {
        return $this->addresses['Reply-To'];
    }

    /**
     * Get Sender
     *
     * @return array
     */
    public function getSender(): array
    {
        return $this->addresses['Sender'];
    }

    /**
     * Get Return-Path
     *
     * @return array
     */
    public function getReturnPath(): array
    {
        return $this->addresses['Return-Path'];
    }

    /**
     * Has To
     *
     * @return bool
     */
    public function hasTo(): bool
    {
        return !empty($this->addresses['To']);
    }

    /**
     * Has CC
     *
     * @return bool
     */
    public function hasCc(): bool
    {
        return !empty($this->addresses['CC']);
    }

    /**
     * Has BCC
     *
     * @return bool
     */
    public function hasBcc(): bool
    {
        return !empty($this->addresses['BCC']);
    }

    /**
     * Has From
     *
     * @return bool
     */
    public function hasFrom(): bool
    {
        return !empty($this->addresses['From']);
    }

    /**
     * Has Reply-To
     *
     * @return bool
     */
    public function hasReplyTo(): bool
    {
        return !empty($this->addresses['Reply-To']);
    }

    /**
     * Has Sender
     *
     * @return bool
     */
    public function hasSender(): bool
    {
        return !empty($this->addresses['Sender']);
    }

    /**
     * Has Return-Path
     *
     * @return bool
     */
    public function hasReturnPath(): bool
    {
        return !empty($this->addresses['Return-Path']);
    }

    /**
     * Get message body
     *
     * @return ?string
     */
    public function getBody(): ?string
    {
        $body  = null;

        if (count($this->parts) > 1) {
            foreach ($this->parts as $part) {
                $body .= '--' . $this->getBoundary() . self::CRLF . $part . self::CRLF;
            }
            $body .= '--' . $this->getBoundary() . '--';
        } else if (count($this->parts) == 1) {
            $part  = $this->parts[0];
            if (($part instanceof Text) || ($part instanceof Html)) {
                $body .= $part->getBody() . self::CRLF;
            } else {
                $body .= '--' . $this->getBoundary() . self::CRLF . $part . self::CRLF;
                $body .= '--' . $this->getBoundary() . '--';
            }
        }

        return $body;
    }

    /**
     * Get message MIME boundary
     *
     * @return string
     */
    public function getBoundary(): string
    {
        if ($this->boundary === null) {
            $this->generateBoundary();
        }
        return $this->boundary;
    }

    /**
     * Get message part
     *
     * @param  int $i
     * @return PartInterface|null
     */
    public function getPart(int $i): PartInterface|null
    {
        return $this->parts[$i] ?? null;
    }

    /**
     * Get message parts
     *
     * @return array
     */
    public function getParts(): array
    {
        return $this->parts;
    }

    /**
     * Set message MIME boundary
     *
     * @param  string $boundary
     * @return Message
     */
    public function setBoundary(string $boundary): Message
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
    public function generateBoundary(): Message
    {
        return $this->setBoundary(sha1(time()));
    }

    /**
     * Render message
     *
     * @param  array $omitHeaders
     * @return string
     */
    public function render(array $omitHeaders = []): string
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
    public function save(string $to, array $omitHeaders = []): void
    {
        file_put_contents($to, $this->render($omitHeaders));
    }

    /**
     * Render as an array of lines
     *
     * @param  array $omitHeaders
     * @return array
     */
    public function renderAsLines(array $omitHeaders = []): array
    {
        $lines   = [];
        $headers = explode(Message::CRLF, $this->getHeadersAsString($omitHeaders) . Message::CRLF);
        $body    = explode(Message::CRLF, $this->getBody());

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
     * @param  Transport\Smtp\Stream\BufferInterface $is
     * @param  array $omitHeaders
     * @return void
     */
    public function toByteStream(Transport\Smtp\Stream\BufferInterface $is, array $omitHeaders = []): void
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
    public static function parseFromFile(string $file): Message
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
    public static function parse(string $stream): Message
    {
        $parsedMessage = \Pop\Mime\Message::parseMessage($stream);
        $message       = new self();

        if ($parsedMessage->hasHeaders()) {
            $headers = $parsedMessage->getHeaders();
            foreach ($headers as $header => $value) {
                if (count($value->getValues()) == 1) {
                    switch (strtolower($header)) {
                        case 'subject':
                            $message->setSubject($value->getValue(0));
                            break;
                        case 'to':
                            $message->setTo($value->getValue(0));
                            break;
                        case 'cc':
                            $message->setCc($value->getValue(0));
                            break;
                        case 'bcc':
                            $message->setBcc($value->getValue(0));
                            break;
                        case 'from':
                            $message->setFrom($value->getValue(0));
                            break;
                        case 'reply-to':
                            $message->setReplyTo($value->getValue(0));
                            break;
                        case 'sender':
                            $message->setSender($value->getValue(0));
                            break;
                        case 'return-path':
                            $message->setReturnPath($value->getValue(0));
                            break;
                        default:
                            $message->addHeader($header, $value->getValue(0));
                    }
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
            $parts = Part::parseParts($parsedMessage->getParts());

            foreach ($parts as $part) {
                if ($part->attachment) {
                    $options = [
                        'contentType' => $part->type,
                        'basename'    => $part->basename,
                        'encoding'    => AbstractPart::BASE64,
                        'chunk'       => true
                    ];
                    $message->addPart(Attachment::createFromStream($part->content, $options));
                } else if (!empty($part->type) && (stripos($part->type, 'html') !== false)) {
                    $message->addPart(new Html($part->content));
                } else if (!empty($part->type) && (stripos($part->type, 'text') !== false)) {
                    $message->addPart(new Text($part->content));
                } else {
                    $message->addPart(new Simple($part->content));
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
    public static function decodeText(string $text): string
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
     * @param  mixed $addresses
     * @param  bool  $asArray
     * @return string|array
     */
    public function parseAddresses(mixed $addresses, bool $asArray = false): string|array
    {
        $formatted = [];
        $emails    = [];

        if (is_array($addresses)) {
            foreach ($addresses as $key => $value) {
                if (($value instanceof \stdClass) && isset($value->mailbox) && isset($value->host)) {
                    $formatted[]    = $value->mailbox . '@' . $value->host;
                    $emails[$value->mailbox . '@' . $value->host] = null;
                } else {
                    // $key is email
                    if (str_contains($key, '@')) {
                        if (!empty($value) && !is_numeric($value)) {
                            $formatted[]  = '"' . $value . '" <' . $key . '>';
                            $emails[$key] = $value;
                        } else {
                            $formatted[]  = $key;
                            $emails[$key] = null;
                        }
                    // $value is email
                    } else if (str_contains($value, '@')) {
                        if (!empty($key) && !is_numeric($key)) {
                            $formatted[]    = '"' . $key . '" <' . $value . '>';
                            $emails[$value] = $key;
                        } else {
                            $formatted[]    = $value;
                            $emails[$value] = null;
                        }
                    }
                }
            }
        } else if (is_string($addresses) && (str_contains($addresses, '@'))) {
            $formatted = [$addresses];
            if (str_contains($addresses, ',')) {
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
    public function parseNameAndEmail(string $address): array
    {
        $name  = null;
        $email = null;

        if ((str_contains($address, '<')) && (str_contains($address, '>'))) {
            $name  = trim(substr($address, 0, strpos($address, '<')));
            $email = substr($address, (strpos($address, '<') + 1));
            $email = trim(substr($email, 0, -1));
        } else if (str_contains($address, '@')) {
            $email = trim($address);
        }

        return ['name' => $name, 'email' => $email];
    }

    /**
     * Validate content type based on message parts added to the message
     *
     * @return void
     */
    protected function validateContentType(): void
    {
        $hasText = false;
        $hasHtml = false;
        $hasFile = false;

        foreach ($this->parts as $part) {
            if ($part instanceof Text) {
                $hasText = true;
            }
            if ($part instanceof Html) {
                $hasHtml = true;
            }
            if ($part instanceof Attachment) {
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
    public function __clone(): void
    {
        foreach($this as $key => $val) {
            if (is_object($val) || (is_array($val))) {
                $this->{$key} = unserialize(serialize($val));
            }
        }
    }

}
