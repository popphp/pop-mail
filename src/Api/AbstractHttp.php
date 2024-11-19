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
use Aws\Ses\SesClient;

/**
 * Abstract HTTP base class
 *
 * @category   Pop
 * @package    Pop\Mail
 * @author     Nick Sagona, III <dev@noladev.com>
 * @copyright  Copyright (c) 2009-2025 NOLA Interactive, LLC.
 * @license    https://www.popphp.org/license     New BSD License
 * @version    4.0.0
 */
abstract class AbstractHttp implements HttpInterface
{

    /**
     * Client
     * @var mixed
     */
    protected mixed $client = null;

    /**
     * Create Office 365 object
     *
     * @param array|string|null $options
     */
    public function __construct(array|string|null $options = null)
    {
        if ($options !== null) {
            $this->createClient($options);
        }
    }

    /**
     * Set client
     *
     * @param  mixed $client
     * @return AbstractHttp
     */
    public function setClient(mixed $client): AbstractHttp
    {
        $this->client = $client;
        return $this;
    }

    /**
     * Get client
     *
     * @return mixed
     */
    public function getClient(): mixed
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
     * Parse options
     *
     * @param  array|string $options
     * @throws Exception
     * @return array
     */
    public function parseOptions(array|string $options): array
    {
        if (is_string($options)) {
            $jsonValue = @json_decode($options, true);
            if ((json_last_error() === JSON_ERROR_NONE) && is_array($jsonValue)) {
                $options = $jsonValue;
            } else if (file_exists($options)) {
                $options = @json_decode(file_get_contents($options), true);
            }

            if (!is_array($options)) {
                throw new Exception('Error: Unable to parse the options.');
            }
        }

        return $options;
    }

    /**
     * Create client
     *
     * @param  array|string $options
     * @return AbstractHttp
     */
    abstract public function createClient(array|string $options): AbstractHttp;

}
