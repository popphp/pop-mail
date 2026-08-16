<?php
declare(strict_types=1);
/**
 * Pop PHP Framework (https://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp-framework
 * @author     Nick Sagona, III <dev@noladev.com>
 * @copyright  Copyright (c) 2009-2027 NOLA Interactive, LLC.
 * @license    https://www.popphp.org/license     New BSD License
 */

namespace Pop\Mail\Message;

use Pop\Mime\Part;

/**
 * Attachment message part class
 *
 * @category   Pop
 * @package    Pop\Mail
 * @author     Nick Sagona, III <dev@noladev.com>
 * @copyright  Copyright (c) 2009-2027 NOLA Interactive, LLC.
 * @license    https://www.popphp.org/license     New BSD License
 * @version    5.0.0
 */
class Attachment extends Part
{
    use CharsetAwareTrait;
    use PartContentTrait;

    /**
     * The original on-disk path this attachment was created from, if any.
     * Deliberately diverges from parent::getFilename() (which returns the
     * basename, decoded from headers) - three transports need the real
     * openable path for curl_file_create().
     * @var ?string
     */
    protected ?string $sourcePath = null;

    /**
     * Create an attachment from a file on disk
     *
     * @param  string             $file
     * @param  ?string            $contentType
     * @param  string             $disposition
     * @param  Part\Body\Encoding $encoding
     * @param  int|bool           $split
     * @throws Exception
     * @return static
     */
    public static function create(
        string $file, ?string $contentType = null, string $disposition = 'attachment',
        Part\Body\Encoding $encoding = Part\Body\Encoding::BASE64, int|bool $split = true
    ): static
    {
        if (!file_exists($file)) {
            throw new Exception("Error: The file '" . $file . "' does not exist.");
        }
        $attachment = parent::attachment($file, $contentType, $disposition, $encoding, $split);
        $attachment->sourcePath = $file;
        $attachment->addHeader('Content-Description', basename($file));
        return $attachment;
    }

    /**
     * Create an attachment from in-memory content
     *
     * @param  string             $content
     * @param  string             $filename
     * @param  ?string            $contentType
     * @param  string             $disposition
     * @param  Part\Body\Encoding $encoding
     * @param  int|bool           $split
     * @return static
     */
    public static function createFromContent(
        string $content, string $filename, ?string $contentType = null, string $disposition = 'attachment',
        Part\Body\Encoding $encoding = Part\Body\Encoding::BASE64, int|bool $split = true
    ): static
    {
        $attachment = parent::attachmentFromContent($content, $filename, $contentType, $disposition, $encoding, $split);
        $attachment->addHeader('Content-Description', basename($filename));
        return $attachment;
    }

    /**
     * Get the real on-disk path this attachment was created from.
     *
     * No fallback to parent::getFilename() is used deliberately. The parent method
     * returns a basename decoded from headers; Part::attachmentFromContent() marks
     * the body as file-like and unconditionally sets a Content-Disposition filename=
     * parameter. A fallback would return a fabricated basename (e.g. 'generated.txt')
     * instead of null for in-memory attachments created via createFromContent().
     * This method guarantees null for in-memory attachments to honor that contract.
     *
     * @return ?string The real path if created from a file, null if in-memory
     */
    public function getFilename(): ?string
    {
        return $this->sourcePath;
    }

    /**
     * Get the basename decoded from headers (the parent's original getFilename() behavior)
     *
     * @return ?string
     */
    public function getBasename(): ?string
    {
        return parent::getFilename();
    }

}
