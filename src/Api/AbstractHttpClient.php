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
namespace Pop\Mail\Api;

use Pop\Http;

/**
 * Abstract HTTP class
 *
 * @category   Pop
 * @package    Pop\Mail
 * @author     Nick Sagona, III <dev@noladev.com>
 * @copyright  Copyright (c) 2009-2025 NOLA Interactive, LLC.
 * @license    https://www.popphp.org/license     New BSD License
 * @version    4.0.4
 */
abstract class AbstractHttpClient extends AbstractHttp implements HttpClientInterface
{

    /**
     * Client ID
     * @var ?string
     */
    protected ?string $clientId = null;

    /**
     * Client Secret
     * @var ?string
     */
    protected ?string $clientSecret = null;

    /**
     * Scope
     * @var ?string
     */
    protected ?string $scope = null;

    /**
     * Account ID
     * @var ?string
     */
    protected ?string $accountId = null;

    /**
     * Account username
     * @var ?string
     */
    protected ?string $username = null;

    /**
     * Token
     * @var ?string
     */
    protected ?string $token = null;

    /**
     * Token expires
     * @var ?string
     */
    protected ?string $tokenExpires = null;

    /**
     * Token request URI
     * @var ?string
     */
    protected ?string $tokenRequestUri = null;

    /**
     * Base URL
     * @var ?string
     */
    protected ?string $baseUri = null;

    /**
     * Set client ID
     *
     * @param  string $clientId
     * @return AbstractHttpClient
     */
    public function setClientId(string $clientId): AbstractHttpClient
    {
        $this->clientId = $clientId;
        return $this;
    }

    /**
     * Get client ID
     *
     * @return ?string
     */
    public function getClientId(): ?string
    {
        return $this->clientId;
    }

    /**
     * Has client ID
     *
     * @return bool
     */
    public function hasClientId(): bool
    {
        return ($this->clientId !== null);
    }

    /**
     * Set client secret
     *
     * @param  string $clientSecret
     * @return AbstractHttpClient
     */
    public function setClientSecret(string $clientSecret): AbstractHttpClient
    {
        $this->clientSecret = $clientSecret;
        return $this;
    }

    /**
     * Get client ID
     *
     * @return ?string
     */
    public function getClientSecret(): ?string
    {
        return $this->clientSecret;
    }

    /**
     * Has client ID
     *
     * @return bool
     */
    public function hasClientSecret(): bool
    {
        return ($this->clientSecret !== null);
    }


    /**
     * Set scope
     * @param  string $scope
     * @return AbstractHttpClient
     */
    public function setScope(string $scope): AbstractHttpClient
    {
        $this->scope = $scope;
        return $this;
    }

    /**
     * Get scope ID
     *
     * @return ?string
     */
    public function getScope(): ?string
    {
        return $this->scope;
    }

    /**
     * Has scope ID
     *
     * @return bool
     */
    public function hasScope(): bool
    {
        return ($this->scope !== null);
    }

    /**
     * Set account ID
     *
     * @param  string $accountId
     * @return AbstractHttpClient
     */
    public function setAccountId(string $accountId): AbstractHttpClient
    {
        $this->accountId = $accountId;
        return $this;
    }

    /**
     * Get account ID
     *
     * @return ?string
     */
    public function getAccountId(): ?string
    {
        return $this->accountId;
    }

    /**
     * Has account ID
     *
     * @return bool
     */
    public function hasAccountId(): bool
    {
        return ($this->accountId !== null);
    }

    /**
     * Set account username
     *
     * @param  string $username
     * @return AbstractHttpClient
     */
    public function setUsername(string $username): AbstractHttpClient
    {
        $this->username = $username;
        return $this;
    }

    /**
     * Get account username
     *
     * @return ?string
     */
    public function getUsername(): ?string
    {
        return $this->username;
    }

    /**
     * Has account username
     *
     * @return bool
     */
    public function hasUsername(): bool
    {
        return ($this->username !== null);
    }

    /**
     * Set token
     *
     * @param  string $token
     * @return AbstractHttpClient
     */
    public function setToken(string $token): AbstractHttpClient
    {
        $this->token = $token;
        return $this;
    }

    /**
     * Get token
     *
     * @return ?string
     */
    public function getToken(): ?string
    {
        return $this->token;
    }

    /**
     * Has token
     *
     * @return bool
     */
    public function hasToken(): bool
    {
        return ($this->token !== null);
    }

    /**
     * Set token expires
     *
     * @param  string $tokenExpires
     * @return AbstractHttpClient
     */
    public function setTokenExpires(string $tokenExpires): AbstractHttpClient
    {
        $this->tokenExpires = $tokenExpires;
        return $this;
    }

    /**
     * Get token expires
     *
     * @return ?string
     */
    public function getTokenExpires(): ?string
    {
        return $this->tokenExpires;
    }

    /**
     * Has token expires
     *
     * @return bool
     */
    public function hasTokenExpires(): bool
    {
        return ($this->tokenExpires !== null);
    }

    /**
     * Check if token is expired
     *
     * @return boolean
     */
    public function isTokenExpired(): bool
    {
        return (((int)$this->tokenExpires > 0) && ($this->tokenExpires <= time()));
    }

    /**
     * Verify token and refresh is expired
     *
     * @return boolean
     */
    public function verifyToken(): bool
    {
        if (!isset($this->token) || !isset($this->tokenExpires) || ($this->isTokenExpired())) {
            $this->requestToken();
        }

        return (isset($this->token) && isset($this->tokenExpires) && (!$this->isTokenExpired()));
    }

    /**
     * Request token
     *
     * @throws Exception|Http\Exception|Http\Client\Exception|Http\Client\Handler\Exception
     * @return AbstractHttpClient
     */
    abstract public function requestToken(): AbstractHttpClient;

}
