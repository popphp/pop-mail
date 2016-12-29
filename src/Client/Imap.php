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
namespace Pop\Mail\Client;

/**
 * Mail client IMAP class
 *
 * @category   Pop
 * @package    Pop\Mail
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2016 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    3.0.0
 */
class Imap extends AbstractClient
{

    /**
     * Mailbox connection string
     * @var string
     */
    protected $connectionString = null;

    /**
     * Mailbox connection resource
     * @var resource
     */
    protected $connection = null;

    /**
     * Constructor
     *
     * Instantiate the IMAP mail client object
     *
     * @param string $host
     * @param int    $port
     * @param string $service
     */
    public function __construct($host, $port, $service = 'imap')
    {
        parent::__construct($host, $port, $service);
    }

    /**
     * Open mailbox connection
     *
     * @param string $flags
     * @param int    $options
     * @param int    $retries
     * @param array  $params
     * @return Imap
     */
    public function open($flags = null, $options = null, $retries = null, array $params = null)
    {
        $this->connectionString = '{' . $this->host . ':' . $this->port . '/' . $this->service;

        if (null !== $flags) {
            $this->connectionString .= $flags;
        }

        $this->connectionString .= '}' . $this->folder;

        if ((null !== $options) && (null !== $retries) && (null !== $params)) {
            $this->connection = imap_open($this->connectionString, $this->username, $this->password, $options, $retries, $params);
        } else if ((null !== $options) && (null !== $retries)) {
            $this->connection = imap_open($this->connectionString, $this->username, $this->password, $options, $retries);
        } else if (null !== $options) {
            $this->connection = imap_open($this->connectionString, $this->username, $this->password, $options);
        } else {
            $this->connection = imap_open($this->connectionString, $this->username, $this->password);
        }

        return $this;
    }

    /**
     * Determine if the mailbox connection has been opened
     *
     * @return boolean
     */
    public function isOpen()
    {
        return is_resource($this->connection);
    }

    /**
     * Get mailbox connection
     *
     * @return resource
     */
    public function connection()
    {
        return $this->connection;
    }

    /**
     * Get mailbox connection string
     *
     * @return string
     */
    public function getConnectionString()
    {
        return $this->connectionString;
    }

    /**
     * List mailboxes
     *
     * @param string $pattern
     * @return array
     */
    public function listMailboxes($pattern = '*')
    {
        return imap_list($this->connection, $this->connectionString, $pattern);
    }

    /**
     * Get message IDs from a mailbox
     *
     * @param string $criteria
     * @param int    $options
     * @param string $charset
     * @return array
     */
    public function getMessageIds($criteria = 'ALL', $options = SE_UID, $charset = null)
    {
        return (null !== $charset) ?
            imap_search($this->connection, $criteria, $options, $charset) :
            imap_search($this->connection, $criteria, $options);
    }

    /**
     * Get message headers from a mailbox
     *
     * @param  string $criteria
     * @param  int    $options
     * @param  string $charset
     * @return array
     */
    public function getMessageHeaders($criteria = 'ALL', $options = SE_UID, $charset = null)
    {
        $headers = [];
        $ids     = $this->getMessageIds($criteria, $options, $charset);

        foreach ($ids as $id) {
            $headers[$id] =  imap_rfc822_parse_headers(imap_fetchheader($this->connection, $id, FT_UID));
        }

        return $headers;
    }

    /**
     * Get message headers by message ID
     *
     * @param  int $id
     * @return \stdClass
     */
    public function getMessageHeadersById($id)
    {
        return imap_rfc822_parse_headers(imap_fetchheader($this->connection, $id, FT_UID));
    }

    /**
     * Get message structure by message ID
     *
     * @param  int $id
     * @return \stdClass
     */
    public function getMessageStructure($id)
    {
        return imap_fetchstructure($this->connection, $id, FT_UID);
    }

    /**
     * Get message boundary by message ID
     *
     * @param  int $id
     * @return string
     */
    public function getMessageBoundary($id)
    {
        $boundary  = null;
        $structure = $this->getMessageStructure($id);

        if (isset($structure->parameters) && (count($structure->parameters) > 0)) {
            foreach ($structure->parameters as $parameter) {
                if (strtolower($parameter->attribute) == 'boundary') {
                    $boundary = $parameter->value;
                    break;
                }
            }
        }

        return $boundary;
    }

