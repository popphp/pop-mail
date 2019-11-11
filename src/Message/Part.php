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
namespace Pop\Mail\Message;

/**
 * Message part object class
 *
 * @category   Pop
 * @package    Pop\Mail
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2019 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    3.1.0
 */
class Part implements \ArrayAccess, \Countable, \IteratorAggregate
{

    /**
     * Part data array
     * @var array
     */
    protected $data = [];

    /**
     * Constructor
     *
     * Instantiate a part object
     *
     * @param  array $data
     */
    public function __construct(array $data = [])
    {
        $this->data = $data;
    }

    /**
     * Method to get the count of items in the model
     *
     * @return int
     */
    public function count()
    {
        return count($this->data);
    }

    /**
     * Method to iterate over the data
     *
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->data);
    }

    /**
     * Return all model data as an array
     *
     * @return array
     */
    public function toArray()
    {
        return $this->data;
    }

    /**
     * Magic get method to return the value of data[$name].
     *
     * @param  string $name
     * @return mixed
     */
    public function __get($name)
    {
        return (array_key_exists($name, $this->data)) ? $this->data[$name] : null;
    }

    /**
     * Magic set method to set the property to the value of data[$name].
     *
     * @param  string $name
     * @param  mixed  $value
     * @return void
     */
    public function __set($name, $value)
    {
        $this->data[$name] = $value;
    }

    /**
     * Return the isset value of data[$name].
     *
     * @param  string $name
     * @return boolean
     */
    public function __isset($name)
    {
        return isset($this->data[$name]);
    }

    /**
     * Unset data[$name].
     *
     * @param  string $name
     * @return void
     */
    public function __unset($name)
    {
        unset($this->data[$name]);
    }

    /**
     * ArrayAccess offsetExists
     *
     * @param  mixed $offset
     * @return boolean
     */
    public function offsetExists($offset)
    {
        return $this->__isset($offset);
    }

    /**
     * ArrayAccess offsetGet
     *
     * @param  mixed $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->__get($offset);
    }

    /**
     * ArrayAccess offsetSet
     *
     * @param  mixed $offset
     * @param  mixed $value
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        $this->__set($offset, $value);
    }

    /**
     * ArrayAccess offsetUnset
     *
     * @param  mixed $offset
     * @return void
     */
    public function offsetUnset($offset)
    {
        $this->__unset($offset);
    }

