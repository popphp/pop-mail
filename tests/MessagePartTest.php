<?php

namespace Pop\Mail\Test;

use Pop\Mail\Message;
use PHPUnit\Framework\TestCase;

class MessagePartTest extends TestCase
{

    public function testQuotedPrintable()
    {
        $part = new Message\Simple('Hello World', 'text/plain', Message\Simple::QUOTED_PRINTABLE);
        $this->assertEquals(Message\Simple::QUOTED_PRINTABLE, $part->getEncoding());
        $this->assertFalse($part->isBase64());
    }

    public function testBinary()
    {
        $part = new Message\Simple('Hello World', 'text/plain', Message\Simple::BINARY);
        $this->assertEquals(Message\Simple::BINARY, $part->getEncoding());
    }

    public function test7Bit()
    {
        $part = new Message\Simple('Hello World', 'text/plain', Message\Simple::_7BIT);
        $this->assertEquals(Message\Simple::_7BIT, $part->getEncoding());
    }

    public function test8Bit()
    {
        $part = new Message\Simple('Hello World', 'text/plain', Message\Simple::_8BIT);
        $this->assertEquals(Message\Simple::_8BIT, $part->getEncoding());
    }

    public function testQuotedPrintableAttachment()
    {
        $options = [
            'basename'    => 'test.txt',
            'contentType' => 'text/plain',
            'encoding'    => Message\Attachment::QUOTED_PRINTABLE
        ];
        $part = new Message\Attachment(null, 'Hello World', $options);
        $this->assertEquals(Message\Attachment::QUOTED_PRINTABLE, $part->getEncoding());
    }

    public function testBinaryAttachment()
    {
        $options = [
            'basename'    => 'test.txt',
            'contentType' => 'text/plain',
            'encoding'    => Message\Attachment::BINARY
        ];
        $part = new Message\Attachment(null, 'Hello World', $options);
        $this->assertEquals(Message\Attachment::BINARY, $part->getEncoding());
    }

    public function test7BitAttachment()
    {
        $options = [
            'basename'    => 'test.txt',
            'contentType' => 'text/plain',
            'encoding'    => Message\Attachment::_7BIT
        ];
        $part = new Message\Attachment(null, 'Hello World', $options);
        $this->assertEquals(Message\Attachment::_7BIT, $part->getEncoding());
    }

    public function test8BitAttachment()
    {
        $options = [
            'basename'    => 'test.txt',
            'contentType' => 'text/plain',
            'encoding'    => Message\Attachment::_8BIT
        ];
        $part = new Message\Attachment(null, 'Hello World', $options);
        $this->assertEquals(Message\Attachment::_8BIT, $part->getEncoding());
    }

    public function testFileException()
    {
        $this->expectException('Pop\Mail\Message\Exception');
        $part = new Message\Attachment('bad-file.txt');
    }

    public function testFileGetStream()
    {
        $part = new Message\Attachment(__DIR__ . '/tmp/test.txt');
        $this->assertContains('Hello World', $part->getStream());

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

}