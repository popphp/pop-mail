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
namespace Pop\Mail\Transport\Smtp\Stream\Byte;

/**
 * Temporary file byte stream class
 *
 * @category   Pop
 * @package    Pop\Mail
 * @author     Romain-Geissler from the SwiftMailer library https://github.com/swiftmailer/swiftmailer
 * @version    3.1.0
 */
class TemporaryFileByteStream extends FileByteStream
{
    /**
     * Create a new TemporaryFileByteStream
     *
     * @throws Exception
     */
    public function __construct()
    {
        $filePath = tempnam(sys_get_temp_dir(), 'FileByteStream');

        if ($filePath === false) {
            throw new Exception('Failed to retrieve temporary file name.');
        }

        parent::__construct($filePath, true);
    }

    /**
     * Get content
     *
     * @throws Exception
     * @return string
     */
    public function getContent()
    {
        if (($content = file_get_contents($this->getPath())) === false) {
            throw new Exception('Failed to get temporary file content.');
        }

        return $content;
    }

    /**
     * Destructor
     */
    public function __destruct()
    {
        if (file_exists($this->getPath())) {
            @unlink($this->getPath());
        }
    }

}
