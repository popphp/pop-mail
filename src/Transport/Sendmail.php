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
namespace Pop\Mail\Transport;

use Pop\Mail\Message;

/**
 * Sendmail transport class
 *
 * @category   Pop
 * @package    Pop\Mail
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2024 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    4.0.0
 */
class Sendmail extends AbstractTransport
{

    /**
     * Sendmail params
     * @var string
     */
    protected $params = null;

    /**
     * Constructor
     *
     * Instantiate the Sendmail transport object
     *
     * @param  string $params
     */
    public function __construct($params = null)
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
    public function setParams($params)
    {
        $this->params = $params;
        return $this;
    }

    /**
     * Get the params
     *
     * @return string
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * Send the message
     *
     * @param  Message $message
     * @return bool
     */
    public function send(Message $message)
    {
        $headers = $message->getHeadersAsString(['Subject', 'To']);

        if (($headers !== null) && ($this->params !== null)) {
            return mail($message->getHeader('To'), $message->getSubject(), $message->getBody(), $headers, $this->params);
        } else if ($headers !== null) {
            return mail($message->getHeader('To'), $message->getSubject(), $message->getBody(), $headers);
        } else {
            return mail($message->getHeader('To'), $message->getSubject(), $message->getBody());
        }
    }

}
