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
namespace Pop\Mail\Transport;

/**
 * Mail sendmail transport class
 *
 * @category   Pop
 * @package    Pop\Mail
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2016 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    3.0.0
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
     * Instantiate the sendmail transport object
     *
     * @param  string $headers
     * @param  string $params
     */
    public function __construct($headers = null, $params = null)
    {
        if (null !== $headers) {
            $this->setHeaders($headers);
        }
        if (null !== $params) {
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
     * Send the mail
     *
     * @param  string  $to
     * @param  string  $subject
     * @param  string  $message
     * @return boolean
     */
    public function send($to, $subject, $message)
    {
        return mail($to, $subject, $message, $this->headers, $this->params);
    }

}
