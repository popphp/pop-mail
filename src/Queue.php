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
namespace Pop\Mail;

/**
 * Mail queue class
 *
 * @category   Pop
 * @package    Pop\Mail
 * @author     Nick Sagona, III <dev@noladev.com>
 * @copyright  Copyright (c) 2009-2025 NOLA Interactive, LLC.
 * @license    https://www.popphp.org/license     New BSD License
 * @version    4.0.3
 */
class Queue
{

    /**
     * Recipients
     * @var array
     */
    protected array $recipients = [];

    /**
     * Messages
     * @var array
     */
    protected array $messages = [];

    /**
     * Prepared messages
     * @var array
     */
    protected array $prepared = [];

    /**
     * Constructor
     *
     * Instantiate the mail queue object
     */
    public function __construct()
    {
        $args = func_get_args();

        foreach ($args as $arg) {
            if ($arg instanceof Message) {
                $this->addMessage($arg);
            } else if (is_array($arg)) {
                $keys = array_keys($arg);
                if (is_numeric($keys[0])) {
                    $this->addRecipients($arg);
                } else {
                    $this->addRecipient($arg);
                }
            }
        }
    }

    /**
     * Set (and clear) recipients in the queue
     *
     * @param  array $recipients
     * @return Queue
     */
    public function setRecipients(array $recipients): Queue
    {
        $this->recipients = [];
        foreach ($recipients as $recipient) {
            $this->addRecipient($recipient);
        }
        return $this;
    }

    /**
     * Add recipients to the queue
     *
     * @param  array $recipients
     * @return Queue
     */
    public function addRecipients(array $recipients): Queue
    {
        foreach ($recipients as $recipient) {
            $this->addRecipient($recipient);
        }
        return $this;
    }

    /**
     * Add a recipient to the queue
     *
     * $recipient = [
     *     'email'   => 'me@domain.com', // Required
     *     'name'    => 'My Name',       // Everything else is optional for individual message customization
     *     'company' => 'My Company'
     * ]
     *
     * @param  array $recipient
     * @throws Exception
     * @return Queue
     */
    public function addRecipient(array $recipient): Queue
    {
        if (!isset($recipient['email'])) {
            throw new Exception("Error: The recipient's array must contain at least an 'email' key.");
        }
        if (!in_array($recipient, $this->recipients, true)) {
            $this->recipients[] = $recipient;
        }

        return $this;
    }

    /**
     * Set (and clear) messages in the queue
     *
     * @param  array $messages
     * @return Queue
     */
    public function setMessages(array $messages): Queue
    {
        $this->messages = [];
        foreach ($messages as $message) {
            $this->addMessage($message);
        }
        return $this;
    }

    /**
     * Add messages to the queue
     *
     * @param  array $messages
     * @return Queue
     */
    public function addMessages(array $messages): Queue
    {
        foreach ($messages as $message) {
            $this->addMessage($message);
        }
        return $this;
    }

    /**
     * Add a message to the queue
     *
     * @param  Message $message
     * @return Queue
     */
    public function addMessage(Message $message): Queue
    {
        if (!in_array($message, $this->messages, true)) {
            $this->messages[] = $message;
        }
        return $this;
    }

    /**
     * Get messages
     *
     * @return array
     */
    public function getMessages(): array
    {
        return $this->messages;
    }

    /**
     * Get recipients
     *
     * @return array
     */
    public function getRecipients(): array
    {
        return $this->recipients;
    }

    /**
     * Get prepared messages
     *
     * @return array
     */
    public function getPreparedMessages(): array
    {
        return $this->prepared;
    }

    /**
     * Prepare queue for sending messages
     *
     * @return array
     */
    public function prepare(): array
    {
        foreach ($this->recipients as $recipient) {
            foreach ($this->messages as $message) {
                $to  = (isset($recipient['name'])) ? [$recipient['email'] => $recipient['name']] : $recipient['email'];

                $msg = clone $message;
                $msg->setTo($to);

                foreach ($msg->getParts() as $i => $part) {
                    if (!($part instanceof Message\Attachment)) {
                        $subject = $msg->getSubject();
                        $content = $part->getContent();
                        foreach ($recipient as $key => $value) {
                            $subject = str_replace('[{' . $key . '}]', $value, $subject);
                            $content = str_replace('[{' . $key . '}]', $value, $content);
                        }
                        $msg->setSubject($subject);
                        $msg->getPart($i)->setContent($content);
                    }
                }
                $this->prepared[] = $msg;
            }
        }

        return $this->prepared;
    }

}
