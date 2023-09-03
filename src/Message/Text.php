<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp-framework
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2023 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Pop\Mail\Message;

/**
 * Text message part class
 *
 * @category   Pop
 * @package    Pop\Mail
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2023 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    3.8.0
 */
class Text extends AbstractPart
{

    /**
     * Constructor
     *
     * Instantiate the message part object
     *
     * @param string  $content
     * @param string  $contentType
     * @param string  $encoding
     * @param boolean $chunk
     */
    public function __construct($content, $contentType = 'text/plain', $encoding = null, $chunk = false)
    {
        parent::__construct($content, $contentType, $encoding, $chunk);
    }

}
