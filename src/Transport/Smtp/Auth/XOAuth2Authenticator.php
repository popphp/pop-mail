<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp-framework
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2019 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
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
 * @version    3.1.0
 */
class XOAuth2Authenticator implements AuthInterface
{

    /**
     * Get the name of the AUTH mechanism this Authenticator handles
     *
     * @return string
     */
    public function getAuthKeyword()
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
    public function authenticate(AgentInterface $agent, $email, $token)
    {
        try {
            $param = $this->constructXOAuth2Params($email, $token);
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
    protected function constructXOAuth2Params($email, $token)
    {
        return base64_encode("user=$email\1auth=Bearer $token\1\1");
    }

}
