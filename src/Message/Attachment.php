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
 * Mail fill attachment message class
 *
 * @category   Pop
 * @package    Pop_Mail
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
     * File attachment encoded content
     * @var string
     */
    protected $encoded = null;

    /**
     * Constructor
     *
     * Instantiate the mail attachment object
     *
     * @param  string $file
     * @throws Exception
     * @return Attachment
     */
    public function __construct($file)
    {
        // Determine if the file is valid.
        if (!file_exists($file)) {
            throw new Exception('Error: The file does not exist.');
        }

        // Encode the file contents and set the file into the attachments array property.
        $this->basename = basename($file);
        $this->encoded  = chunk_split(base64_encode(file_get_contents($file)));
        $this->content  = 'Content-Type: file; name="' . $this->basename .
            '"' . "\r\n" . 'Content-Transfer-Encoding: base64' . "\r\n" .
            'Content-Description: ' . $this->basename . "\r\n" .
            'Content-Disposition: attachment; filename="' . $this->basename .
            '"' . "\r\n" . "\r\n" . $this->encoded . "\r\n" . "\r\n";
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
     * Get attachment encoded content
     *
     * @return string
     */
    public function getEncoded()
    {
        return $this->encoded;
    }

}
