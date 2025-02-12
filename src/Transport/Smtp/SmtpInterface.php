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
namespace Pop\Mail\Transport\Smtp;

/**
 * SMTP interface
 *
 * @category   Pop
 * @package    Pop\Mail
 * @author     Chris Corbyn, from the SwiftMailer library https://github.com/swiftmailer/swiftmailer
 * @version    4.0.4
 */
interface SmtpInterface
{

    /**
     * Test if this Transport mechanism has started
     *
     * @return bool
     */
    public function isStarted(): bool;

    /**
     * Start this Transport mechanism
     */
    public function start(): void;

    /**
     * Stop this Transport mechanism
     */
    public function stop(): void;

}
