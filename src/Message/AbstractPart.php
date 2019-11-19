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

use Pop\Mail\Message;

/**
 * Abstract mail message part class
 *
 * @category   Pop
 * @package    Pop\Mail
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2020 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    3.5.0
 */
abstract class AbstractPart extends AbstractMessage implements PartInterface
{

    /**
     * Encoding constants
     * @var string
     */
    const BASE64           = 'BASE64';
    const QUOTED_PRINTABLE = 'QUOTED_PRINTABLE';
    const BINARY           = 'BINARY';
    const _8BIT            = '_8BIT';
    const _7BIT            = '_7BIT';

    /**
     * Message part content
     * @var string
     */
    protected $content = null;

    /**
     * Message part encoding
     * @var string
     */
    protected $encoding = null;

    /**
     * Constructor
     *
     * Instantiate the message part object
     *
     * @param string  $content
     * @param string  $contentType
     * @param string  $encoding
     * @param boolean $chunk
     */
    public function __construct($content, $contentType = 'text/plain', $encoding = null, $chunk = false)
    {
        parent::__construct();

        if (null !== $encoding) {
            $this->setEncoding($encoding);

            switch ($this->encoding) {
                case self::BASE64:
                    $content = base64_encode($content);
                    $this->addHeader('Content-Transfer-Encoding', 'base64');
                    break;
                case self::QUOTED_PRINTABLE:
                    $content = quoted_printable_encode($content);
                    $this->addHeader('Content-Transfer-Encoding', 'quoted-printable');
                    break;
                case self::BINARY:
                    $this->addHeader('Content-Transfer-Encoding', 'binary');
                    break;
                case self::_7BIT:
                    $this->addHeader('Content-Transfer-Encoding', '7bit');
                    break;
                case self::_8BIT:
                    $this->addHeader('Content-Transfer-Encoding', '8bit');
                    break;
            }
        }

        if ($chunk) {
            $content = chunk_split($content);
        }

        $this->setContent($content);
        $this->setContentType($contentType);
    }

    /**
     * Get message part content
     *
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Set message part content
     *
     * @param  string $content
     * @return AbstractPart
     */
    public function setContent($content)
    {
        $this->content = $content;
        return $this;
    }

    /**
     * Get message part encoding
     *
     * @return string
     */
    public function getEncoding()
    {
        return $this->encoding;
    }

    /**
     * Set message part encoding
     *
     * @param  string $encoding
     * @return AbstractPart
     */
    public function setEncoding($encoding)
    {
        $this->encoding = $encoding;
        return $this;
    }

    /**
     * Check if encoding in base-64
     *
     * @return string
     */
    public function isBase64()
    {
        return ($this->encoding == self::BASE64);
    }

    /**
     * Get message body
     *
     * @return string
     */
    public function getBody()
    {
        return $this->getContent();
    }

    /**
     * Render message
     *
     * @param  array $omitHeaders
     * @return string
     */
    public function render(array $omitHeaders = [])
    {
        return $this->getHeadersAsString($omitHeaders) . Message::CRLF . $this->getBody() . Message::CRLF . Message::CRLF;
    }

    /**
     * Render as an array of lines
     *
     * @param  array $omitHeaders
     * @return array
     */
    public function renderAsLines(array $omitHeaders = [])
    {
        $lines = [];

        $headers = explode(Message::CRLF, $this->getHeadersAsString($omitHeaders) . Message::CRLF);
        $body    = explode("\n", $this->getContent());

        foreach ($headers as $header) {
            $lines[] = trim($header);
        }

        foreach ($body as $line) {
            $lines[] = trim($line);
        }

        $lines[] = Message::CRLF;
        $lines[] = Message::CRLF;

        return $lines;
    }

}
