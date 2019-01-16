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
 * CRAM-MD5 Auth class
 *
 * @category   Pop
 * @package    Pop\Mail
 * @author     Chris Corbyn, from the SwiftMailer library https://github.com/swiftmailer/swiftmailer
 * @version    3.1.0
 */
class CramMd5Authenticator implements AuthInterface
{

    /**
     * Get the name of the AUTH mechanism this Authenticator handles
     *
     * @return string
     */
    public function getAuthKeyword()
    {
        return 'CRAM-MD5';
    }

    /**
     * Try to authenticate the user with $username and $password
     *
     * @param  AgentInterface  $agent
     * @param  string          $username
     * @param  string          $password
     * @return bool
     */
    public function authenticate(AgentInterface $agent, $username, $password)
    {
        try {
            $challenge = $agent->executeCommand("AUTH CRAM-MD5\r\n", [334]);
            $challenge = base64_decode(substr($challenge, 4));
            $message   = base64_encode($username . ' ' . $this->getResponse($password, $challenge));
            $agent->executeCommand(sprintf("%s\r\n", $message), [235]);

            return true;
        } catch (Exception $e) {
            $agent->executeCommand("RSET\r\n", [250]);

            return false;
        }
    }

    /**
     * Generate a CRAM-MD5 response from a server challenge
     *
     * @param  string $secret
     * @param  string $challenge
     * @return string
     */
    private function getResponse($secret, $challenge)
    {
        if (strlen($secret) > 64) {
            $secret = pack('H32', md5($secret));
        }

        if (strlen($secret) < 64) {
            $secret = str_pad($secret, 64, chr(0));
        }

        $k_ipad = substr($secret, 0, 64) ^ str_repeat(chr(0x36), 64);
        $k_opad = substr($secret, 0, 64) ^ str_repeat(chr(0x5C), 64);

        $inner  = pack('H32', md5($k_ipad . $challenge));
        $digest = md5($k_opad . $inner);

        return $digest;
    }
}
