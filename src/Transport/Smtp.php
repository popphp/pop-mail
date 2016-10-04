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
 * Mail SMTP transport class
 *
 * @category   Pop
 * @package    Pop_Mail
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
     * Constructor
     *
     * Instantiate the sendmail transport object
     *
     * @param  string $host
     * @param  int    $port
     * @param  string $username
     * @param  string $password
     * @return Smtp
     */
    public function __construct($host = 'localhost', $port = 25, $username = null, $password = null)
    {
        if (null !== $host) {
            $this->setHost($host);
        }
        if (null !== $port) {
            $this->setPort($port);
        }
        if (null !== $username) {
            $this->setUsername($username);
        }
        if (null !== $password) {
            $this->setPassword($password);
        }
    }

    /**
     * Set the host
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
     * Set the port
     *
     * @param  int $port
     * @return Smtp
     */
    public function setPort($port)
    {
        $this->port = (int)$port;
        return $this;
    }

    /**
     * Set the username
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
     * Set the password
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
     * Get the host
     *
     * @return string
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * Get the port
     *
     * @return int
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * Get the username
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Get the password
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
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
        return;
    }

}
