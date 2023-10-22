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
 * Abstract HTTP client class
 *
 * @category   Pop
 * @package    Pop\Mail
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2024 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    4.0.0
 */
abstract class AbstractHttpClient implements HttpClientInterface
{

    /**
     * Client
     * @var ?Http\Client
     */
    protected ?Http\Client $client = null;

    /**
     * Create Office 365 object
     *
     * @param ?array $options
     */
    public function __construct(?array $options = null)
    {
        if ($options !== null) {
            $this->setOptions($options);
        }
    }

    /**
     * Get client
     *
     * @return Http\Client
     */
    public function getClient(): Http\Client
    {
        return $this->client;
    }

    /**
     * Has client
     *
     * @return bool
     */
    public function hasClient(): bool
    {
        return ($this->client !== null);
    }

    /**
     * Set options
     *
     * @param  array $options
     * @return AbstractHttpClient
     */
    abstract public function setOptions(array $options): AbstractHttpClient;

}
