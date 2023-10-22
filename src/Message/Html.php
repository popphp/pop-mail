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
namespace Pop\Mail\Message;

/**
 * HTML message part class
 *
 * @category   Pop
 * @package    Pop\Mail
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2024 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    4.0.0
 */
class Html extends AbstractPart
{

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
    public function __construct(string $content, string $contentType = 'text/html', ?string $encoding = null, bool $chunk = false)
    {
        parent::__construct($content, $contentType, $encoding, $chunk);
    }

}
