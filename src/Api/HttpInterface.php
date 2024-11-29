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

use Aws\Ses\SesClient;
use Pop\Http;

/**
 * Http base interface
 *
 * @category   Pop
 * @package    Pop\Mail
 * @author     Nick Sagona, III <dev@noladev.com>
 * @copyright  Copyright (c) 2009-2025 NOLA Interactive, LLC.
 * @license    https://www.popphp.org/license     New BSD License
 * @version    4.0.3
 */
interface HttpInterface
{

    /**
     * Create client
     *
     * @param  array|string $options
     * @return HttpInterface
     */
    public function createClient(array|string $options): HttpInterface;

    /**
     * Set client
     *
     * @param  mixed $client
     * @return HttpInterface
     */
    public function setClient(mixed $client): HttpInterface;

    /**
     * Get client
     *
     * @return mixed
     */
    public function getClient(): mixed;

    /**
     * Has client
     *
     * @return bool
     */
    public function hasClient(): bool;

    /**
     * Parse options
     *
     * @param  array|string $options
     * @throws Exception
     * @return array
     */
    public function parseOptions(array|string $options): array;

}