    /**
     * Parse message parts from string
     *
     * @param  array  $parts
     * @param  string $encoding
     * @return array
     */
    public static function parse(array $parts, $encoding = null)
    {
        foreach ($parts as $i => $part) {
            $part = trim($part);
            if (strtolower($encoding) == 'base64') {
                $part = base64_decode($part);
            } else if (strtolower($encoding) == 'quoted-printable') {
                $part = quoted_printable_decode($part);
            }
            if (($part == '--') || empty($part)) {
                unset($parts[$i]);
            } else {
                $type       = null;
                $basename   = null;
                $headersAry = [];
                if ((strpos($part, "\r\n\r\n") !== false) && (substr($part, 0, 1) != '<')) {
                    $headers = substr($part, 0, strpos($part, "\r\n\r\n"));
                    if (strpos($headers, "\r\n\t") !== false) {
                        $headers = str_replace("\r\n\t", " ", $headers);
                    }
                    if (strpos($headers, "\n\t") !== false) {
                        $headers = str_replace("\n\t", " ", $headers);
                    }
                    if (strpos($part, "\r\n\t") !== false) {
                        $part = str_replace("\r\n\t", " ", $part);
                    }
                    if (strpos($part, "\n\t") !== false) {
                        $part = str_replace("\n\t", " ", $part);
                    }
                    $matches = [];
                    preg_match_all('/("[^"\n]*)\r?\n(?!(([^"]*"){2})*[^"]*$)/mi', $headers, $matches);

                    // Check for newlines inside header values
                    if (isset($matches[0]) && isset($matches[0][0])) {
                        $headers = str_replace($matches[0][0], trim($matches[0][0]), $headers);
                    }

                    $headers    = explode("\r\n", $headers);
                    $headersAry = [];
                    $part       = trim(substr($part, (strpos($part, "\r\n\r\n") + 4)));
                    foreach ($headers as $header) {
                        if (strpos($header, ':') !== false) {
                            $name  = trim(substr($header, 0, strpos($header, ':')));
                            $value = trim(substr($header, (strpos($header, ': ') + 2)));
                        } else if (strpos($header, '=') !== false) {
                            $name  = trim(substr($header, 0, strpos($header, '=')));
                            $value = trim(substr($header, (strpos($header, '=') + 1)));
                        } else {
                            $name  = null;
                            $value = null;
                        }
                        if ((null !== $name) && (null !== $value)) {
                            if ((substr($value, 0, 1) == '"') && (substr($value, -1) == '"')) {
                                $value = substr($value, 1);
                                $value = substr($value, 0, -1);
                            }

                            $name = implode('-', array_map(function($value) {
                                return ucfirst(strtolower($value));
                            }, explode('-', $name)));

                            if (strpos($value, ';') !== false) {
                                $subheaders = explode(';', str_replace('; ', ';', $value));
                                $value = $subheaders[0];
                                unset($subheaders[0]);
                                foreach ($subheaders as $subheader) {
                                    if (strpos($subheader, '=') !== false) {
                                        [$subheaderName, $subheaderValue] = array_map('trim', explode('=', $subheader));
                                        if ((substr($subheaderValue, 0, 1) == '"') && (substr($subheaderValue, -1) == '"')) {
                                            $subheaderValue = substr($subheaderValue, 1);
                                            $subheaderValue = substr($subheaderValue, 0, -1);
                                        }

                                        $subheaderName = implode('-', array_map(function($value) {
                                            return ucfirst(strtolower($value));
                                        }, explode('-', $subheaderName)));

                                        $headersAry[$subheaderName] = $subheaderValue;
                                    }
                                }
                            }

                            $headersAry[$name] = $value;
                        }
                    }
                }

                if (substr($part, -2) == '--') {
                    $part = trim(substr($part, 0, -2));
                }

                $part = (isset($headersAry['Content-Transfer-Encoding']) && (strtolower($headersAry['Content-Transfer-Encoding']) == 'base64')) ?
                    base64_decode($part) : quoted_printable_decode($part);

                if (isset($headersAry['Content-Type'])) {
                    if ((stripos($headersAry['Content-Type'], 'multipart/') !== false) &&
                        (isset($headersAry['boundary']) || isset($headersAry['Boundary']))) {
                        $boundaryKey = (isset($headersAry['Boundary'])) ? 'Boundary' : 'boundary';
                        $subBody     = (strpos($part, $headersAry[$boundaryKey]) !== false) ?
                            explode($headersAry[$boundaryKey], $part) : [$part];
                        $subParts = self::parse($subBody);
                        unset($parts[$i]);
                        foreach ($subParts as $subPart) {
                            $parts[] = $subPart;
                        }
                    } else {
                        $type = $headersAry['Content-Type'];
                        if (strpos($type, ';') !== false) {
                            $type = trim(substr($type, 0, strpos($type, ';')));
                        }
                    }
                }

                $attachment = (isset($headersAry['Content-Disposition']) &&
                    ((stripos($headersAry['Content-Disposition'], 'attachment') !== false) || (stripos($headersAry['Content-Disposition'], 'name=') !== false)));

                if (isset($headersAry['Content-Disposition']) && (stripos($headersAry['Content-Disposition'], 'name=') !== false)) {
                    $basename = substr($headersAry['Content-Disposition'], (stripos($headersAry['Content-Disposition'], 'name=') + 5));
                    if (strpos($basename, ';') !== false) {
                        $basename = substr($basename, 0, strpos($basename, ';'));
                    }
                    $attachment = true;
                } else if (isset($headersAry['Content-Type']) && (stripos($headersAry['Content-Type'], 'name=') !== false)) {
                    $basename = substr($headersAry['Content-Type'], (stripos($headersAry['Content-Type'], 'name=') + 5));
                    if (strpos($basename, ';') !== false) {
                        $basename = substr($basename, 0, strpos($basename, ';'));
                    }
                    $attachment = true;
                } else if (isset($headersAry['Content-Description'])) {
                    $basename   = $headersAry['Content-Description'];
                    $attachment = true;
                } else if (isset($headersAry['Name'])) {
                    $basename   = $headersAry['Name'];
                    $attachment = true;
                } else if (isset($headersAry['Filename'])) {
                    $basename   = $headersAry['Filename'];
                    $attachment = true;
                }

                if ((substr($basename, 0, 1) == '"') && (substr($basename, -1) == '"')) {
                    $basename = substr($basename, 1);
                    $basename = substr($basename, 0, -1);
                }

                if (($attachment) && empty($basename) && isset($headersAry['filename'])) {
                    $basename = $headersAry['filename'];
                    if ((strpos($basename, 'UTF') !== false) || (strpos($basename, '?') !== false) ||
                        (strpos($basename, '=') !== false)) {
                        $basenameAry = imap_mime_header_decode($basename);
                        if (isset($basenameAry[0]) && isset($basenameAry[0]->text)) {
                            $basename = $basenameAry[0]->text;
                        }
                    }
                }

                $parts[$i] = new static([
                    'headers'    => $headersAry,
                    'type'       => $type,
                    'attachment' => $attachment,
                    'basename'   => $basename,
                    'content'    => $part
                ]);
            }
        }

        return array_values($parts);
    }

}
