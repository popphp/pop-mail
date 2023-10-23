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
 * Abstract Office 365 Mail API class
 *
 * @category   Pop
 * @package    Pop\Mail
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2024 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    4.0.0
 */
abstract class AbstractOffice365 extends AbstractHttpClient
{

    /**
     * Tenant ID
     * @var ?string
     */
    protected ?string $tenantId = null;

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
     * Token request URI
     * @var ?string
     */
    protected ?string $tokenRequestUri = 'https://login.microsoftonline.com/[{tenant_id}]/oauth2/v2.0/token';

    /**
     * Base URL
     * @var ?string
     */
    protected ?string $baseUri = 'https://graph.microsoft.com/v1.0/users';

    /**
     * Create client
     *
     * @param  array $options
     * @throws Exception
     * @return AbstractOffice365
     */
    public function createClient(array $options): AbstractOffice365
    {
        $this->clientId     = $options['client_id'] ?? null;
        $this->clientSecret = $options['client_secret'] ?? null;
        $this->scope        = $options['scope'] ?? null;
        $this->tenantId     = $options['tenant_id'] ?? null;
        $this->accountId    = $options['account_id'] ?? null;
        $this->username     = $options['username'] ?? null;

        if ($this->accountId === null) {
            throw new Exception('Error: The account ID is required to create the client object.');
        }

        $this->client = new Http\Client([
            'base_uri' => $this->baseUri
        ]);

        return $this;
    }

    /**
     * Set tenant ID
     *
     * @param  string $tenantId
     * @return AbstractOffice365
     */
    public function setTenantId(string $tenantId): AbstractOffice365
    {
        $this->tenantId = $tenantId;
        return $this;
    }

    /**
     * Get tenant ID
     *
     * @return ?string
     */
    public function getTenantId(): ?string
    {
        return $this->tenantId;
    }

    /**
     * Has tenant ID
     *
     * @return bool
     */
    public function hasTenantId(): bool
    {
        return ($this->tenantId !== null);
    }

    /**
     * Set account ID
     *
     * @param  string $accountId
     * @return AbstractOffice365
     */
    public function setAccountId(string $accountId): AbstractOffice365
    {
        $this->accountId = $accountId;

        if ($this->accountId !== null) {
            $this->client = new Http\Client([
                'base_uri' => $this->baseUri . $this->accountId
            ]);
        }

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
     * @return AbstractOffice365
     */
    public function setUsername(string $username): AbstractOffice365
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
     * Request token
     *
     * @throws Exception|Http\Exception|Http\Client\Exception|Http\Client\Handler\Exception
     * @return AbstractOffice365
     */
    public function requestToken(): AbstractOffice365
    {
        if (empty($this->tenantId) || empty($this->clientId) || empty($this->scope) || empty($this->clientSecret)) {
            throw new Exception('Error: The required credentials have not been set.');
        } else {
            if (isset($this->token) && isset($this->tokenExpires) && !$this->isTokenExpired()) {
                return $this;
            }
        }

        $tokenRequestUri = str_replace('[{tenant_id}]', $this->tenantId, $this->tokenRequestUri);

        $client = new Http\Client($tokenRequestUri, [
            'method' => 'POST',
            'auto'   => true,
            'data'   => [
                'grant_type'    => 'client_credentials',
                'client_id'     => $this->clientId,
                'scope'         => $this->scope,
                'client_secret' => $this->clientSecret,
            ]
        ]);

        $response = $client->send();

        if (is_array($response) && isset($response['access_token']) && isset($response['expires_in'])) {
            $this->setToken($response['access_token'])
                ->setTokenExpires(time() + $response['expires_in']);
        }

        return $this;
    }

}