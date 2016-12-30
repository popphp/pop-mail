<?php

namespace Pop\Mail\Test;

use Pop\Mail\Message;

class MessageTest extends \PHPUnit_Framework_TestCase
{

    public function testConstructor()
    {
        $message = new Message('Hello World');
        $message->setContentType('text/html');
        $message->setCharSet('iso-8859-1');
        $this->assertInstanceOf('Pop\Mail\Message', $message);
        $this->assertEquals('text/html', $message->getContentType());
        $this->assertEquals('iso-8859-1', $message->getCharSet());
    }

    public function testLoadFromFile()
    {
        $message = Message::load(__DIR__ . '/tmp/test.msg');
        $this->assertInstanceOf('Pop\Mail\Message', $message);
        $this->assertEquals('Hello World', $message->getSubject());
    }

    public function testLoadFromString()
    {
        $message = Message::load(file_get_contents(__DIR__ . '/tmp/test.msg'));
        $this->assertInstanceOf('Pop\Mail\Message', $message);
        $this->assertEquals('Hello World', $message->getSubject());
    }

    public function testLoadException()
    {
        $this->expectException('Pop\Mail\Exception');
        $message = Message::load('----');
    }

    public function testSetAndGetTo()
    {
        $message = new Message('Hello World');
        $message->setTo('test@domain.com');
        $this->assertEquals(['test@domain.com' => null], $message->getTo());
    }

    public function testSetAndGetCc()
    {
        $message = new Message('Hello World');
        $message->setCc('test@domain.com');
        $this->assertEquals(['test@domain.com' => null], $message->getCc());
    }

    public function testSetAndGetBcc()
    {
        $message = new Message('Hello World');
        $message->setBcc('test@domain.com');
        $this->assertEquals(['test@domain.com' => null], $message->getBcc());
    }

    public function testSetAndGetFrom()
    {
        $message = new Message('Hello World');
        $message->setFrom('test@domain.com');
        $this->assertEquals(['test@domain.com' => null], $message->getFrom());
    }

    public function testSetAndGetReplyTo()
    {
        $message = new Message('Hello World');
        $message->setReplyTo('test@domain.com');
        $this->assertEquals(['test@domain.com' => null], $message->getReplyTo());
    }

    public function testSetAndGetSender()
    {
        $message = new Message('Hello World');
        $message->setSender('test@domain.com');
        $this->assertEquals(['test@domain.com' => null], $message->getSender());
    }

    public function testSetAndGetReturnPath()
    {
        $message = new Message('Hello World');
        $message->setReturnPath('test@domain.com');
        $this->assertEquals(['test@domain.com' => null], $message->getReturnPath());
    }

    public function testAddText()
    {
        $message = new Message('Hello World');
        $message->addText('Hello World');
        $this->assertEquals('Hello World', $message->getPart(0)->getContent());
    }

    public function testAddHtml()
    {
        $message = new Message('Hello World');
        $message->addHtml('<h1>Hello World</h1>');
        $this->assertEquals('<h1>Hello World</h1>', $message->getPart(0)->getContent());
    }

    public function testAttachFile()
    {
        $message = new Message('Hello World');
        $message->attachFile(__DIR__ . '/tmp/test.txt');
        $this->assertEquals('test.txt', $message->getPart(0)->getBasename());
    }

    public function testGetBodyText()
    {
        $message = new Message('Hello World');
        $message->addText('Hello World');
        $this->assertContains('Hello World', $message->getBody());
    }

    public function testGetBodyHtml()
    {
        $message = new Message('Hello World');
        $message->addHtml('<h1>Hello World</h1>');
        $this->assertContains('<h1>Hello World</h1>', $message->getBody());
    }

    public function testGetMultipartBody()
    {
        $message = new Message('Hello World');
        $message->addText('Hello World');
        $message->addHtml('<h1>Hello World</h1>');
        $this->assertContains('Hello World', $message->getBody());
        $this->assertContains('<h1>Hello World</h1>', $message->getBody());
    }

    public function testAddHeaders()
    {
        $message = new Message('Hello World');
        $message->addHeaders(['X-Test-Header' => 'Test']);
        $this->assertTrue($message->hasHeader('X-Test-Header'));
        $this->assertEquals(2, count($message->getHeaders()));
        $this->assertEquals('X-Test-Header: Test', $message->getHeaderAsString('X-Test-Header'));
    }

    public function testRemoveHeader()
    {
        $message = new Message('Hello World');
        $message->addHeader('X-Test-Header', 'Test');
        $this->assertEquals('Test', $message->getHeader('X-Test-Header'));
        $message->removeHeader('X-Test-Header');
        $this->assertNull($message->getHeader('X-Test-Header'));
    }

    public function testMessageId()
    {
        $message = new Message('Hello World');
        $message->setId('abcdef');
        $message->setIdHeader('Message-ID');
        $this->assertEquals('abcdef', $message->getId());
        $this->assertEquals('Message-ID', $message->getIdHeader());
    }

    public function testRenderPartAsLines()
    {
        $message = new Message('Hello World');
        $message->addText('Hello World');
        $this->assertTrue(is_array($message->getPart(0)->renderAsLines()));
    }

    public function testRender()
    {
        $message = new Message('Hello World');
        $message->addText('Hello World');
        $this->assertContains('Subject: Hello World', $message->render());
        $this->assertContains('Hello World', $message->render());
    }

    public function testRenderAsLines()
    {
        $message = new Message('Hello World');
        $message->addText('Hello World');
        $this->assertTrue(is_array($message->renderAsLines()));
    }

    public function testSave()
    {
        $message = new Message('Hello World');
        $message->addText('Hello World');
        $message->save(__DIR__ . '/tmp/save.msg');
        $this->assertFileExists(__DIR__ . '/tmp/save.msg');
        $this->assertContains('Subject: Hello World', file_get_contents(__DIR__ . '/tmp/save.msg'));
        $this->assertContains('Hello World', file_get_contents(__DIR__ . '/tmp/save.msg'));

        if (file_exists(__DIR__ . '/tmp/save.msg')) {
            unlink(__DIR__ . '/tmp/save.msg');
        }
    }

}