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
 * Http client interface
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
     * Get client
     *
     * @return Http\Client
     */
    public function getClient(): Http\Client;

    /**
     * Has client
     *
     * @return bool
     */
    public function hasClient(): bool;

    /**
     * Set options
     *
     * @param  array $options
     * @return HttpClientInterface
     */
    public function setOptions(array $options): HttpClientInterface;

}
