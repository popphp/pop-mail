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
namespace Pop\Mail\Message;

/**
 * Attachment message part class
 *
 * @category   Pop
 * @package    Pop\Mail
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2016 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    3.0.0
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
     * Constructor
     *
     * Instantiate the mail attachment object
     *
     * @param  string $file
     * @param  string $contentType
     * @param  string $basename
     */
    public function __construct($file, $contentType = 'file', $basename = 'file.tmp')
    {
        if (@file_exists($file)) {
            $this->stream   = file_get_contents($file);
            $this->basename = basename($file);
        } else {
            $this->stream   = $file;
            $this->basename = $basename;
        }

        parent::__construct(
            chunk_split(base64_encode($this->stream)),
            $contentType . '; name="' . $this->basename . '"'
        );

        $this->addHeader('Content-Transfer-Encoding', 'base64')
             ->addHeader('Content-Description', $this->basename)
             ->addHeader('Content-Disposition', 'attachment; filename="' . $this->basename . '"')
             ->setCharSet('');
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
