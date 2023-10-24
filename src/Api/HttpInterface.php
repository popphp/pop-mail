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

use Aws\Ses\SesClient;
use Pop\Http;

/**
 * Http base interface
 *
 * @category   Pop
 * @package    Pop\Mail
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2024 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    4.0.0
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
