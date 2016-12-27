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
namespace Pop\Mail\Transport;

use Pop\Mail\Message;

/**
 * SMTP transport class
 *
 * @category   Pop
 * @package    Pop\Mail
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2016 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    3.0.0
 */
class Smtp extends Smtp\EsmtpTransport
{

    /**
     * Create a new SmtpTransport, optionally with $host, $port and $security.
     *
     * @param string $host
     * @param int    $port
     * @param string $security
     */
    public function __construct($host = 'localhost', $port = 25, $security = null)
    {
        $streamBuffer = new Smtp\StreamBuffer(new Smtp\StreamFilters\StringReplacementFilterFactory);
        $authHandler  = [new Smtp\AuthHandler([
            new Smtp\Auth\CramMd5Authenticator(),
            new Smtp\Auth\LoginAuthenticator(),
            new Smtp\Auth\NTLMAuthenticator(),
            new Smtp\Auth\PlainAuthenticator(),
            new Smtp\Auth\XOAuth2Authenticator()
        ])];

        parent::__construct($streamBuffer, $authHandler);

        $this->setHost($host);
        $this->setPort($port);
        $this->setEncryption($security);
    }

    /**
     * Send the message
     *
     * @param \Pop\Mail\Message $message
     * @param string[] $failedRecipients An array of failures by-reference
     * @return mixed
     */
    public function send(\Pop\Mail\Message $message, &$failedRecipients = null)
    {
        return;
    }

}
