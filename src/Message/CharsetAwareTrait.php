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

namespace Pop\Mail\Message;

/**
 * Restores AbstractMessage's charset/content-type field concept on top of
 * Pop\Mime\Part, which only models Content-Type as a header.
 *
 * @category   Pop
 * @package    Pop\Mail
 * @author     Nick Sagona, III <dev@noladev.com>
 * @copyright  Copyright (c) 2009-2027 NOLA Interactive, LLC.
 * @license    https://www.popphp.org/license     New BSD License
 * @version    5.0.0
 */
trait CharsetAwareTrait
{

    /**
     * Character set
     * @var ?string
     */
    protected ?string $charSet = null;

    /**
     * Set Content-Type header
     *
     * @param  string $contentType
     * @return static
     */
    public function setContentType(string $contentType): static
    {
        $this->addHeader('Content-Type', $contentType);
        return $this;
    }

    /**
     * Get character set
     *
     * @return ?string
     */
    public function getCharSet(): ?string
    {
        return $this->charSet;
    }

    /**
     * Set character set
     *
     * @param  ?string $charSet
     * @return static
     */
    public function setCharSet(?string $charSet = null): static
    {
        $this->charSet = $charSet;
        return $this;
    }

}
