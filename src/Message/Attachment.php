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
 * Attachment message part class
 *
 * @category   Pop
 * @package    Pop\Mail
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2019 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    3.1.0
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
     * Content-types for auto-detection
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
     * @param  string  $contentType
     * @param  string  $basename
     * @param  string  $encoding
     * @param  boolean $isStream
     * @param  boolean $chunk
     * @throws Exception
     */
    public function __construct($file, $contentType = 'file', $basename = 'file.tmp', $encoding = AbstractPart::BASE64, $isStream = false, $chunk = true)
    {
        if ($isStream) {
            $this->stream   = $file;
            $this->basename = $basename;
        } else if (!file_exists($file)) {
            throw new Exception('Error: That file does not exist.');
        } else {
            $this->stream   = file_get_contents($file);
            $this->basename = basename($file);
        }

        if (($contentType == 'file') && (strpos($this->basename, '.') !== false)) {
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
