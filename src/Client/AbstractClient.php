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
namespace Pop\Mail\Client;

/**
 * Abstract mail client class
 *
 * @category   Pop
 * @package    Pop\Mail
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2016 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    3.0.0
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
     * Mailbox resource
     * @var resource
     */
    protected $mailbox = null;

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
    public function __construct($host, $port, $service)
    {
        $this->host     = $host;
        $this->port     = $port;
        $this->service  = $service;
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

    /**
     * Open mailbox
     *
     * @param string $flags
     * @param int    $options
     * @param int    $retries
     * @param array  $params
     * @return AbstractClient
     */
    public function open($flags = null, $options = null, $retries = 0, array $params = null)
    {
        $connection = '{' . $this->host . ':' . $this->port . '/' . $this->service;

        if (null !== $flags) {
            $connection .= $flags;
        }

        $connection .= '}' . $this->folder;

        $this->mailbox = imap_open($connection, $this->username, $this->password, $options, $retries, $params);
        return $this;
    }

    /**
     * Determine if the mailbox has been opened
     *
     * @return boolean
     */
    public function isOpen()
    {
        return is_resource($this->mailbox);
    }

    /**
     * Get mailbox
     *
     * @return resource
     */
    public function mailbox()
    {
        return $this->mailbox;
    }

}
