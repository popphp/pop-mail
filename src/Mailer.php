<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp-framework
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2016 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Pop\Mail;

/**
 * Mailer class
 *
 * @category   Pop
 * @package    Pop\Mail
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2016 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    3.0.0
 */
class Mailer
{

    /**
     * Transport object
     * @var Transport\TransportInterface
     */
    protected $transport = null;

    /**
     * Constructor
     *
     * Instantiate the message object
     *
     * @param  Transport\TransportInterface $transport
     */
    public function __construct(Transport\TransportInterface $transport)
    {
        $this->transport = $transport;
    }

    /**
     * Send message
     *
     * @param  Message $message
     * @return void
     */
    public function send(Message $message)
    {

    }

}
