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
 * Abstract mail message class
 *
 * @category   Pop
 * @package    Pop\Mail
 * @author     Nick Sagona, III <dev@noladev.com>
 * @copyright  Copyright (c) 2009-2025 NOLA Interactive, LLC.
 * @license    https://www.popphp.org/license     New BSD License
 * @version    4.0.3
 */
abstract class AbstractMessage implements MessageInterface
{

    /**
     * Headers
     * @var array
     */
    protected array $headers = [];

    /**
     * Content type
     * @var ?string
     */
    protected ?string $contentType = null;

    /**
     * Character set
     * @var ?string
     */
    protected ?string $charSet = null;

    /**
     * Message or part ID
     * @var ?string
     */
    protected ?string $id = null;

    /**
     * Message or part ID header name
     * @var ?string
     */
    protected ?string $idHeader = null;

    /**
     * Constructor
     *
     * Instantiate the message object
     */
    public function __construct()
    {

    }

    /**
     * Add message part header
     *
     * @param  string $header
     * @param  string $value
     * @return AbstractMessage
     */
    public function addHeader(string $header, string $value): AbstractMessage
    {
        $this->headers[$header] = $value;
        return $this;
    }

    /**
     * Add message part headers
     *
     * @param  array $headers
     * @return AbstractMessage
     */
    public function addHeaders(array $headers): AbstractMessage
    {
        foreach ($headers as $header => $value) {
            $this->addHeader($header, $value);
        }
        return $this;
    }

    /**
     * Determine if message part has header
     *
     * @param  string $header
     * @return bool
     */
    public function hasHeader(string $header): bool
    {
        return isset($this->headers[$header]);
    }

    /**
     * Get message part header
     *
     * @param  string $header
     * @return ?string
     */
    public function getHeader(string $header): ?string
    {
        return $this->headers[$header] ?? null;
    }

    /**
     * Get all message part headers
     *
     * @return array
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * Get message part content type
     *
     * @return ?string
     */
    public function getContentType(): ?string
    {
        return $this->contentType;
    }

    /**
     * Get message part character set
     *
     * @return ?string
     */
    public function getCharSet(): ?string
    {
        return $this->charSet;
    }

    /**
     * Set message part content type
     *
     * @param  string $contentType
     * @return AbstractMessage
     */
    public function setContentType(string $contentType): AbstractMessage
    {
        $this->contentType = $contentType;
        return $this;
    }

    /**
     * Set message part character set
     *
     * @param  ?string $charSet
     * @return AbstractMessage
     */
    public function setCharSet(?string $charSet = null): AbstractMessage
    {
        $this->charSet = $charSet;
        return $this;
    }

    /**
     * Get header as string
     *
     * @param  string $header
     * @return string|null
     */
    public function getHeaderAsString(string $header): string|null
    {
        return ($this->hasHeader($header)) ? $header . ': ' . $this->getHeader($header) : null;
    }

    /**
     * Get all message headers as string
     *
     * @param  array $omitHeaders
     * @return string
     */
    public function getHeadersAsString(array $omitHeaders = []): string
    {
        $headers = null;

        foreach ($this->headers as $header => $value) {
            if (!in_array($header, $omitHeaders) && !empty($value)) {
                $headers .= $header . ': ' . $value . Message::CRLF;
            }
        }

        if ($this->id !== null) {
            if ($this->idHeader === null) {
                $this->setIdHeader((($this instanceof Message) ? 'Message-ID' : 'Content-ID'));
            }

            if (!in_array($this->idHeader, $omitHeaders)) {
                $headers .= $this->idHeader . ': ' . $this->id . Message::CRLF;
            }
        }

        if (($this->contentType !== null) && !in_array('Content-Type', $omitHeaders)) {
            $headers .= 'Content-Type: ' . $this->contentType;
            if (!empty($this->charSet) && (stripos($this->contentType, 'charset') === false)) {
                $headers .= '; charset="' . $this->charSet . '"';
            }
            $headers .= Message::CRLF;
        }

        if ((!str_contains($headers, 'Content-Type')) && (count($this->parts) == 1)) {
            $contentType = $this->parts[0]->getContentType();
            if ($contentType !== null) {
                $headers .= 'Content-Type: ' . $contentType . Message::CRLF;
            }
        }

        return $headers;
    }

    /**
     * Set the ID header name
     *
     * @param  string $header
     * @return AbstractMessage
     */
    public function setIdHeader(string $header): AbstractMessage
    {
        $this->idHeader = $header;
        return $this;
    }

    /**
     * Get the ID
     *
     * @return ?string
     */
    public function getIdHeader(): ?string
    {
        return $this->idHeader;
    }

    /**
     * Set the ID
     *
     * @param  string $id
     * @return AbstractMessage
     */
    public function setId(string $id): AbstractMessage
    {
        $this->id = $id;
        return $this;
    }

    /**
     * Get the ID
     *
     * @return ?string
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * Generate a new ID
     *
     * @string ?string $domain
     * @return string
     */
    public function generateId(?string $domain = null): string
    {
        $this->setId($this->getRandomId($domain));
        return $this->id;
    }

    /**
     * Returns a random ID
     *
     * @string ?string $idRight
     * @return string
     */
    protected function getRandomId(?string $idRight = null): string
    {
        $idLeft = md5(getmypid().'.'.time().'.'.uniqid(mt_rand(), true));
        if ($idRight === null) {
            $idRight = !empty($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : 'localhost';
        }
        return '<' . $idLeft . '@' . $idRight . '>';
    }

    /**
     * Get body
     *
     * @return ?string
     */
    abstract public function getBody(): ?string;

    /**
     * Render
     *
     * @return string
     */
    abstract public function render(): string;

    /**
     * Render as an array of lines
     *
     * @return array
     */
    abstract public function renderAsLines(): array;

    /**
     * Render message to string
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->render();
    }

}
