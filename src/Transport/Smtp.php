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

use Pop\Mail\Message;

/**
 * SMTP transport class
 *
 * @category   Pop
 * @package    Pop\Mail
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2016 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    3.0.0
 */
class Smtp extends AbstractTransport
{

    /**
     * SMTP host
     * @var string
     */
    protected $host = 'localhost';

    /**
     * SMTP port
     * @var int
     */
    protected $port = 25;

    /**
     * SMTP username
     * @var string
     */
    protected $username = null;

    /**
     * SMTP password
     * @var string
     */
    protected $password = null;

    /**
     * SMTP protocol
     * @var boolean
     */
    protected $protocol = null;

    /**
     * SMTP TLS
     * @var boolean
     */
    protected $tls = false;

    /**
     * Constructor
     *
     * Instantiate the SMTP object
     *
     * @param string $host
     * @param int    $port
     * @param string $security
     */
    public function __construct($host = 'localhost', $port = 25, $security = null)
    {
        $this->setHost($host);
        $this->setPort($port);
        $this->setSecurity($security);
    }

    /**
     * Set the SMTP host
     *
     * @param  string $host
     * @return Smtp
     */
    public function setHost($host)
    {
        $this->host = $host;
        return $this;
    }

    /**
     * Set the SMTP port
     *
     * @param  int $port
     * @return Smtp
     */
    public function setPort($port)
    {
        $this->port = $port;
        return $this;
    }

    /**
     * Set the SMTP security
     *
     * @param  string $security
     * @return Smtp
     */
    public function setSecurity($security)
    {
        $protocol = strtolower($security);

        if ($protocol == 'tls') {
            $this->protocol = 'tcp';
            $this->tls      = true;
        } else {
            $this->protocol = $protocol;
            $this->tls      = false;
        }

        return $this;
    }

    /**
     * Set the SMTP username
     *
     * @param  string $username
     * @return Smtp
     */
    public function setUsername($username)
    {
        $this->username = $username;
        return $this;
    }

    /**
     * Set the SMTP password
     *
     * @param  string $password
     * @return Smtp
     */
    public function setPassword($password)
    {
        $this->password = $password;
        return $this;
    }

    /**
     * Get the SMTP host
     *
     * @return string
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * Get the SMTP port
     *
     * @return int
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * Get the SMTP protocol
     *
     * @return string
     */
    public function getProtocol()
    {
        return $this->protocol;
    }

    /**
     * Determine if SMTP is using TLS
     *
     * @return boolean
     */
    public function isTls()
    {
        return $this->tls;
    }

    /**
     * Get the SMTP username
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Get the SMTP password
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Send the message
     *
     * @param  Message $message
     * @return mixed
     */
    public function send(Message $message)
    {
        return;
    }

}
