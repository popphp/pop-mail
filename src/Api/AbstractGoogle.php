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
 * Abstract Google Mail API class
 *
 * @category   Pop
 * @package    Pop\Mail
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2024 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    4.0.0
 */
abstract class AbstractGoogle extends AbstractHttpClient
{

    /**
     * Token request URI
     * @var ?string
     */
    protected ?string $tokenRequestUri = 'https://accounts.google.com/o/oauth2/v2/auth';

    /**
     * Base URL
     * @var ?string
     */
    protected ?string $baseUri = '';

    /**
     * Create client
     *
     * @param  array $options
     * @return AbstractGoogle
     */
    public function createClient(array $options): AbstractGoogle
    {
        $this->clientId     = $options['client_id'] ?? null;
        $this->clientSecret = $options['client_secret'] ?? null;
        $this->scope        = $options['scope'] ?? null;

        /**
         * TO-DO: Create client
         */

        return $this;
    }

    /**
     * Request token
     *
     * @throws Exception|Http\Exception|Http\Client\Exception|Http\Client\Handler\Exception
     * @return AbstractGoogle
     */
    public function requestToken(): AbstractGoogle
    {
        if (empty($this->clientId) || empty($this->scope)) {
            throw new Exception('Error: The required credentials have not been set.');
        } else {
            if (isset($this->token) && isset($this->tokenExpires) && !$this->isTokenExpired()) {
                return $this;
            }
        }

        /**
         * TO-DO
         */

        $client = new Http\Client($this->tokenRequestUri, [
            'method' => 'GET',
            'auto'   => true,
            'query'  => [
                'client_id'     => $this->clientId,
                'scope'         => $this->scope,
                'response_type' => 'code',
                'redirect_uri'  => 'http://localhost:8000/',
                'access_type'   => 'offline'
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