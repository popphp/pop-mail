<?php

namespace Pop\Mail\Test;

use Pop\Mail\Message;
use Pop\Mime\Part;
use PHPUnit\Framework\TestCase;

class MessagePartTest extends TestCase
{

    public function testAttachmentQuotedPrintableEncoding()
    {
        $attachment = Message\Attachment::create(__DIR__ . '/tmp/test.txt', null, 'attachment', Part\Body\Encoding::QUOTED_PRINTABLE);
        $this->assertEquals(Part\Body\Encoding::QUOTED_PRINTABLE, $attachment->getBody()->getEncoding());
    }

    public function testAttachmentBinaryEncoding()
    {
        $attachment = Message\Attachment::create(__DIR__ . '/tmp/test.txt', null, 'attachment', Part\Body\Encoding::BINARY);
        $this->assertEquals(Part\Body\Encoding::BINARY, $attachment->getBody()->getEncoding());
    }

    public function testAttachment7BitEncoding()
    {
        $attachment = Message\Attachment::create(__DIR__ . '/tmp/test.txt', null, 'attachment', Part\Body\Encoding::_7BIT);
        $this->assertEquals(Part\Body\Encoding::_7BIT, $attachment->getBody()->getEncoding());
    }

    public function testAttachment8BitEncoding()
    {
        $attachment = Message\Attachment::create(__DIR__ . '/tmp/test.txt', null, 'attachment', Part\Body\Encoding::_8BIT);
        $this->assertEquals(Part\Body\Encoding::_8BIT, $attachment->getBody()->getEncoding());
    }

    public function testAttachmentFileNotFoundThrows()
    {
        $this->expectException(Message\Exception::class);
        Message\Attachment::create('bad-file.txt');
    }

    public function testPartObject()
    {
        $partObject = new Message\Part();
        $partObject['foo'] = 'bar';

        $str = '';

        foreach ($partObject as $object) {
            $str .= $object;
        }

        $this->assertEquals(1, count($partObject));
        $this->assertEquals('bar', $partObject['foo']);
        $this->assertEquals('bar', $str);
        $this->assertTrue(isset($partObject['foo']));
        $this->assertTrue(is_array($partObject->toArray()));
        unset($partObject['foo']);
        $this->assertFalse(isset($partObject['foo']));
    }

    public function testParse()
    {
        $message = new \Pop\Mime\Message();
        $message->addHeaders([
            'Subject'      => 'Hello World',
            'To'           => 'test@test.com',
            'Date'         => date('m/d/Y g:i A'),
            'MIME-Version' => '1.0'
        ]);

        $message->setSubType('mixed');

        $html = new \Pop\Mime\Part();
        $html->addHeader('Content-Type', 'text/html');
        $html->setBody('<html><body><h1>This is the text message.</h1></body></html>');

        $text = new \Pop\Mime\Part();
        $text->addHeader('Content-Type', 'text/plain');
        $text->setBody('This is the text message.');

        $file = new \Pop\Mime\Part();
        $file->addHeader('Content-Type', 'application/octet-stream');
        $file->addFile(__DIR__ . '/tmp/test.pdf');

        $part = new \Pop\Mime\Part();
        $part->addParts([$html, $text]);

        $message->addParts([$part, $file]);
        $messageString = $message->render();
        $bodyString = substr($messageString, (strpos($messageString, "This is a multi-part message in MIME format.\r\n") + 46));

        $parts = Message\Part::parse($bodyString);
        $this->assertEquals(6, count($parts));
    }

    public function testParseParts()
    {
        $html = new \Pop\Mime\Part();
        $html->addHeader('Content-Type', 'text/html');
        $html->setBody('<html><body><h1>This is the text message.</h1></body></html>');

        $text = new \Pop\Mime\Part();
        $text->addHeader('Content-Type', 'text/plain');
        $text->setBody('This is the text message.');

        $file = new \Pop\Mime\Part();
        $file->addHeader('Content-Type', 'application/octet-stream');
        $file->addFile(__DIR__ . '/tmp/test.pdf');

        $parts = Message\Part::parseParts([[$html, $text, $file]]);
        $this->assertEquals(3, count($parts));
    }

}