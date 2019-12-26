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
namespace Pop\Mail\Message;

use Pop\Utils;

/**
 * Message part object class
 *
 * @category   Pop
 * @package    Pop\Mail
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2020 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    3.5.0
 */
class Part extends Utils\ArrayObject
{

    /**
     * Parse message parts from string
     *
     * @param  mixed $body
     * @param  string $boundary
     * @return array
     */
    public static function parse($body, $boundary = null)
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
    public static function parseParts(array $parts)
    {
        $flattenedParts = [];

        foreach ($parts as $part) {
            if (is_array($part)) {
                $flattenedParts = array_merge($flattenedParts, self::parseParts($part));
            } else {
                $flattenedParts[] = new static([
                    'headers'    => $part->getHeadersAsArray(),
                    'type'       => ($part->hasHeader('Content-Type')) ? $part->getHeader('Content-Type')->getValue() : null,
                    'attachment' => (($part->hasBody()) && ($part->getBody()->isFile())),
                    'basename'   => $part->getFilename(),
                    'content'    => $part->getContents()
                ]);
            }
        }

        return $flattenedParts;
    }

}
