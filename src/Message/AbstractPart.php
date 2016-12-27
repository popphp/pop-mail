<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp-framework
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2016 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
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
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2016 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    3.0.0
 */
abstract class AbstractPart extends AbstractMessage implements PartInterface
{

    /**
     * Message part content
     * @var string
     */
    protected $content = null;

    /**
     * Constructor
     *
     * Instantiate the message part object
     *
     * @param  string $content
     * @param  string $contentType
     */
    public function __construct($content, $contentType = 'text/plain')
    {
        $this->setContent($content);
        $this->setContentType($contentType);
    }

    /**
     * Get message part content
     *
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Set message part content
     *
     * @param  string $content
     * @return AbstractPart
     */
    public function setContent($content)
    {
        $this->content = $content;
        return $this;
    }

    /**
     * Get message body
     *
     * @return string
     */
    public function getBody()
    {
        return $this->getContent();
    }

    /**
     * Render message
     *
     * @return string
     */
    public function render()
    {
        return $this->getHeadersAsString() . Message::CRLF . Message::CRLF . $this->getBody() . Message::CRLF . Message::CRLF;
    }

}
