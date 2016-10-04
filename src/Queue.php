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
 * Mail queue class
 *
 * @category   Pop
 * @package    Pop_Mail
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2016 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    3.0.0
 */
class Queue extends \SplQueue
{

    /**
     * Constructor
     *
     * Instantiate the mail queue object
     *
     * @param  mixed  $email
     * @param  string $name
     * @return Queue
     */
    public function __construct($email = null, $name = null)
    {
        if (null !== $email) {
            if (is_array($email)) {
                $this->addRecipients($email);
            } else {
                $this->addRecipient($email, $name);
            }
        }
    }

    /**
     * Add a recipient
     *
     * @param  string $email
     * @param  string $name
     * @return Queue
     */
    public function addRecipient($email, $name = null)
    {
        $recipient = [];
        if (null !== $name) {
            $rcpt['name'] = $name;
        }
        $recipient['email'] = $email;

        return $this->addRecipients([$recipient]);
    }

    /**
     * Add recipients
     *
     * @param  array $recipients
     * @throws Exception
     * @return Queue
     */
    public function addRecipients(array $recipients)
    {
        foreach ($recipients as $recipient) {
            if (is_array($recipient) && isset($recipient['email']) && $this->isValid($recipient['email'])) {
                $this[] = $recipient;
            } else if (is_string($recipient) && $this->isValid($recipient)) {
                $this[] = ['email' => $recipient];
            } else {
                throw new Exception('Error: That recipient did not contain a valid email address.');
            }
        }

        return $this;
    }


    /**
     * Validate the email address
     *
     * @param  string $email
     * @return boolean
     */
    public function isValid($email)
    {
        return (preg_match('/[a-zA-Z0-9\.\-\_+%]+@[a-zA-Z0-9\-\_\.]+\.[a-zA-Z]{2,4}/', $email) ||
            preg_match('/[a-zA-Z0-9\.\-\_+%]+@localhost/', $email));
    }


    /**
     * Build the address string
     *
     * @return string
     */
    public function __toString()
    {
        $addresses = [];
        foreach ($this as $rcpt) {
            $addresses[] = (isset($rcpt['name'])) ? $rcpt['name'] . ' <' . $rcpt['email'] . '>' : $rcpt['email'];
        }

        return implode(', ', $addresses);
    }

}
