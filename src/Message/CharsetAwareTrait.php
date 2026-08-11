<?php
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
 */
trait CharsetAwareTrait
{

    protected ?string $charSet = null;

    public function setContentType(string $contentType): static
    {
        $this->addHeader('Content-Type', $contentType);
        return $this;
    }

    public function getCharSet(): ?string
    {
        return $this->charSet;
    }

    public function setCharSet(?string $charSet = null): static
    {
        $this->charSet = $charSet;
        return $this;
    }

}
