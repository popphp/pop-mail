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

use Pop\Utils;

/**
 * Message part object class
 *
 * @category   Pop
 * @package    Pop\Mail
 * @author     Nick Sagona, III <dev@noladev.com>
 * @copyright  Copyright (c) 2009-2025 NOLA Interactive, LLC.
 * @license    https://www.popphp.org/license     New BSD License
 * @version    4.0.4
 */
class Part extends Utils\ArrayObject
{

    /**
     * Parse message parts from string
     *
     * @param  mixed   $body
     * @param  ?string $boundary
     * @return array
     */
    public static function parse(mixed $body, ?string $boundary = null): array
    {
        $partStrings = \Pop\Mime\Message::parseBody($body, $boundary);
        $parts       = [];

        foreach ($partStrings as $partString) {
            $parts[] = \Pop\Mime\Message::parsePart($partString);
        }

        return self::parseParts($parts);
    }

    /**
     * Parse message parts from array of parts
     *
     * @param  array $parts
     * @return array
     */
    public static function parseParts(array $parts): array
    {
        $flattenedParts = [];

        foreach ($parts as $part) {
            if (is_array($part)) {
                $flattenedParts = array_merge($flattenedParts, self::parseParts($part));
            } else {
                $flattenedParts[] = new static([
                    'headers'    => $part->getHeadersAsArray(),
                    'type'       => (($part->hasHeader('Content-Type')) && (count($part->getHeader('Content-Type')->getValues()) == 1)) ?
                        $part->getHeader('Content-Type')->getValue(0) : null,
                    'attachment' => (($part->hasBody()) && ($part->getBody()->isFile())),
                    'basename'   => $part->getFilename(),
                    'content'    => $part->getContents()
                ]);
            }
        }

        return $flattenedParts;
    }

}
