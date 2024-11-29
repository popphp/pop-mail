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
 * Mail client interface
 *
 * @category   Pop
 * @package    Pop\Mail
 * @author     Nick Sagona, III <dev@noladev.com>
 * @copyright  Copyright (c) 2009-2025 NOLA Interactive, LLC.
 * @license    https://www.popphp.org/license     New BSD License
 * @version    4.0.3
 */
interface MailClientInterface
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
     * @return MailClientInterface
     */
    public function setHost(string $host): MailClientInterface;

    /**
     * Set mail client port
     *
     * @param  int|string $port
     * @return MailClientInterface
     */
    public function setPort(int|string $port): MailClientInterface;

    /**
     * Set mail client service
     *
     * @param  string $service
     * @return MailClientInterface
     */
    public function setService(string $service): MailClientInterface;

    /**
     * Set username
     *
     * @param  string $username
     * @return MailClientInterface
     */
    public function setUsername(string $username): MailClientInterface;

    /**
     * Set password
     *
     * @param  string $password
     * @return MailClientInterface
     */
    public function setPassword(string $password): MailClientInterface;

    /**
     * Set folder
     *
     * @param  string $folder
     * @return MailClientInterface
     */
    public function setFolder(string $folder): MailClientInterface;

}
