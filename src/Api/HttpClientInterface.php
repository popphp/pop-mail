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
namespace Pop\Mail\Api;

use Pop\Http;

/**
 * Http interface
 *
 * @category   Pop
 * @package    Pop\Mail
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2024 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    4.0.0
 */
interface HttpClientInterface
{

    /**
     * Set client ID
     *
     * @param  string $clientId
     * @return HttpClientInterface
     */
    public function setClientId(string $clientId): HttpClientInterface;

    /**
     * Get client ID
     *
     * @return ?string
     */
    public function getClientId(): ?string;

    /**
     * Has client ID
     *
     * @return bool
     */
    public function hasClientId(): bool;

    /**
     * Set client secret
     *
     * @param  string $clientSecret
     * @return HttpClientInterface
     */
    public function setClientSecret(string $clientSecret): HttpClientInterface;

    /**
     * Get client ID
     *
     * @return ?string
     */
    public function getClientSecret(): ?string;

    /**
     * Has client ID
     *
     * @return bool
     */
    public function hasClientSecret(): bool;

    /**
     * Set scope
     * @param  string $scope
     * @return HttpClientInterface
     */
    public function setScope(string $scope): HttpClientInterface;

    /**
     * Get scope ID
     *
     * @return ?string
     */
    public function getScope(): ?string;

    /**
     * Has scope ID
     *
     * @return bool
     */
    public function hasScope(): bool;

    /**
     * Set account ID
     *
     * @param  string $accountId
     * @return HttpClientInterface
     */
    public function setAccountId(string $accountId): HttpClientInterface;

    /**
     * Get account ID
     *
     * @return ?string
     */
    public function getAccountId(): ?string;

    /**
     * Has account ID
     *
     * @return bool
     */
    public function hasAccountId(): bool;

    /**
     * Set account username
     *
     * @param  string $username
     * @return HttpClientInterface
     */
    public function setUsername(string $username): HttpClientInterface;

    /**
     * Get account username
     *
     * @return ?string
     */
    public function getUsername(): ?string;

    /**
     * Has account username
     *
     * @return bool
     */
    public function hasUsername(): bool;

    /**
     * Set token
     *
     * @param  string $token
     * @return HttpClientInterface
     */
    public function setToken(string $token): HttpClientInterface;

    /**
     * Get token
     *
     * @return ?string
     */
    public function getToken(): ?string;

    /**
     * Has token
     *
     * @return bool
     */
    public function hasToken(): bool;

    /**
     * Set token expires
     *
     * @param  string $tokenExpires
     * @return HttpClientInterface
     */
    public function setTokenExpires(string $tokenExpires): HttpClientInterface;

    /**
     * Get token expires
     *
     * @return ?string
     */
    public function getTokenExpires(): ?string;

    /**
     * Has token expires
     *
     * @return bool
     */
    public function hasTokenExpires(): bool;

    /**
     * Check if token is expired
     *
     * @return boolean
     */
    public function isTokenExpired(): bool;

    /**
     * Verify token and refresh is expired
     *
     * @return boolean
     */
    public function verifyToken(): bool;

    /**
     * Request token
     *
     * @throws Exception|Http\Exception|Http\Client\Exception|Http\Client\Handler\Exception
     * @return HttpClientInterface
     */
    public function requestToken(): HttpClientInterface;

}
