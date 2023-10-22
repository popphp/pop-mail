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
namespace Pop\Mail\Client;

/**
 * Mail client interface
 *
 * @category   Pop
 * @package    Pop\Mail
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2024 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    4.0.0
 */
interface ClientInterface
{

    /**
     * Get mail client host
     *
     * @return ?string
     */
    public function getHost(): ?string;

    /**
     * Get mail client port
     *
     * @return int|string|null
     */
    public function getPort(): int|string|null;

    /**
     * Get mail client service
     *
     * @return ?string
     */
    public function getService(): ?string;

    /**
     * Get username
     *
     * @return string
     */
    public function getUsername(): string;

    /**
     * Get password
     *
     * @return string
     */
    public function getPassword(): string;

    /**
     * Get folder
     *
     * @return string
     */
    public function getFolder(): string;

    /**
     * Set mail client host
     *
     * @param  string $host
     * @return ClientInterface
     */
    public function setHost(string $host): ClientInterface;

    /**
     * Set mail client port
     *
     * @param  int|string $port
     * @return ClientInterface
     */
    public function setPort(int|string $port): ClientInterface;

    /**
     * Set mail client service
     *
     * @param  string $service
     * @return ClientInterface
     */
    public function setService(string $service): ClientInterface;

    /**
     * Set username
     *
     * @param  string $username
     * @return ClientInterface
     */
    public function setUsername(string $username): ClientInterface;

    /**
     * Set password
     *
     * @param  string $password
     * @return ClientInterface
     */
    public function setPassword(string $password): ClientInterface;

    /**
     * Set folder
     *
     * @param  string $folder
     * @return ClientInterface
     */
    public function setFolder(string $folder): ClientInterface;

}
