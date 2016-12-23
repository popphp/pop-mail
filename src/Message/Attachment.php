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
 * Attachment class
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
     * Constructor
     *
     * Instantiate the mail attachment object
     *
     * @param  string $file
     * @param  string $contentType
     * @throws Exception
     */
    public function __construct($file, $contentType = 'file')
    {
        // Determine if the file is valid.
        if (!file_exists($file)) {
            throw new Exception('Error: The file does not exist.');
        }

        parent::__construct(
            chunk_split(base64_encode(file_get_contents($file))),
            $contentType . '; name="' . $this->basename . '"'
        );

        $this->basename = basename($file);
        $this->addHeader('Content-Transfer-Encoding', 'base64')
             ->addHeader('Content-Description', $this->basename)
             ->addHeader('Content-Disposition', 'attachment; filename="' . $this->basename . '"');
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

}
