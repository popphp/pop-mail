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

/**
 * Mail message interface
 *
 * @category   Pop
 * @package    Pop\Mail
 * @author     Nick Sagona, III <dev@noladev.com>
 * @copyright  Copyright (c) 2009-2025 NOLA Interactive, LLC.
 * @license    https://www.popphp.org/license     New BSD License
 * @version    4.0.4
 */
interface MessageInterface
{

    /**
     * Add header
     *
     * @param  string $header
     * @param  string $value
     * @return MessageInterface
     */
    public function addHeader(string $header, string $value): MessageInterface;

    /**
     * Add headers
     *
     * @param  array $headers
     * @return MessageInterface
     */
    public function addHeaders(array $headers): MessageInterface;

    /**
     * Determine if message has header
     *
     * @param  string $header
     * @return bool
     */
    public function hasHeader(string $header): bool;

    /**
     * Get header
     *
     * @param  string $header
     * @return string
     */
    public function getHeader(string $header): ?string;

    /**
     * Get all headers
     *
     * @return array
     */
    public function getHeaders(): array;

    /**
     * Get header as string
     *
     * @param  string $header
     * @return string|null
     */
    public function getHeaderAsString(string $header): string|null;

    /**
     * Get all headers as string
     *
     * @param  array $omitHeaders
     * @return string|null
     */
    public function getHeadersAsString(array $omitHeaders = []): string|null;

    /**
     * Get content type
     *
     * @return ?string
     */
    public function getContentType(): ?string;

    /**
     * Get character set
     *
     * @return ?string
     */
    public function getCharSet(): ?string;

    /**
     * Get body
     *
     * @return ?string
     */
    public function getBody(): ?string;

    /**
     * Set content type
     *
     * @param  string $contentType
     * @return MessageInterface
     */
    public function setContentType(string $contentType): MessageInterface;

    /**
     * Set character set
     *
     * @param  string $charSet
     * @return MessageInterface
     */
    public function setCharSet(string $charSet): MessageInterface;

    /**
     * Render
     *
     * @return string
     */
    public function render(): string;

    /**
     * Render as an array of lines
     *
     * @return array
     */
    public function renderAsLines(): array;

    /**
     * Render to string
     *
     * @return string
     */
    public function __toString(): string;

}
