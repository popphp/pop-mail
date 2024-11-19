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
namespace Pop\Mail\Client;

/**
 * Mail client POP class
 *
 * @category   Pop
 * @package    Pop\Mail
 * @author     Nick Sagona, III <dev@noladev.com>
 * @copyright  Copyright (c) 2009-2025 NOLA Interactive, LLC.
 * @license    https://www.popphp.org/license     New BSD License
 * @version    4.0.0
 */
class Pop extends Imap
{

    /**
     * Constructor
     *
     * NOTE: Many enterprise mail applications have discontinued support of POP and it is no longer allowed.
     *
     * Instantiate the POP mail client object
     *
     * @param string     $host
     * @param int|string $port
     * @param string     $service
     */
    public function __construct(string $host, int|string $port, $service = 'pop3')
    {
        parent::__construct($host, $port, $service);
    }

}
