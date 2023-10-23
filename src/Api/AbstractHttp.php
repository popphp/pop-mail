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
 * Abstract HTTP base class
 *
 * @category   Pop
 * @package    Pop\Mail
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2024 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    4.0.0
 */
abstract class AbstractHttp implements HttpInterface
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
            $this->createClient($options);
        }
    }

    /**
     * Set client
     *
     * @param  Http\Client $client
     * @return AbstractHttp
     */
    public function setClient(Http\Client $client): AbstractHttp
    {
        $this->client = $client;
        return $this;
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
     * Create client
     *
     * @param  array $options
     * @return AbstractHttp
     */
    abstract public function createClient(array $options): AbstractHttp;

}
