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
namespace Pop\Mail\Transport\Smtp\Auth;

use Pop\Mail\Transport\Smtp\AgentInterface;

/**
 * LOGIN Auth class
 *
 * @category   Pop
 * @package    Pop\Mail
 * @author     Chris Corbyn, from the SwiftMailer library https://github.com/swiftmailer/swiftmailer
 * @version    3.5.0
 */
class LoginAuthenticator implements AuthInterface
{

    /**
     * Get the name of the AUTH mechanism this Authenticator handles
     *
     * @return string
     */
    public function getAuthKeyword()
    {
        return 'LOGIN';
    }

    /**
     * Try to authenticate the user with $username and $password
     *
     * @param  AgentInterface $agent
     * @param  string         $username
     * @param  string         $password
     * @return bool
     */
    public function authenticate(AgentInterface $agent, $username, $password)
    {
        try {
            $agent->executeCommand("AUTH LOGIN\r\n", [334]);
            $agent->executeCommand(sprintf("%s\r\n", base64_encode($username)), [334]);
            $agent->executeCommand(sprintf("%s\r\n", base64_encode($password)), [235]);

            return true;
        } catch (Exception $e) {
            $agent->executeCommand("RSET\r\n", [250]);

            return false;
        }
    }

}
