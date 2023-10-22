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

use Pop\Mail\Message;

/**
 * Mail client IMAP class
 *
 *  NOTE: Many enterprise mail applications have discontinued support of IMAP and it is no longer allowed.
 *
 * @category   Pop
 * @package    Pop\Mail
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2024 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    4.0.0
 */
class Imap extends AbstractClient
{

    /**
     * Mailbox connection string
     * @var ?string
     */
    protected ?string $connectionString = null;

    /**
     * Mailbox connection resource
     * @var mixed
     */
    protected mixed $connection = null;

    /**
     * Constructor
     *
     * Instantiate the IMAP mail client object
     *
     * @param string     $host
     * @param int|string $port
     * @param string     $service
     */
    public function __construct(string $host, int|string $port, string $service = 'imap')
    {
        parent::__construct($host, $port, $service);
    }

    /**
     * Connect to an IMAP mailbox
     *
     * @param  array   $creds
     * @param  ?string $flags
     * @param  ?int    $options
     * @param  ?int    $retries
     * @param  ?array  $params
     * @return Imap
     */
    public static function connect(
        array $creds, ?string $flags = null, ?int $options = null, ?int $retries = null, ?array $params = null
    ): Imap
    {
        if (!isset($creds['host']) || !isset($creds['port']) || !isset($creds['username']) || !isset($creds['password'])) {
            throw new Exception(
                "Error: The credentials were incomplete. They must contain 'host', 'port', 'username' and 'password'."
            );
        }

        $imap = new static($creds['host'], $creds['port']);
        $imap->setUsername($creds['username'])
             ->setPassword($creds['password']);

        if (isset($creds['folder'])) {
            $imap->setFolder($creds['folder']);
        }

        return $imap->open($flags, $options, $retries, $params);
    }

    /**
     * Open mailbox connection
     *
     * @param  ?string $flags
     * @param  ?int    $options
     * @param  ?int    $retries
     * @param  ?array  $params
     * @return Imap
     */
    public function open(?string $flags = null, ?int $options = null, ?int $retries = null, ?array $params = null): Imap
    {
        $this->connectionString = '{' . $this->host . ':' . $this->port . '/' . $this->service;

        if ($flags !== null) {
            $this->connectionString .= $flags;
        }

        $this->connectionString .= '}';

        if (($options !== null) && ($retries !== null) && ($params !== null)) {
            $this->connection = imap_open(
                $this->connectionString . $this->folder, $this->username, $this->password, $options, $retries, $params
            );
        } else if (($options !== null) && ($retries !== null)) {
            $this->connection = imap_open(
                $this->connectionString . $this->folder, $this->username, $this->password, $options, $retries
            );
        } else if ($options !== null) {
            $this->connection = imap_open(
                $this->connectionString . $this->folder, $this->username, $this->password, $options
            );
        } else {
            $this->connection = imap_open(
                $this->connectionString . $this->folder, $this->username, $this->password
            );
        }

        return $this;
    }

    /**
     * Determine if the mailbox connection has been opened
     *
     * @return bool
     */
    public function isOpen(): bool
    {
        return is_resource($this->connection);
    }

    /**
     * Get mailbox connection
     *
     * @return mixed
     */
    public function connection(): mixed
    {
        return $this->connection;
    }

    /**
     * Get mailbox connection string
     *
     * @return ?string
     */
    public function getConnectionString(): ?string
    {
        return $this->connectionString;
    }

    /**
     * List mailboxes
     *
     * @param  string $pattern
     * @return array
     */
    public function listMailboxes(string $pattern = '*'): array
    {
        return imap_list($this->connection, $this->connectionString . $this->folder, $pattern);
    }

    /**
     * Get mailbox status
     *
     * @return \stdClass
     */
    public function getStatus(): \stdClass
    {
        return imap_status($this->connection, $this->connectionString . $this->folder, SA_ALL);

    }

    /**
     * Get mailbox info
     *
     * @return \stdClass
     */
    public function getInfo(): \stdClass
    {
        return imap_mailboxmsginfo($this->connection);

    }
    /**
     * Get total number of messages
     *
     * @return int
     */
    public function getNumberOfMessages(): int
    {
        return imap_num_msg($this->connection);
    }

    /**
     * Get total number of read messages
     *
     * @return int
     */
    public function getNumberOfReadMessages(): int
    {
        return abs($this->getNumberOfMessages() - $this->getStatus()->unseen);
    }

    /**
     * Get total number of unread messages
     *
     * @return int
     */
    public function getNumberOfUnreadMessages(): int
    {
        return $this->getStatus()->unseen;
    }

    /**
     * Get message overviews
     *
     * @param  mixed  $ids
     * @param  int    $options
     * @return array
     */
    public function getOverview(mixed $ids, int $options = FT_UID): array
    {
        if (is_array($ids)) {
            $ids = implode(',', $ids);
        }
        return imap_fetch_overview($this->connection, $ids, $options);
    }

