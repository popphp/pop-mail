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
        unset($partObject['foo']);
        $this->assertFalse(isset($partObject['foo']));

    }


}