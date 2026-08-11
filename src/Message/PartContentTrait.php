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
 * Adds getContent()/setContent() convenience accessors on top of
 * Pop\Mime\Part, which has no direct equivalents.
 */
trait PartContentTrait
{

    public function getContent(): ?string
    {
        return $this->hasBody() ? $this->getBody()->getContent() : null;
    }

    public function setContent(string $content): static
    {
        $this->setBody($content);
        return $this;
    }

    public function renderAsLines(): array
    {
        $lines = explode("\r\n", $this->render());
        return array_map('trim', $lines);
    }

}
