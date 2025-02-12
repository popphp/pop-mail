<?php
/**
 * Pop PHP Framework (https://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp-framework
 * @author     Nick Sagona, III <dev@noladev.com>
 * @copyright  Copyright (c) 2009-2025 NOLA Interactive, LLC.
 * @license    https://www.popphp.org/license     New BSD License
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
 * @author     Nick Sagona, III <dev@noladev.com>
 * @copyright  Copyright (c) 2009-2025 NOLA Interactive, LLC.
 * @license    https://www.popphp.org/license     New BSD License
 * @version    4.0.4
 */
abstract class AbstractMailClient implements MailClientInterface
{

    /**
     * Mail client host
     * @var ?string
     */
    protected ?string $host = null;

    /**
     * Mail client port
     * @var int|string|null
     */
    protected int|string|null $port = null;

    /**
     * Mail client service (pop, imap, nntp, etc.)
     * @var ?string
     */
    protected ?string $service = null;

    /**
     * Username
     * @var string
     */
    protected string $username = '';

    /**
     * Password
     * @var string
     */
    protected string $password = '';

    /**
     * Current folder
     * @var string
     */
    protected string $folder = '';

    /**
     * Constructor
     *
     * Instantiate the mail client object
     *
     * @param string     $host
     * @param int|string $port
     * @param ?string    $service
     */
    public function __construct(string $host, int|string $port, ?string $service = null)
    {
        $this->setHost($host);
        $this->setPort($port);
        if ($service !== null) {
            $this->setService($service);
        }
    }

    /**
     * Get mail client host
     *
     * @return ?string
     */
    public function getHost(): ?string
    {
        return $this->host;
    }

    /**
     * Get mail client port
     *
     * @return int|string|null
     */
    public function getPort(): int|string|null
    {
        return $this->port;
    }

    /**
     * Get mail client service
     *
     * @return ?string
     */
    public function getService(): ?string
    {
        return $this->service;
    }

    /**
     * Get username
     *
     * @return string
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * Get password
     *
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * Get folder
     *
     * @return string
     */
    public function getFolder(): string
    {
        return $this->folder;
    }

    /**
     * Set mail client host
     *
     * @param  string $host
     * @return AbstractMailClient
     */
    public function setHost(string $host): AbstractMailClient
    {
        $this->host = $host;
        return $this;
    }

    /**
     * Set mail client port
     *
     * @param  int|string $port
     * @return AbstractMailClient
     */
    public function setPort(int|string$port): AbstractMailClient
    {
        $this->port = $port;
        return $this;
    }

    /**
     * Set mail client service
     *
     * @param  string $service
     * @return AbstractMailClient
     */
    public function setService(string $service): AbstractMailClient
    {
        $this->service = $service;
        return $this;
    }

    /**
     * Set username
     *
     * @param  string $username
     * @return AbstractMailClient
     */
    public function setUsername(string $username): AbstractMailClient
    {
        $this->username = $username;
        return $this;
    }

    /**
     * Set password
     *
     * @param  string $password
     * @return AbstractMailClient
     */
    public function setPassword(string $password): AbstractMailClient
    {
        $this->password = $password;
        return $this;
    }

    /**
     * Set folder
     *
     * @param  string $folder
     * @return AbstractMailClient
     */
    public function setFolder(string $folder): AbstractMailClient
    {
        $this->folder = $folder;
        return $this;
    }

}
