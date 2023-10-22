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
 * SMTP auth handler class
 *
 * @category   Pop
 * @package    Pop\Mail
 * @author     Chris Corbyn, from the SwiftMailer library https://github.com/swiftmailer/swiftmailer
 * @version    4.0.0
 */
class AuthHandler implements HandlerInterface
{

    /**
     * Authenticators available to process the request.
     * @var array
     */
    private array $authenticators = [];

    /**
     * The username for authentication
     * @var ?string
     */
    private ?string $username = null;

    /**
     * The password for authentication
     * @var ?string
     */
    private ?string $password = null;

    /**
     * The auth mode for authentication
     * @var ?string
     */
    private ?string $authMode = null;

    /**
     * The ESMTP AUTH parameters available
     * @var array
     */
    private array $esmtpParams = [];

    /**
     * Create a new AuthHandler with $authenticators for support
     *
     * @param array $authenticators
     */
    public function __construct(array $authenticators)
    {
        $this->setAuthenticators($authenticators);
    }

    /**
     * Set the Authenticators which can process a login request
     *
     * @param array $authenticators
     */
    public function setAuthenticators(array $authenticators): void
    {
        $this->authenticators = $authenticators;
    }

    /**
     * Get the Authenticators which can process a login request
     *
     * @return array
     */
    public function getAuthenticators(): array
    {
        return $this->authenticators;
    }

    /**
     * Set the username to authenticate with
     *
     * @param string $username
     */
    public function setUsername(string $username): void
    {
        $this->username = $username;
    }

    /**
     * Get the username to authenticate with
     *
     * @return ?string
     */
    public function getUsername(): ?string
    {
        return $this->username;
    }

    /**
     * Set the password to authenticate with
     *
     * @param string $password
     */
    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

    /**
     * Get the password to authenticate with
     *
     * @return ?string
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    /**
     * Set the auth mode to use to authenticate
     *
     * @param string $mode
     */
    public function setAuthMode(string $mode): void
    {
        $this->authMode = $mode;
    }

    /**
     * Get the auth mode to use to authenticate
     *
     * @return ?string
     */
    public function getAuthMode(): ?string
    {
        return $this->authMode;
    }

    /**
     * Get the name of the ESMTP extension this handles
     *
     * @return string
     */
    public function getHandledKeyword(): string
    {
        return 'AUTH';
    }

    /**
     * Set the parameters which the EHLO greeting indicated
     *
     * @param array $parameters
     */
    public function setKeywordParams(array $parameters): void
    {
        $this->esmtpParams = $parameters;
    }

    /**
     * Runs immediately after a EHLO has been issued
     *
     * @param  AgentInterface $agent to read/write
     * @throws Exception
     * @return void
     */
    public function afterEhlo(AgentInterface $agent): void
    {
        if ($this->username) {
            $count = 0;
            foreach ($this->getAuthenticatorsForAgent() as $authenticator) {
                if (in_array(strtolower((string)$authenticator->getAuthKeyword()),
                    array_map('strtolower', $this->esmtpParams))) {
                    ++$count;
                    if ($authenticator->authenticate($agent, $this->username, $this->password)) {
                        return;
                    }
                }
            }
            throw new Exception(
                'Failed to authenticate on SMTP server with username "' .
                $this->username . '" using ' . $count . ' possible authenticators'
            );
        }
    }

    /**
     * Not used
     */
    public function getMailParams(): array
    {
        return [];
    }

    /**
     * Not used
     */
    public function getRcptParams(): array
    {
        return [];
    }

    /**
     * Not used
     *
     * @param AgentInterface $agent
     * @param string         $command
     * @param array          $codes
     * @param bool           $stop
     */
    public function onCommand(AgentInterface $agent, string $command, array $codes = [], bool &$stop = false): void
    {
    }

    /**
     * Returns +1, -1 or 0 according to the rules for usort().
     *
     * This method is called to ensure extensions can be execute in an appropriate order.
     *
     * @param  string $esmtpKeyword to compare with
     * @return int
     */
    public function getPriorityOver(string $esmtpKeyword): int
    {
        return 0;
    }

    /**
     * Not used
     */
    public function resetState(): void
    {
    }

    /**
     * Returns the authenticator list for the given agent
     *
     * @throws Exception
     * @return array
     */
    protected function getAuthenticatorsForAgent(): array
    {
        if (!$mode = strtolower((string)$this->authMode)) {
            return $this->authenticators;
        }

        foreach ($this->authenticators as $authenticator) {
            if (strtolower((string)$authenticator->getAuthKeyword()) == $mode) {
                return [$authenticator];
            }
        }

        throw new Exception('Auth mode ' . $mode . ' is invalid');
    }

}