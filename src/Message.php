<?php
declare(strict_types=1);
/**
 * Pop PHP Framework (https://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp-framework
 * @author     Nick Sagona, III <dev@noladev.com>
 * @copyright  Copyright (c) 2009-2027 NOLA Interactive, LLC.
 * @license    https://www.popphp.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Pop\Mail;

use Pop\Mime\Part;
use Pop\Mime\Message as MimeMessage;
use Pop\Mail\Message\Text;
use Pop\Mail\Message\Html;
use Pop\Mail\Message\Attachment;
use Pop\Mail\Message\CharsetAwareTrait;
use Pop\Mail\Message\Part as MailPart;
use Pop\Mime\Part\Header\AddressList;
use Pop\Mime\Part\Header\Value;
use Pop\Mime\Part\Header\EncodedWord;

/**
 * Message class
 *
 * @category   Pop
 * @package    Pop\Mail
 * @author     Nick Sagona, III <dev@noladev.com>
 * @copyright  Copyright (c) 2009-2027 NOLA Interactive, LLC.
 * @license    https://www.popphp.org/license     New BSD License
 * @version    5.0.0
 */
class Message extends MimeMessage
{
    use CharsetAwareTrait;

    /**
     * Message newline constant
     * @var string
     */
    const CRLF = "\r\n";

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
     * Get headers (flat name => string map)
     *
     * @return array
     */
    public function getHeaders(): array
    {
        $headers = [];
        foreach (parent::getHeaders() as $name => $header) {
            if (count($header->getValues()) === 1) {
                $headers[$name] = $header->getValue(0)->render($name);
            }
        }
        return $headers;
    }

    /**
     * Get header value as a string
     *
     * Named getHeaderValue() rather than getHeader() because Pop\Mime\Part already
     * declares a public getHeader(string $name): ?Header. A same-named override
     * returning ?string here is not legal (return-type covariance requires a
     * subtype of Header|null, and string is unrelated) - PHP fatals at class-load
     * time for every `new Message()`. getHeader() is therefore left un-overridden
     * and inherited as-is (returns the raw Part\Header object, consistent with how
     * Text/Html/Attachment already expose it); this method is the flat-string
     * convenience accessor instead.
     *
     * @param  string $name
     * @return ?string
     */
    public function getHeaderValue(string $name): ?string
    {
        $header = parent::getHeader($name);
        if (($header === null) || (count($header->getValues()) !== 1)) {
            return null;
        }
        return $header->getValue(0)->render($name);
    }

    /**
     * Get a single header rendered as a full "Name: Value" string
     *
     * @param  string $name
     * @return ?string
     */
    public function getHeaderAsString(string $name): ?string
    {
        $header = parent::getHeader($name);
        return ($header !== null) ? rtrim((string)$header) : null;
    }

    /**
     * Get all headers rendered as a string, one per line
     *
     * @param  array $omitHeaders
     * @return string
     */
    public function getHeadersAsString(array $omitHeaders = []): string
    {
        $result = '';
        foreach (parent::getHeaders() as $name => $header) {
            if (in_array($name, $omitHeaders, true)) {
                continue;
            }
            if ((count($header->getValues()) === 1) && (trim((string)$header->getValue(0)) === '')) {
                continue;
            }
            $line = (string)$header;
            if (($name === 'Content-Type') && ($this->getCharSet() !== null) && !str_contains(strtolower($line), 'charset')) {
                $line .= '; charset="' . $this->getCharSet() . '"';
            }
            $result .= $line . self::CRLF;
        }
        return $result;
    }

    /**
     * Get message MIME boundary (auto-generating and setting MIME-Version if needed)
     *
     * @return string
     */
    public function getBoundary(): string
    {
        if (!$this->hasBoundary()) {
            $this->generateBoundary();
            $this->addHeader('MIME-Version', '1.0');
        }
        return parent::getBoundary();
    }

    /**
     * Get the rendered body content
     *
     * @return ?string
     */
    public function getBodyContent(): ?string
    {
        if ($this->hasParts()) {
            return $this->renderParts(true, false);
        }
        return $this->hasBody() ? $this->renderBody() : null;
    }

    /**
     * Render the message
     *
     * Overrides Part::render() to explicitly call getBoundary() first (when
     * parts are present), which is what actually generates the MIME-Version
     * header as a side effect. Part::renderParts() (called internally by the
     * inherited render()) reads $this->boundary/generateBoundary() directly
     * and never routes through this class's getBoundary() override, so
     * without this, MIME-Version was never emitted on real rendered output.
     * Routing through getHeadersAsString() also picks up the charset-append
     * logic that the inherited renderHeaders() doesn't have.
     *
     * Body must be rendered BEFORE headers are stringified: renderParts()
     * (invoked by getBodyContent()) is what lazily synthesizes the
     * Content-Type: multipart/...; boundary=... header as a side effect when
     * one isn't already present. Calling getHeadersAsString() first would
     * stringify the header set before that side effect fires, silently
     * dropping Content-Type from the rendered output.
     *
     * @param  bool $preamble
     * @return string
     */
    public function render(bool $preamble = true): string
    {
        if ($this->hasParts()) {
            $this->getBoundary();
        }
        $body = $this->getBodyContent();
        return $this->getHeadersAsString() . self::CRLF . $body;
    }

