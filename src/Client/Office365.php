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
namespace Pop\Mail\Client;

use Pop\Http;

/**
 * Office 365 mail client class
 *
 * @category   Pop
 * @package    Pop\Mail
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2024 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    4.0.0
 */
class Office365 extends AbstractHttpClient
{

    /**
     * Client
     * @var ?Http\Client
     */
    protected ?Http\Client $client = null;

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
     * Scope
     * @var ?string
     */
    protected ?string $scope = null;

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
    protected ?string $tokenRequestUri = 'https://login.microsoftonline.com/[{tenant_id}]/oauth2/v2.0/token';

    /**
     * Base URL
     * @var ?string
     */
    protected ?string $baseUri = 'https://graph.microsoft.com/v1.0/users/';

    /**
     * Set options
     *
     * @param  array $options
     * @return Office365
     */
    public function setOptions(array $options): Office365
    {
        $this->clientId     = $options['client_id'] ?? null;
        $this->clientSecret = $options['client_secret'] ?? null;
        $this->tenantId     = $options['tenant_id'] ?? null;
        $this->accountId    = $options['account_id'] ?? null;
        $this->username     = $options['username'] ?? null;
        $this->scope        = $options['scope'] ?? null;

        if ($this->accountId !== null) {
            $this->client = new Http\Client([
                'base_uri' => $this->baseUri . $this->accountId
            ]);
        }

        return $this;
    }

    /**
     * Set client ID
     * 
     * @param  string $clientId
     * @return Office365
     */
    public function setClientId(string $clientId): Office365
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
     * @return Office365
     */
    public function setClientSecret(string $clientSecret): Office365
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
     * Set tenant ID
     * 
     * @param  string $tenantId
     * @return Office365
     */
    public function setTenantId(string $tenantId): Office365
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
     * @return Office365
     */
    public function setAccountId(string $accountId): Office365
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
     * @return Office365
     */
    public function setUsername(string $username): Office365
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
     * Set scope
     * @param  string $scope
     * @return Office365
     */
    public function setScope(string $scope): Office365
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
     * Set token
     *
     * @param  string $token
     * @return Office365
     */
    public function setToken(string $token): Office365
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
     * @return Office365
     */
    public function setTokenExpires(string $tokenExpires): Office365
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
     * @return Office365
     */
    public function requestToken(): Office365
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

    /**
     * Get messages
     *
     * @param  string $folder
     * @param  bool   $unread
     * @param  int    $limit
     * @throws Exception|Http\Exception|Http\Client\Exception|Http\Client\Handler\Exception
     * @return mixed
     */
    public function getMessages(string $folder = 'Inbox', bool $unread = false, int $limit = 10): mixed
    {
        if ($this->client === null) {
            throw new Exception('Error: The client object has not been instantiated yet.');
        }

        $this->verifyToken();

        $data = [];

        if (!empty($limit)) {
            $data['$top'] = $limit;
        }
        if ($unread) {
            $data['filter'] = 'isRead eq false';
        }

        $this->client->setAuth(Http\Auth::createBearer($this->token));
        $this->client->addOption('method', 'GET');
        $this->client->addOption('type', Http\Client\Request::URLFORM);
        $this->client->addOption('auto', true);

        $uri = "/mailfolders('" . $folder . "')/messages";
        if (!empty($data)){
            $uri .= '?' . rawurldecode(http_build_query($data, "\n"));
        }

        return $this->client->send($uri);
    }

    /**
     * Get messages
     *
     * @param  string $messageId
     * @param  bool   $raw
     * @throws Exception|Http\Exception|Http\Client\Exception|Http\Client\Handler\Exception
     * @return mixed
     */
    public function getMessage(string $messageId, bool $raw = false): mixed
    {
        if ($this->client === null) {
            throw new Exception('Error: The client object has not been instantiated yet.');
        }

        $this->verifyToken();

        $this->client->setAuth(Http\Auth::createBearer($this->token));
        $this->client->addOption('method', 'GET');
        $this->client->addOption('type', Http\Client\Request::URLFORM);
        $this->client->addOption('auto', true);

        $uri = "/messages/" . $messageId;
        if ($raw) {
            $uri .= '/$value';
        }

        return $this->client->send($uri);
    }

    /**
     * Get message attachments
     *
     * @param  string $messageId
     * @param  string $folder
     * @throws Exception|Http\Exception|Http\Client\Exception|Http\Client\Handler\Exception
     * @return mixed
     */
    public function getAttachments(string $messageId, string $folder = 'Inbox'): mixed
    {
        if ($this->client === null) {
            throw new Exception('Error: The client object has not been instantiated yet.');
        }

        $this->verifyToken();

        $this->client->setAuth(Http\Auth::createBearer($this->token));
        $this->client->addOption('method', 'GET');
        $this->client->addOption('type', Http\Client\Request::URLFORM);
        $this->client->addOption('auto', true);

        return $this->client->send("/mailfolders('" . $folder . "')/messages/" . $messageId . '/attachments');
    }

    /**
     * Get message attachment
     *
     * @param  string $messageId
     * @param  string $attachmentId
     * @param  string $folder
     * @throws Exception|Http\Exception|Http\Client\Exception|Http\Client\Handler\Exception
     * @return mixed
     */
    public function getAttachment(string $messageId, string $attachmentId, string $folder = 'Inbox'): mixed
    {
        if ($this->client === null) {
            throw new Exception('Error: The client object has not been instantiated yet.');
        }

        $this->verifyToken();

        $this->client->setAuth(Http\Auth::createBearer($this->token));
        $this->client->addOption('method', 'GET');
        $this->client->addOption('type', Http\Client\Request::URLFORM);
        $this->client->addOption('auto', true);

        return $this->client->send("/mailfolders('" . $folder . "')/messages/" . $messageId . '/attachments/' . $attachmentId);
    }

    /**
     * Mark message as read
     *
     * @param  string $messageId
     * @param  bool   $isRead
     * @throws Exception|Http\Exception|Http\Client\Exception|Http\Client\Handler\Exception
     * @return Office365
     */
    public function markAsRead(string $messageId, bool $isRead = true): Office365
    {
        if ($this->client === null) {
            throw new Exception('Error: The client object has not been instantiated yet.');
        }

        $this->verifyToken();

        $this->client->setAuth(Http\Auth::createBearer($this->token));
        $this->client->addOption('type', Http\Client\Request::JSON);
        $this->client->addOption('auto', true);
        $this->client->addOption('method', 'PATCH');
        $this->client->setData(['isRead' => $isRead]);

        $this->client->send('/messages/' . $messageId);

        return $this;
    }

    /**
     * Mark message as unread
     *
     * @param  string $messageId
     * @throws Exception|Http\Exception|Http\Client\Exception|Http\Client\Handler\Exception
     * @return Office365
     */
    public function markAsUnread(string $messageId): Office365
    {
        return $this->markAsRead($messageId, false);
    }

}