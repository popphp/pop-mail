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
 * NTLM Auth class
 *
 * @category   Pop
 * @package    Pop\Mail
 * @author     xu.li & Chris Corbyn, from the SwiftMailer library https://github.com/swiftmailer/swiftmailer
 * @see        https://developers.google.com/google-apps/gmail/xoauth2_protocol
 * @version    4.0.0
 */
class XOAuth2Authenticator implements AuthInterface
{

    /**
     * Get the name of the AUTH mechanism this Authenticator handles
     *
     * @return string
     */
    public function getAuthKeyword(): string
    {
        return 'XOAUTH2';
    }

    /**
     * Try to authenticate the user with $email and $token
     *
     * @param  AgentInterface $agent
     * @param  string         $email
     * @param  string         $token
     * @return bool
     */
    public function authenticate(AgentInterface $agent, string $username, string $password): bool
    {
        try {
            $param = $this->constructXOAuth2Params($username, $password);
            $agent->executeCommand('AUTH XOAUTH2 ' . $param . "\r\n", [235]);

            return true;
        } catch (Exception $e) {
            $agent->executeCommand("RSET\r\n", [250]);

            return false;
        }
    }

    /**
     * Construct the auth parameter
     *
     * @param  string $email
     * @param  string $token
     * @see    https://developers.google.com/google-apps/gmail/xoauth2_protocol#the_sasl_xoauth2_mechanism
     * @return string
     */
    protected function constructXOAuth2Params(string $email, string $token): string
    {
        return base64_encode("user=$email\1auth=Bearer $token\1\1");
    }

}