    /**
     * Generate a Message-ID and set it as a side effect
     *
     * This overrides Part::generateId(?string $domain = null): string (same
     * signature, legal). Note: MimeMessage::setMessageId() internally does
     * `$id ?? $this->generateId($domain)` when no $id is given - calling
     * setMessageId(null, $domain) from inside this override would resolve
     * $this->generateId() polymorphically back to THIS method, recursing
     * forever. Generating the id via parent::generateId() first and passing
     * it explicitly avoids that.
     *
     * @param  ?string $domain
     * @return string
     */
    public function generateId(?string $domain = null): string
    {
        $this->setMessageId(parent::generateId($domain), $domain);
        return (string)$this->getHeaderValue('Message-ID');
    }

    /**
     * Get message part
     *
     * @param  int $i
     * @return ?Part
     */
    public function getPart(int $i): ?Part
    {
        return $this->getParts()[$i] ?? null;
    }

    /**
     * Render as an array of lines
     *
     * @return array
     */
    public function renderAsLines(): array
    {
        $lines = explode(self::CRLF, $this->render());
        return array_map('trim', $lines);
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
        if (str_contains($message, 'Subject:')) {
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
     * @return Message
     */
    public function setSubject(string $subject): Message
    {
        $this->addHeader('Subject', $subject);
        return $this;
    }

    /**
     * Set To
     *
     * @param  mixed $to
     * @return Message
     */
    public function setTo(mixed $to): Message
    {
        if ($to instanceof Value) {
            $to = (string)$to;
        } else if (is_array($to)) {
            $to = self::arrayToAddressString($to);
        }
        $list = AddressList::parse((string)$to);
        $this->addresses['To'] = self::addressListToArray($list);
        $this->addHeader('To', $list->render());
        return $this;
    }

    /**
     * Set CC
     *
     * @param  mixed $cc
     * @return Message
     */
    public function setCc(mixed $cc): Message
    {
        if ($cc instanceof Value) {
            $cc = (string)$cc;
        } else if (is_array($cc)) {
            $cc = self::arrayToAddressString($cc);
        }
        $list = AddressList::parse((string)$cc);
        $this->addresses['CC'] = self::addressListToArray($list);
        $this->addHeader('CC', $list->render());
        return $this;
    }

    /**
     * Set BCC
     *
     * @param  mixed $bcc
     * @return Message
     */
    public function setBcc(mixed $bcc): Message
    {
        if ($bcc instanceof Value) {
            $bcc = (string)$bcc;
        } else if (is_array($bcc)) {
            $bcc = self::arrayToAddressString($bcc);
        }
        $list = AddressList::parse((string)$bcc);
        $this->addresses['BCC'] = self::addressListToArray($list);
        $this->addHeader('BCC', $list->render());
        return $this;
    }

    /**
     * Set From
     *
     * @param  mixed $from
     * @return Message
     */
    public function setFrom(mixed $from): Message
    {
        if ($from instanceof Value) {
            $from = (string)$from;
        } else if (is_array($from)) {
            $from = self::arrayToAddressString($from);
        }
        $list = AddressList::parse((string)$from);
        $this->addresses['From'] = self::addressListToArray($list);
        $this->addHeader('From', $list->render());
        return $this;
    }

    /**
     * Set Reply-To
     *
     * @param  mixed $replyTo
     * @return Message
     */
    public function setReplyTo(mixed $replyTo): Message
    {
        if ($replyTo instanceof Value) {
            $replyTo = (string)$replyTo;
        } else if (is_array($replyTo)) {
            $replyTo = self::arrayToAddressString($replyTo);
        }
        $list = AddressList::parse((string)$replyTo);
        $this->addresses['Reply-To'] = self::addressListToArray($list);
        $this->addHeader('Reply-To', $list->render());
        return $this;
    }

    /**
     * Set Sender
     *
     * @param  mixed $sender
     * @return Message
     */
    public function setSender(mixed $sender): Message
    {
        if ($sender instanceof Value) {
            $sender = (string)$sender;
        } else if (is_array($sender)) {
            $sender = self::arrayToAddressString($sender);
        }
        $list = AddressList::parse((string)$sender);
        $this->addresses['Sender'] = self::addressListToArray($list);
        $this->addHeader('Sender', $list->render());
        return $this;
    }

    /**
     * Set Return-Path
     *
     * @param  mixed $returnPath
     * @return Message
     */
    public function setReturnPath(mixed $returnPath): Message
    {
        if ($returnPath instanceof Value) {
            $returnPath = (string)$returnPath;
        } else if (is_array($returnPath)) {
            $returnPath = self::arrayToAddressString($returnPath);
        }
        $list = AddressList::parse((string)$returnPath);
        $this->addresses['Return-Path'] = self::addressListToArray($list);
        $this->addHeader('Return-Path', $list->render());
        return $this;
    }

    /**
     * Convert an AddressList into an email => ?name array
     *
     * @param  AddressList $list
     * @return array
     */
    private static function addressListToArray(AddressList $list): array
    {
        $emails = [];
        foreach ($list->getAddresses() as $address) {
            $emails[$address->getAddress()] = $address->getName();
        }
        return $emails;
    }

    /**
     * Convert an array of addresses into a comma-joined address string
     *
     * Supports:
     *   - ['email@example.com' => 'Name', ...]         (key is email)
     *   - ['Name' => 'email@example.com', ...]         (value is email)
     *   - [0 => 'email@example.com', ...]               (plain list)
     *   - [0 => stdClass{mailbox, host}, ...]
     *
     * @param  array $addresses
     * @return string
     */
    private static function arrayToAddressString(array $addresses): string
    {
        $formatted = [];

        foreach ($addresses as $key => $value) {
            if (($value instanceof \stdClass) && isset($value->mailbox) && isset($value->host)) {
                $formatted[] = $value->mailbox . '@' . $value->host;
            } else {
                // $key is email
                if (is_string($key) && str_contains($key, '@')) {
                    if (!empty($value) && !is_numeric($value)) {
                        $formatted[] = '"' . $value . '" <' . $key . '>';
                    } else {
                        $formatted[] = $key;
                    }
                // $value is email
                } else if (is_string($value) && str_contains($value, '@')) {
                    if (!empty($key) && !is_numeric($key)) {
                        $formatted[] = '"' . $key . '" <' . $value . '>';
                    } else {
                        $formatted[] = $value;
                    }
                }
            }
        }

        return implode(', ', $formatted);
    }

    /**
     * Set body
     *
     * @param  mixed $body
     * @return Message
     */
    public function setBody(mixed $body): Message
    {
        if (!($body instanceof Part)) {
            $body = Text::create($body);
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
            $text = Text::create($text);
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
            $html = Html::create($html);
        }
        return $this->addPart($html);
    }

    /**
     * Attach file message part
     *
     * @param  string             $file
     * @param  Part\Body\Encoding $encoding
     * @return Message
     */
    public function attachFile(string $file, Part\Body\Encoding $encoding = Part\Body\Encoding::BASE64): Message
    {
        return $this->addPart(Attachment::create($file, null, 'attachment', $encoding));
    }

    /**
     * Attach file message part from stream
     *
     * @param  string             $stream
     * @param  string             $basename
     * @param  Part\Body\Encoding $encoding
     * @return Message
     */
    public function attachFileFromStream(
        string $stream, string $basename = 'file.tmp', Part\Body\Encoding $encoding = Part\Body\Encoding::BASE64
    ): Message
    {
        return $this->addPart(Attachment::createFromContent($stream, $basename, null, 'attachment', $encoding));
    }

    /**
     * Add message part
     *
     * Auto-infers the multipart subtype (alternative/mixed) from the nested
     * parts via Part::inferSubType(); when parts exist but no subtype could
     * be inferred (e.g. a single text-only part), defaults to 'mixed' per
     * the "Single-part rendering" spec resolution.
     *
     * @param  Part $part
     * @return Message
     */
    public function addPart(Part $part): Message
    {
        parent::addPart($part);
        $this->inferSubType();
        if ($this->hasParts() && !$this->hasSubType()) {
            $this->setSubType('mixed');
        }
        if ($this->hasParts() && $this->hasHeader('Content-Type')) {
            $this->removeHeader('Content-Type');
        }
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
        return $this->getHeaderValue('Subject');
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
     * Get message parts
     *
     * @return array
     */
    public function getParts(): array
    {
        return $this->parts;
    }

    /**
     * Save message to file on disk
     *
     * @param  string $to
     * @return void
     */
    public function save(string $to): void
    {
        file_put_contents($to, $this->render());
    }

    /**
     * Write this entire entity to a buffer
     *
     * @param  Transport\Smtp\Stream\BufferInterface $is
     * @return void
     */
    public function toByteStream(Transport\Smtp\Stream\BufferInterface $is): void
    {
        $lines = $this->renderAsLines();
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
                            $message->setSubject($value->getValueAsString(0));
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
                            $message->addHeader($header, $value->getValueAsString(0));
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
            $parts = MailPart::parseParts($parsedMessage->getParts());

            foreach ($parts as $part) {
                if ($part->attachment) {
                    $message->addPart(Attachment::createFromContent($part->content, $part->basename ?? 'file.tmp', $part->type));
                } else if (!empty($part->type) && (stripos($part->type, 'html') !== false)) {
                    $message->addPart(Html::create($part->content));
                } else if (!empty($part->type) && (stripos($part->type, 'text') !== false)) {
                    $message->addPart(Text::create($part->content));
                } else {
                    $plain = Text::create($part->content);
                    if (!empty($part->type)) {
                        $plain->addHeader('Content-Type', $part->type);
                    }
                    $message->addPart($plain);
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
        return EncodedWord::decode($text);
    }

    /**
     * Perform a "deep" clone of a message object
     *
     * @return void
     */
    public function __clone(): void
    {
        foreach (get_object_vars($this) as $key => $val) {
            if (is_object($val) || (is_array($val))) {
                $this->{$key} = unserialize(serialize($val));
            }
        }
    }

}