    /**
     * Get message body by message ID
     *
     * @param  int $id
     * @return string
     */
    public function getMessageBody($id)
    {
        return imap_body($this->connection, $id, FT_UID);
    }

    /**
     * Get message parts by message ID
     *
     * @param  int $id
     * @return array
     */
    public function getMessageParts($id)
    {
        $boundary = $this->getMessageBoundary($id);
        $body     = $this->getMessageBody($id);
        $parts    = (strpos($body, $boundary) !== false) ?
            explode($boundary, $body) : [$body];

        foreach ($parts as $i => $part) {
            $part = trim($part);
            if (($part == '--') || empty($part)) {
                unset($parts[$i]);
            } else {
                $headers    = substr($part, 0, strpos($part, "\r\n\r\n"));
                $headers    = explode("\r\n", $headers);
                $headersAry = [];
                $part       = trim(substr($part, (strpos($part, "\r\n\r\n") + 4)));
                foreach ($headers as $header) {
                    $name  = trim(substr($header, 0, strpos($header, ':')));
                    $value = trim(substr($header, (strpos($header, ': ') + 2)));
                    $headersAry[$name] = $value;
                }

                if (substr($part, -2) == '--') {
                    $part = trim(substr($part, 0, -2));
                }

                if (isset($headersAry['Content-Transfer-Encoding'])) {
                    switch (strtolower($headersAry['Content-Transfer-Encoding'])) {
                        case 'quoted-printable':
                            $part = quoted_printable_decode($part);
                            break;
                        case 'base64':
                            $part = base64_decode($part);
                            break;
                    }
                }

                $type = null;
                if (isset($headersAry['Content-Type'])) {
                    $type = $headersAry['Content-Type'];
                    if (strpos($type, ';') !== false) {
                        $type = trim(substr($type, 0, strpos($type, ';')));
                    }
                }

                $parts[$i] = new \ArrayObject([
                    'headers' => $headersAry,
                    'type'    => $type,
                    'content' => $part
                ], \ArrayObject::ARRAY_AS_PROPS);
            }
        }

        return array_values($parts);
    }

    /**
     * Mark a message or messages as read
     *
     * @param  mixed $ids
     * @param  int   $options
     * @return Imap
     */
    public function markAsRead($ids, $options = ST_UID)
    {
        return $this->setMessageFlags($ids, "\\Seen", $options);
    }

    /**
     * Mark a message or messages as unread
     *
     * @param  mixed $ids
     * @param  int   $options
     * @return Imap
     */
    public function markAsUnread($ids, $options = ST_UID)
    {
        return $this->clearMessageFlags($ids, "\\Seen", $options);
    }

    /**
     * Mark a message or messages as read
     *
     * @param  mixed  $ids
     * @param  string $flags
     * @param  int    $options
     * @return Imap
     */
    public function setMessageFlags($ids, $flags, $options = ST_UID)
    {
        if (is_array($ids)) {
            $ids = implode(',', $ids);
        }
        imap_setflag_full($this->connection, $ids, $flags, $options);

        return $this;
    }

    /**
     * Mark a message or messages as unread
     *
     * @param  mixed  $ids
     * @param  string $flags
     * @param  int    $options
     * @return Imap
     */
    public function clearMessageFlags($ids, $flags, $options = ST_UID)
    {
        if (is_array($ids)) {
            $ids = implode(',', $ids);
        }
        imap_clearflag_full($this->connection, $ids, $flags, $options);

        return $this;
    }

    /**
     * Delete message
     *
     * @param  int $id
     * @param  int $options
     * @return Imap
     */
    public function deleteMessage($id, $options = FT_UID)
    {
        imap_delete($this->connection, $id, $options);
        return $this;
    }

    /**
     * Delete message
     *
     * @param  string $mailbox
     * @return Imap
     */
    public function deleteMailbox($mailbox = null)
    {
        if (null === $mailbox) {
            $mailbox = $this->connectionString;
        }
        imap_deletemailbox($this->connection, $mailbox);
        return $this;
    }

    /**
     * Close the mailbox connection resource
     *
     * @return void
     */
    public function close()
    {
        if (is_resource($this->connection)) {
            imap_close($this->connection);
        }
    }

}
