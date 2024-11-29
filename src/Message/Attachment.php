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
namespace Pop\Mail\Message;

/**
 * Attachment message part class
 *
 * @category   Pop
 * @package    Pop\Mail
 * @author     Nick Sagona, III <dev@noladev.com>
 * @copyright  Copyright (c) 2009-2025 NOLA Interactive, LLC.
 * @license    https://www.popphp.org/license     New BSD License
 * @version    4.0.3
 */
class Attachment extends AbstractPart
{

    /**
     * File attachment
     * @var ?string
     */
    protected ?string $filename = null;

    /**
     * File attachment basename
     * @var ?string
     */
    protected ?string $basename = null;

    /**
     * File attachment original stream content
     * @var ?string
     */
    protected ?string $stream = null;

    /**
     * Common content types for auto-detection
     * @var array
     */
    protected array $contentTypes = [
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
     * @param  ?string $file
     * @param  ?string $stream
     * @param  array   $options  ['contentType', 'basename', 'encoding', 'chunk']
     * @throws Exception
     */
    public function __construct(?string $file = null, ?string $stream = null, array $options = [])
    {
        if ($stream !== null) {
            $this->stream   = $stream;
            $this->basename = $options['basename'] ?? 'file.tmp';
        } else if ($file !== null) {
            if (!file_exists($file)) {
                throw new Exception("Error: The file '" . $file . "' does not exist.");
            } else {
                $this->filename = $file;
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
                case Attachment::BASE64:
                case Attachment::QUOTED_PRINTABLE:
                case Attachment::BINARY:
                case Attachment::_8BIT:
                case Attachment::_7BIT:
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
        if (($contentType === null) && (str_contains($this->basename, '.'))) {
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
     * @return Attachment
     */
    public static function createFromFile(string $file, array $options = []): Attachment
    {
        return new Attachment($file, null, $options);
    }

    /**
     * Create attachment from stream
     *
     * @param  string  $stream
     * @param  array   $options  ['contentType', 'basename', 'encoding', 'chunk']
     * @return Attachment
     */
    public static function createFromStream(string $stream, array $options = []): Attachment
    {
        return new Attachment(null, $stream, $options);
    }

    /**
     * Get attachment filename
     *
     * @return ?string
     */
    public function getFilename(): ?string
    {
        return $this->filename;
    }

    /**
     * Get attachment basename
     *
     * @return ?string
     */
    public function getBasename(): ?string
    {
        return $this->basename;
    }

    /**
     * Get attachment original stream content
     *
     * @return ?string
     */
    public function getStream(): ?string
    {
        return $this->stream;
    }

}
