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

use Pop\Mail\Transport\Smtp\Stream\BufferInterface;

/**
 * SMTP agent interface
 *
 * @category   Pop
 * @package    Pop\Mail
 * @author     Chris Corbyn, from the SwiftMailer library https://github.com/swiftmailer/swiftmailer
 * @version    4.0.0
 */
interface AgentInterface
{

    /**
     * Get the IoBuffer where read/writes are occurring.
     *
     * @return BufferInterface
     */
    public function getBuffer(): BufferInterface;

    /**
     * Run a command against the buffer, expecting the given response codes.
     *
     * If no response codes are given, the response will not be validated.
     * If codes are given, an exception will be thrown on an invalid response.
     *
     * @param  string $command
     * @param  array  $codes
     * @return mixed
     */
    public function executeCommand(string $command, array $codes = []): mixed;

}
