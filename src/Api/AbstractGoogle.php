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
use Google;

/**
 * Abstract Google Mail API class
 *
 * @category   Pop
 * @package    Pop\Mail
 * @author     Nick Sagona, III <dev@noladev.com>
 * @copyright  Copyright (c) 2009-2025 NOLA Interactive, LLC.
 * @license    https://www.popphp.org/license     New BSD License
 * @version    4.0.4
 */
abstract class AbstractGoogle extends AbstractHttpClient
{

    /**
     * Create client
     *
     * @param  array|string $options
     * @param  ?string      $username
     * @throws Exception
     * @return AbstractGoogle
     */
    public function createClient(array|string $options, ?string $username = null): AbstractGoogle
    {
        if ($username !== null) {
            $this->setUsername($username);
        }

        if ($this->username === null) {
            throw new Exception('Error: The username is required to create the client object.');
        }

        $this->client = new Google\Client();
        $this->client->setAuthConfig($options);
        $this->client->setSubject($this->username);
        $this->client->setAccessType('offline');
        $this->client->setIncludeGrantedScopes(true);
        $this->client->addScope(Google\Service\Gmail::MAIL_GOOGLE_COM);

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
        if (empty($this->client)) {
            throw new Exception('Error: The client object has not yet been instantiated.');
        } else {
            if (isset($this->token) && isset($this->tokenExpires) && !$this->isTokenExpired()) {
                return $this;
            }
        }

        $response = $this->client->fetchAccessTokenWithAssertion();

        if (is_array($response) && isset($response['access_token']) && isset($response['expires_in'])) {
            $this->setToken($response['access_token'])
                ->setTokenExpires(time() + $response['expires_in']);
        }

        return $this;
    }

}
