<?php

namespace Pop\Mail\Test;

use Pop\Mail\Message;
use PHPUnit\Framework\TestCase;

class MessageCoreTest extends TestCase
{

    public function testMessageExtendsPopMimeMessage()
    {
        $message = new Message();
        $this->assertInstanceOf(\Pop\Mime\Message::class, $message);
    }

    public function testGetHeadersReturnsFlatStringMap()
    {
        $message = new Message();
        $message->addHeader('X-Test', 'Value');
        $headers = $message->getHeaders();
        $this->assertIsString($headers['X-Test']);
        $this->assertEquals('Value', $headers['X-Test']);
    }

    public function testGetHeaderValueReturnsString()
    {
        $message = new Message();
        $message->addHeader('X-Test', 'Value');
        $this->assertEquals('Value', $message->getHeaderValue('X-Test'));
        $this->assertNull($message->getHeaderValue('X-Missing'));
    }

    public function testGetHeaderAsString()
    {
        $message = new Message();
        $message->addHeader('X-Test', 'Value');
        $this->assertEquals('X-Test: Value', $message->getHeaderAsString('X-Test'));
        $this->assertNull($message->getHeaderAsString('X-Missing'));
    }

    public function testGetHeadersAsStringOmitsListedHeaders()
    {
        $message = new Message();
        $message->addHeader('Subject', 'Hello');
        $message->addHeader('X-Test', 'Value');
        $result = $message->getHeadersAsString(['Subject']);
        $this->assertStringNotContainsString('Subject', $result);
        $this->assertStringContainsString('X-Test: Value', $result);
    }

    public function testGetHeadersAsStringAppendsCharset()
    {
        $message = new Message();
        $message->setContentType('text/plain');
        $message->setCharSet('utf-8');
        $result = $message->getHeadersAsString();
        $this->assertStringContainsString('text/plain; charset="utf-8"', $result);
    }

    public function testGetBoundaryAutoGeneratesAndSetsMimeVersion()
    {
        $message = new Message();
        $this->assertFalse($message->hasHeader('MIME-Version'));
        $boundary = $message->getBoundary();
        $this->assertIsString($boundary);
        $this->assertNotEmpty($boundary);
        $this->assertTrue($message->hasHeader('MIME-Version'));
        $this->assertEquals('1.0', $message->getHeaderValue('MIME-Version'));
    }

    public function testGetBoundaryIsStableAcrossCalls()
    {
        $message = new Message();
        $first   = $message->getBoundary();
        $second  = $message->getBoundary();
        $this->assertEquals($first, $second);
    }

    public function testGenerateIdSetsMessageIdHeaderAsSideEffect()
    {
        $message = new Message();
        $id      = $message->generateId('testdomain.com');
        $this->assertStringContainsString('@testdomain.com>', $id);
        $this->assertEquals($id, $message->getHeaderValue('Message-ID'));
    }

    public function testGetPartReturnsNullForMissingIndex()
    {
        $message = new Message();
        $this->assertNull($message->getPart(0));
    }

}
