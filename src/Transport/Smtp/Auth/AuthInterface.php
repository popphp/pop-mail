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
namespace Pop\Mail\Transport\Smtp\Auth;

use Pop\Mail\Transport\Smtp\AgentInterface;

/**
 * SMTP authenticator interface
 *
 * @category   Pop
 * @package    Pop\Mail
 * @author     Chris Corbyn, from the SwiftMailer library https://github.com/swiftmailer/swiftmailer
 * @version    4.0.3
 */
interface AuthInterface
{

    /**
     * Get the name of the AUTH mechanism this Authenticator handles
     *
     * @return string
     */
    public function getAuthKeyword(): string;

    /**
     * Try to authenticate the user with $username and $password
     *
     * @param  AgentInterface $agent
     * @param  string         $username
     * @param  string         $password
     * @return bool
     */
    public function authenticate(AgentInterface $agent, string $username, string $password): bool;

}
