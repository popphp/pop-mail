<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp-framework
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2024 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Pop\Mail\Transport\Smtp;

/**
 * SMTP handler interface
 *
 * @category   Pop
 * @package    Pop\Mail
 * @author     Chris Corbyn, from the SwiftMailer library https://github.com/swiftmailer/swiftmailer
 * @version    4.0.0
 */
interface HandlerInterface
{

    /**
     * Get the name of the ESMTP extension this handles
     *
     * @return string
     */
    public function getHandledKeyword(): string;

    /**
     * Set the parameters which the EHLO greeting indicated
     *
     * @param array $parameters
     */
    public function setKeywordParams(array $parameters): void;

    /**
     * Runs immediately after a EHLO has been issued
     *
     * @param AgentInterface $agent to read/write
     */
    public function afterEhlo(AgentInterface $agent): void;

    /**
     * Get params which are appended to MAIL FROM:<>
     *
     * @return array
     */
    public function getMailParams(): array;

    /**
     * Get params which are appended to RCPT TO:<>
     *
     * @return array
     */
    public function getRcptParams(): array;

    /**
     * Runs when a command is due to be sent
     *
     * @param AgentInterface $agent            to read/write
     * @param string         $command          to send
     * @param array          $codes            expected in response
     * @param bool           $stop             to be set true  by-reference if the command is now sent
     */
    public function onCommand(AgentInterface $agent, string $command, array $codes = [], bool &$stop = false): void;

    /**
     * Returns +1, -1 or 0 according to the rules for usort().
     *
     * This method is called to ensure extensions can be execute in an appropriate order.
     *
     * @param  string $esmtpKeyword to compare with
     * @return int
     */
    public function getPriorityOver(string $esmtpKeyword): int;

    /**
     * Tells this handler to clear any buffers and reset its state
     */
    public function resetState(): void;

}