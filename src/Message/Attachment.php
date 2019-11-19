<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp-framework
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2020 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Pop\Mail\Message;

/**
 * Attachment message part class
 *
 * @category   Pop
 * @package    Pop\Mail
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2020 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    3.5.0
 */
class Attachment extends AbstractPart
{

    /**
     * File attachment basename
     * @var string
     */
    protected $basename = null;

    /**
     * File attachment original stream content
     * @var string
     */
    protected $stream = null;

    /**
     * Common content types for auto-detection
     * @var array
     */
    protected $contentTypes = [
        'csv'    => 'text/csv',
        'doc'    => 'application/msword',
        'docx'   => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'gif'    => 'image/gif',
        'html'   => 'text/html',
        'htm'    => 'text/html',
        'jpe'    => 'image/jpeg',
        'jpg'    => 'image/jpeg',
        'jpeg'   => 'image/jpeg',
        'log'    => 'text/plain',
        'md'     => 'text/plain',
        'pdf'    => 'application/pdf',
        'png'    => 'image/png',
        'ppt'    => 'application/vnd.ms-powerpoint',
        'pptx'   => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'svg'    => 'image/svg+xml',
        'tif'    => 'image/tiff',
        'tiff'   => 'image/tiff',
        'tsv'    => 'text/tsv',
        'txt'    => 'text/plain',
        'xls'    => 'application/vnd.ms-excel',
        'xlsx'   => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'zip'    => 'application/x-zip'
    ];

    /**
     * Constructor
     *
     * Instantiate the mail attachment object
     *
     * @param  string  $file
     * @param  string  $stream
     * @param  array   $options  ['contentType', 'basename', 'encoding', 'chunk']
     * @throws Exception
     */
    public function __construct($file = null, $stream = null, array $options = [])
    {
        if (null !== $stream) {
            $this->stream   = $stream;
            $this->basename = $options['basename'] ?? 'file.tmp';
        } else if (null !== $file) {
            if (!file_exists($file)) {
                throw new Exception("Error: The file '" . $file . "' does not exist.");
            } else {
                $this->stream   = file_get_contents($file);
                $this->basename = basename($file);
            }
        }

        $chunk       = (isset($options['chunk'])) ? (bool)$options['chunk'] : false;
        $contentType = null;
        $encoding    = null;

        // Set encoding
        if (isset($options['encoding'])) {
            switch (strtoupper($options['encoding'])) {
                case self::BASE64:
                case self::QUOTED_PRINTABLE:
                case self::BINARY:
                case self::_8BIT:
                case self::_7BIT:
                    $encoding = strtoupper($options['encoding']);
            }
        }

        // Set content type
        foreach ($options as $key => $value) {
            $key = strtolower($key);
            if (($key == 'content-type') || ($key == 'contenttype') ||
                ($key == 'mime-type') || ($key == 'mimetype') || ($key == 'mime')) {
                $contentType = $value;
            }
        }

        // Fallback content type detection
        if ((null === $contentType) && (strpos($this->basename, '.') !== false)) {
            $pathInfo    = pathinfo($this->basename);
            $ext         = strtolower($pathInfo['extension']);
            $contentType = (array_key_exists($ext, $this->contentTypes)) ?
                $this->contentTypes[$ext] : 'application/octet-stream';
        }

        parent::__construct($this->stream, $contentType . '; name="' . $this->basename . '"', $encoding, $chunk);

        $this->addHeader('Content-Description', $this->basename)
             ->addHeader('Content-Disposition', 'attachment; filename="' . $this->basename . '"')
             ->setCharSet(null);
    }

    /**
     * Create attachment from file
     *
     * @param  string  $file
     * @param  array   $options  ['contentType', 'basename', 'encoding', 'chunk']
     * @return self
     */
    public static function createFromFile($file, array $options = [])
    {
        return new self($file, null, $options);
    }

    /**
     * Create attachment from stream
     *
     * @param  string  $stream
     * @param  array   $options  ['contentType', 'basename', 'encoding', 'chunk']
     * @return self
     */
    public static function createFromStream($stream, array $options = [])
    {
        return new self(null, $stream, $options);
    }

    /**
     * Get attachment basename
     *
     * @return string
     */
    public function getBasename()
    {
        return $this->basename;
    }

    /**
     * Get attachment original stream content
     *
     * @return string
     */
    public function getStream()
    {
        return $this->stream;
    }

}
