<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp-framework
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2019 NOLA Interactive, LLC. (http://www.nolainteractive.com)
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
 * @copyright  Copyright (c) 2009-2019 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    3.1.0
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
     * Get the transport object
     *
     * @return Transport\TransportInterface
     */
    public function transport()
    {
        return $this->transport;
    }

    /**
     * Send message
     *
     * @param  Message $message
     * @return mixed
     */
    public function send(Message $message)
    {
        return $this->transport->send($message);
    }

    /**
     * Send messages from mail queue
     *
     * @param  Queue $queue
     * @throws Exception
     * @return int
     */
    public function sendFromQueue(Queue $queue)
    {
        $sent     = 0;
        $messages = $queue->prepare();

        foreach ($messages as $message) {
            $this->transport->send($message);
            $sent++;
        }

        return $sent;
    }

    /**
     * Send messages from email messages saved to disk in a directory
     *
     * @param  string $dir
     * @throws Exception
     * @return int
     */
    public function sendFromDir($dir)
    {
        if (!file_exists($dir)) {
            throw new Exception('Error: That directory does not exist');
        }

        $sent  = 0;
        $files = scandir($dir);

        foreach ($files as $file) {
            if (($file != '.') && ($file != '..')) {
                $message = Message::load($dir . DIRECTORY_SEPARATOR . $file);
                $this->transport->send($message);
                $sent++;
            }
        }

        return $sent;
    }

}
