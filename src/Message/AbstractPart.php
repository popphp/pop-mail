<?php
/**
 * Pop PHP Framework (https://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp-framework
 * @author     Nick Sagona, III <dev@noladev.com>
 * @copyright  Copyright (c) 2009-2025 NOLA Interactive, LLC.
 * @license    https://www.popphp.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Pop\Mail\Message;

use Pop\Mail\Message;

/**
 * Abstract mail message part class
 *
 * @category   Pop
 * @package    Pop\Mail
 * @author     Nick Sagona, III <dev@noladev.com>
 * @copyright  Copyright (c) 2009-2025 NOLA Interactive, LLC.
 * @license    https://www.popphp.org/license     New BSD License
 * @version    4.0.3
 */
abstract class AbstractPart extends AbstractMessage implements PartInterface
{

    /**
     * Encoding constants
     * @var string
     */
    const BASE64           = 'BASE64';
    const QUOTED_PRINTABLE = 'QUOTED_PRINTABLE';
    const BINARY           = 'BINARY';
    const _8BIT            = '_8BIT';
    const _7BIT            = '_7BIT';

    /**
     * Message part content
     * @var ?string
     */
    protected ?string $content = null;

    /**
     * Message part encoding
     * @var ?string
     */
    protected ?string $encoding = null;

    /**
     * Constructor
     *
     * Instantiate the message part object
     *
     * @param string  $content
     * @param string  $contentType
     * @param ?string $encoding
     * @param bool    $chunk
     */
    public function __construct(string $content, string $contentType = 'text/plain', ?string $encoding = null, bool $chunk = false)
    {
        if ($encoding !== null) {
            $this->setEncoding($encoding);

            switch ($this->encoding) {
                case self::BASE64:
                    $content = base64_encode($content);
                    $this->addHeader('Content-Transfer-Encoding', 'base64');
                    break;
                case self::QUOTED_PRINTABLE:
                    $content = quoted_printable_encode($content);
                    $this->addHeader('Content-Transfer-Encoding', 'quoted-printable');
                    break;
                case self::BINARY:
                    $this->addHeader('Content-Transfer-Encoding', 'binary');
                    break;
                case self::_7BIT:
                    $this->addHeader('Content-Transfer-Encoding', '7bit');
                    break;
                case self::_8BIT:
                    $this->addHeader('Content-Transfer-Encoding', '8bit');
                    break;
            }
        }

        if ($chunk) {
            $content = chunk_split($content);
        }

        $this->setContent($content);
        $this->setContentType($contentType);
    }

    /**
     * Get message part content
     *
     * @return ?string
     */
    public function getContent(): ?string
    {
        return $this->content;
    }

    /**
     * Set message part content
     *
     * @param  string $content
     * @return AbstractPart
     */
    public function setContent(string $content): AbstractPart
    {
        $this->content = $content;
        return $this;
    }

    /**
     * Get message part encoding
     *
     * @return ?string
     */
    public function getEncoding(): ?string
    {
        return $this->encoding;
    }

    /**
     * Set message part encoding
     *
     * @param  string $encoding
     * @return AbstractPart
     */
    public function setEncoding(string $encoding): AbstractPart
    {
        $this->encoding = $encoding;
        return $this;
    }

    /**
     * Check if encoding is base64
     *
     * @return bool
     */
    public function isBase64(): bool
    {
        return ($this->encoding == self::BASE64);
    }

    /**
     * Check if encoding is quoted printable
     *
     * @return bool
     */
    public function isQuotedPrintable(): bool
    {
        return ($this->encoding == self::QUOTED_PRINTABLE);
    }

    /**
     * Check if encoding is 8-bit
     *
     * @return bool
     */
    public function is8Bit(): bool
    {
        return ($this->encoding == self::_8BIT);
    }

    /**
     * Check if encoding is 7-bit
     *
     * @return bool
     */
    public function is7Bit(): bool
    {
        return ($this->encoding == self::_7BIT);
    }

    /**
     * Get message body
     *
     * @return ?string
     */
    public function getBody(): ?string
    {
        return $this->getContent();
    }

    /**
     * Render message
     *
     * @param  array $omitHeaders
     * @return string
     */
    public function render(array $omitHeaders = []): string
    {
        return $this->getHeadersAsString($omitHeaders) . Message::CRLF . $this->getBody() . Message::CRLF . Message::CRLF;
    }

    /**
     * Render as an array of lines
     *
     * @param  array $omitHeaders
     * @return array
     */
    public function renderAsLines(array $omitHeaders = []): array
    {
        $lines = [];

        $headers = explode(Message::CRLF, $this->getHeadersAsString($omitHeaders) . Message::CRLF);
        $body    = explode(Message::CRLF, $this->getContent());

        foreach ($headers as $header) {
            $lines[] = trim($header);
        }

        foreach ($body as $line) {
            $lines[] = trim($line);
        }

        $lines[] = Message::CRLF;
        $lines[] = Message::CRLF;

        return $lines;
    }

}
