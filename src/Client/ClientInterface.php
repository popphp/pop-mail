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
 * Mail client interface
 *
 * @category   Pop
 * @package    Pop\Mail
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2016 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    3.0.0
 */
interface ClientInterface
{

    /**
     * Get mail client host
     *
     * @return string
     */
    public function getHost();

    /**
     * Get mail client port
     *
     * @return int
     */
    public function getPort();

    /**
     * Get mail client service
     *
     * @return string
     */
    public function getService();

    /**
     * Get username
     *
     * @return string
     */
    public function getUsername();

    /**
     * Get password
     *
     * @return string
     */
    public function getPassword();

    /**
     * Get folder
     *
     * @return string
     */
    public function getFolder();

    /**
     * Set username
     *
     * @param  string $username
     * @return ClientInterface
     */
    public function setUsername($username);

    /**
     * Set password
     *
     * @param  string $password
     * @return ClientInterface
     */
    public function setPassword($password);

    /**
     * Set folder
     *
     * @param  string $folder
     * @return ClientInterface
     */
    public function setFolder($folder);

}