    /**
     * Get message IDs from a mailbox
     *
     * @param  string $criteria
     * @param  int    $options
     * @param  ?string $charset
     * @return array
     */
    public function getMessageIds(string $criteria = 'ALL', int $options = SE_UID, ?string $charset = null): array
    {
        return ($charset !== null) ?
            imap_search($this->connection, $criteria, $options, $charset) :
            imap_search($this->connection, $criteria, $options);
    }

    /**
     * Get message IDs from a mailbox by a sort criteria
     *
     * @param  int     $criteria
     * @param  bool    $reverse
     * @param  int     $options
     * @param  string  $search
     * @param  s?tring $charset
     * @return array
     */
    public function getMessageIdsBy(
        int $criteria = SORTDATE, bool $reverse = true, int $options = SE_UID, string $search = 'ALL', ?string $charset = null
    ): array
    {
        return ($charset !== null) ?
            imap_sort($this->connection, $criteria, (int)$reverse, $options, $search, $charset) :
            imap_sort($this->connection, $criteria, (int)$reverse, $options, $search);
    }

    /**
     * Get message headers from a mailbox
     *
     * @param  string  $criteria
     * @param  int     $options
     * @param  ?string $charset
     * @return array
     */
    public function getMessageHeaders(string $criteria = 'ALL', int $options = SE_UID, ?string $charset = null): array
    {
        $headers = [];
        $ids     = $this->getMessageIds($criteria, $options, $charset);

        foreach ($ids as $id) {
            $headers[$id] =  $this->getMessageHeadersById($id);
        }

        return $headers;
    }

    /**
     * Get message headers from a mailbox
     *
     * @param  int     $criteria
     * @param  bool    $reverse
     * @param  int     $options
     * @param  string  $search
     * @param  ?string $charset
     * @return array
     */
    public function getMessageHeadersBy(
        int $criteria = SORTDATE, bool $reverse = true, int $options = SE_UID, string $search = 'ALL', ?string $charset = null
    ): array
    {
        $headers = [];
        $ids     = $this->getMessageIdsBy($criteria, $reverse, $options, $search, $charset);

        foreach ($ids as $id) {
            $headers[$id] =  $this->getMessageHeadersById($id);
        }

        return $headers;
    }

    /**
     * Get message number from UID
     *
     * @param  int|string $id
     * @return int
     */
    public function getMessageNumber(int|string $id): int
    {
        return imap_msgno($this->connection, $id);
    }

    /**
     * Get raw message headers by message ID
     *
     * @param  int|string $id
     * @return array
     */
    public function getMessageHeaderInfoById(int|string $id): array
    {
        $headers = imap_headerinfo($this->connection, imap_msgno($this->connection, $id));
        return ($headers !== false) ? json_decode(json_encode($headers), true) : [];
    }

    /**
     * Get raw message headers by message ID
     *
     * @param  int|string $id
     * @return array
     */
    public function getMessageRawHeadersById(int|string $id): array
    {
        $headers       = explode("\r\n", imap_fetchheader($this->connection, $id, FT_UID));
        $parsedHeaders = [];
        $name          = null;

        foreach ($headers as $header) {
            if (((str_starts_with($header, ' ')) || (str_starts_with($header, "\t"))) &&
                ($name !== null) && isset($parsedHeaders[$name])) {
                if (is_array($parsedHeaders[$name])) {
                    $parsedHeaders[$name][key($parsedHeaders[$name])] .= $header;
                } else {
                    $parsedHeaders[$name] .= $header;
                }
            } else if (!empty($header) && (str_contains($header, ':'))) {
                $name  = substr($header, 0, strpos($header, ':'));
                $value = (strpos($header, ':') < (strlen($header) - 1)) ? substr($header, (strpos($header, ': ') + 2)) : '';

                if (isset($parsedHeaders[$name])) {
                    if (!is_array($parsedHeaders[$name])) {
                        $parsedHeaders[$name] = [$parsedHeaders[$name]];
                    }
                    $parsedHeaders[$name][] = $value;
                    end($parsedHeaders[$name]);
                } else {
                    $parsedHeaders[$name] = $value;
                }
            }
        }

        return array_map(function($value) {
            if (is_array($value)) {
                return array_map('trim', $value);
            } else {
                return trim($value);
            }
        }, $parsedHeaders);
    }

    /**
     * Get message headers by message ID
     *
     * @param  int|string $id
     * @return array
     */
    public function getMessageHeadersById(int|string $id): array
    {
        $headers = imap_rfc822_parse_headers(imap_fetchheader($this->connection, $id, FT_UID));
        return json_decode(json_encode($headers), true);
    }

