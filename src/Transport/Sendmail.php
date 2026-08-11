<?php
/**
 * Pop PHP Framework (https://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp-framework
 * @author     Nick Sagona, III <dev@noladev.com>
 * @copyright  Copyright (c) 2009-2027 NOLA Interactive, LLC.
 * @license    https://www.popphp.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Pop\Mail\Transport;

use Pop\Mail\Message;

/**
 * Sendmail transport class
 *
 * @category   Pop
 * @package    Pop\Mail
 * @author     Nick Sagona, III <dev@noladev.com>
 * @copyright  Copyright (c) 2009-2027 NOLA Interactive, LLC.
 * @license    https://www.popphp.org/license     New BSD License
 * @version    5.0.0
 */
class Sendmail extends AbstractTransport
{

    /**
     * Sendmail params
     * @var ?string
     */
    protected ?string $params = null;

    /**
     * Constructor
     *
     * Instantiate the Sendmail transport object
     *
     * @param ?string $params
     */
    public function __construct(?string $params = null)
    {
        if ($params !== null) {
            $this->setParams($params);
        }
    }

    /**
     * Set the params
     *
     * @param  string $params
     * @return Sendmail
     */
    public function setParams(string $params): Sendmail
    {
        $this->params = $params;
        return $this;
    }

    /**
     * Get the params
     *
     * @return ?string
     */
    public function getParams(): ?string
    {
        return $this->params;
    }

    /**
     * Send the message
     *
     * Body must be computed (and, when the message has parts, the boundary
     * triggered) BEFORE the headers are stringified: getBodyContent() ->
     * Part::renderParts() is what lazily synthesizes the Content-Type:
     * multipart/...; boundary=... header, and Message::getBoundary() is what
     * emits MIME-Version as a side effect. Computing $headers first (the
     * prior ordering) would stringify the header set before those side
     * effects fire, silently dropping Content-Type/MIME-Version from every
     * multipart message sent through this transport. See Message::render()
     * for the same pattern.
     *
     * @param  Message $message
     * @return bool
     */
    public function send(Message $message): bool
    {
        if ($message->hasParts()) {
            $message->getBoundary();
        }

        $body    = $message->getBodyContent();
        $headers = $message->getHeadersAsString(['Subject', 'To']);

        if ($this->params !== null) {
            return mail($message->getHeaderValue('To'), $message->getSubject(), $body, $headers, $this->params);
        } else {
            return mail($message->getHeaderValue('To'), $message->getSubject(), $body, $headers);
        }
    }

}
