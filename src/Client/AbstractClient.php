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
namespace Pop\Mail\Client;

/**
 * Abstract mail client class
 *
 * @category   Pop
 * @package    Pop\Mail
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2020 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    3.5.0
 */
abstract class AbstractClient implements ClientInterface
{

    /**
     * Mail client host
     * @var string
     */
    protected $host = null;

    /**
     * Mail client port
     * @var int
     */
    protected $port = null;

    /**
     * Mail client service (pop, imap, nntp, etc.)
     * @var string
     */
    protected $service = null;

    /**
     * Username
     * @var string
     */
    protected $username = '';

    /**
     * Password
     * @var string
     */
    protected $password = '';

    /**
     * Current folder
     * @var string
     */
    protected $folder = '';

    /**
     * Constructor
     *
     * Instantiate the mail client object
     *
     * @param string $host
     * @param int    $port
     * @param string $service
     */
    public function __construct($host, $port, $service = null)
    {
        $this->setHost($host);
        $this->setPort($port);
        if (null !== $service) {
            $this->setService($service);
        }
    }

    /**
     * Get mail client host
     *
     * @return string
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * Get mail client port
     *
     * @return int
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * Get mail client service
     *
     * @return string
     */
    public function getService()
    {
        return $this->service;
    }

    /**
     * Get username
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Get password
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Get folder
     *
     * @return string
     */
    public function getFolder()
    {
        return $this->folder;
    }

    /**
     * Set mail client host
     *
     * @param  string $host
     * @return AbstractClient
     */
    public function setHost($host)
    {
        $this->host = $host;
        return $this;
    }

    /**
     * Set mail client port
     *
     * @param  int $port
     * @return AbstractClient
     */
    public function setPort($port)
    {
        $this->port = $port;
        return $this;
    }

    /**
     * Set mail client service
     *
     * @param  string $service
     * @return AbstractClient
     */
    public function setService($service)
    {
        $this->service = $service;
        return $this;
    }

    /**
     * Set username
     *
     * @param  string $username
     * @return AbstractClient
     */
    public function setUsername($username)
    {
        $this->username = $username;
        return $this;
    }

    /**
     * Set password
     *
     * @param  string $password
     * @return AbstractClient
     */
    public function setPassword($password)
    {
        $this->password = $password;
        return $this;
    }

    /**
     * Set folder
     *
     * @param  string $folder
     * @return AbstractClient
     */
    public function setFolder($folder)
    {
        $this->folder = $folder;
        return $this;
    }

}