    /**
     * Get message structure by message ID
     *
     * @param  int|string $id
     * @return \stdClass
     */
    public function getMessageStructure(int|string $id): \stdClass
    {
        return imap_fetchstructure($this->connection, $id, FT_UID);
    }

    /**
     * Get message boundary by message ID
     *
     * @param  int|string $id
     * @return string|null
     */
    public function getMessageBoundary(int|string $id): string|null
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
     * @param  int|string $id
     * @return string
     */
    public function getMessageBody(int|string $id): string
    {
        return imap_body($this->connection, $id, FT_UID);
    }

    /**
     * Get message parts by message ID
     *
     * @param  int|string $id
     * @return array
     */
    public function getMessageParts(int|string $id): array
    {
        $boundary = $this->getMessageBoundary($id);
        $body     = $this->getMessageBody($id);
        return Message\Part::parse($body, $boundary);
    }

    /**
     * Get message parts by message ID
     *
     * @param  int|string $id
     * @return array
     */
    public function getMessageAttachments(int|string $id): array
    {
        return array_filter($this->getMessageParts($id), function($part){
            return $part->attachment;
        });
    }

    /**
     * Get message parts by message ID
     *
     * @param  int|string $id
     * @param  ?string    $encoding
     * @return bool
     */
    public function hasMessageAttachments(int|string $id, ?string $encoding = null): bool
    {
        return (count($this->getMessageAttachments($id, $encoding)) > 0);
    }

    /**
     * Copy messages to another mailbox
     *
     * @param  mixed        $ids
     * @param  string|array $to
     * @param  int          $options
     * @return Imap
     */
    public function copyMessage(mixed $ids, string|array $to, int $options = CP_UID): Imap
    {
        if (is_array($ids)) {
            $ids = implode(',', $ids);
        }

        imap_mail_copy($this->connection, $ids, $to, $options);
        return $this;
    }

    /**
     * Move messages to another mailbox
     *
     * @param  mixed        $ids
     * @param  string|array $to
     * @param  int          $options
     * @return Imap
     */
    public function moveMessage(mixed $ids, string|array $to, int $options = CP_UID): Imap
    {
        if (is_array($ids)) {
            $ids = implode(',', $ids);
        }

        imap_mail_move($this->connection, $ids, $to, $options);
        return $this;
    }

    /**
     * Mark a message or messages as read
     *
     * @param  mixed $ids
     * @param  int   $options
     * @return Imap
     */
    public function markAsRead(mixed $ids, int $options = ST_UID): Imap
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
    public function markAsUnread(mixed $ids, int $options = ST_UID): Imap
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
    public function setMessageFlags(mixed $ids, string $flags, int $options = ST_UID): Imap
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
    public function clearMessageFlags(mixed $ids, string $flags, int $options = ST_UID): Imap
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
     * @param  int|string $id
     * @param  int $options
     * @return Imap
     */
    public function deleteMessage(int|string $id, int $options = FT_UID): Imap
    {
        imap_delete($this->connection, $id, $options);
        return $this;
    }

    /**
     * Create mailbox
     *
     * @param  string $new
     * @return Imap
     */
    public function createMailbox(string $new): Imap
    {
        if (!str_contains($new, $this->connectionString)) {
            $new = $this->connectionString . $new;
        }
        imap_createmailbox($this->connection, $new);
        return $this;
    }

    /**
     * Rename mailbox
     *
     * @param  string  $new
     * @param  ?string $old
     * @return Imap
     */
    public function renameMailbox(string $new, ?string $old = null): Imap
    {
        if ($old === null) {
            $old = $this->connectionString . $this->folder;
        } else if (!str_contains($old, $this->connectionString)) {
            $old = $this->connectionString . $old;
        }

        if (!str_contains($new, $this->connectionString)) {
            $new = $this->connectionString . $new;
        }

        imap_renamemailbox($this->connection, $old, $new);
        return $this;
    }

    /**
     * Delete mailbox
     *
     * @param  ?string $mailbox
     * @throws Exception
     * @return Imap
     */
    public function deleteMailbox(?string $mailbox = null): Imap
    {
        if ($mailbox === null) {
            $mailbox = $this->folder;
        }

        if (empty($mailbox)) {
            throw new Exception('Error: The mailbox is not set.');
        }

        imap_deletemailbox($this->connection, $this->connectionString . $mailbox);
        return $this;
    }

    /**
     * Decode text
     *
     * @param  string $text
     * @return string
     */
    public  function decodeText(string $text): string
    {
        return Message::decodeText($text);
    }

    /**
     * Close the mailbox connection resource
     *
     * @return void
     */
    public function close(): void
    {
        if (is_resource($this->connection)) {
            imap_close($this->connection);
        }
    }

}
