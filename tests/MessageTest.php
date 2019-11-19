<?php

namespace Pop\Mail\Test;

use Pop\Mail\Message;
use PHPUnit\Framework\TestCase;

class MessageTest extends TestCase
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

    public function testParseMultipleParts()
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

        $message->addParts([$html, $text, $file]);

        $mailMessage = Message::parse($message->render());

        $this->assertInstanceOf('Pop\Mail\Message', $mailMessage);
        $this->assertEquals(3, count($mailMessage->getParts()));
        $this->assertInstanceOf('Pop\Mail\Message\Html', $mailMessage->getParts()[0]);
        $this->assertInstanceOf('Pop\Mail\Message\Text', $mailMessage->getParts()[1]);
        $this->assertInstanceOf('Pop\Mail\Message\Attachment', $mailMessage->getParts()[2]);
    }

    public function testLoadExceptionNoSubject()
    {
        $this->expectException('Pop\Mail\Exception');
        $message = Message::load('----');
    }

    public function testLoadExceptionNoTo()
    {
        $msg = "Subject: Hello\r\n\r\nWhat is up?";
        $this->expectException('Pop\Mail\Exception');
        $message = Message::load($msg);
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

    public function testAttachFileFromStream()
    {
        $message = new Message('Hello World');
        $message->attachFileFromStream(file_get_contents(__DIR__ . '/tmp/test.txt'), 'test1.txt');
        $this->assertEquals('test1.txt', $message->getPart(0)->getBasename());
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

    public function testParseNameAndEmail()
    {
        $message = new Message('Hello World');
        $emailAry = $message->parseNameAndEmail('John Doe <john@doe.com>');
        $this->assertTrue(isset($emailAry['name']));
        $this->assertTrue(isset($emailAry['email']));
        $this->assertEquals('John Doe', $emailAry['name']);
        $this->assertEquals('john@doe.com', $emailAry['email']);
    }

    public function testParseAddresses1()
    {
        $address = new \stdClass();
        $address->mailbox = 'test';
        $address->host    = 'test.com';
        $message = new Message();
        $emails  = $message->parseAddresses([$address], true);
        $this->assertTrue(array_key_exists('test@test.com', $emails));
    }

    public function testParseAddresses2()
    {
        $message = new Message();
        $emails  = $message->parseAddresses(['test@test.com' => null], true);
        $this->assertTrue(array_key_exists('test@test.com', $emails));
    }

    public function testParseAddresses3()
    {
        $message = new Message();
        $emails  = $message->parseAddresses(['Test Person' => 'test@test.com'], true);
        $this->assertTrue(array_key_exists('test@test.com', $emails));
        $this->assertEquals('Test Person', $emails['test@test.com']);
    }

    public function testParseAddresses4()
    {
        $message = new Message();
        $emails  = $message->parseAddresses(['' => 'test@test.com'], true);
        $this->assertTrue(array_key_exists('test@test.com', $emails));
    }

    public function testParseAddresses5()
    {
        $message = new Message();
        $emails  = $message->parseAddresses('John Doe <john@doe.com>, Jane Doe <jane@doe.com>', true);
        $this->assertEquals(2, count($emails));
        $this->assertEquals('John Doe', $emails['john@doe.com']);
        $this->assertEquals('Jane Doe', $emails['jane@doe.com']);
    }

    public function testParseStreamNoSubjectException()
    {
        $this->expectException('Pop\Mail\Exception');
        $message = Message::parse('some bad content');
    }

    public function testParseStreamNoToException()
    {
        $this->expectException('Pop\Mail\Exception');
        $message = Message::parse("Subject: This is a subject\nsome other bad content.");
    }

    public function testParseFromFileException()
    {
        $this->expectException('Pop\Mail\Exception');
        $message = Message::parseFromFile('bad-file.msg');
    }

    public function testDecodeText()
    {
        $str = "=?ISO-8859-1?Q?John_D=F8e?= <john@doe.com>";
        $this->assertContains('<john@doe.com>', Message::decodeText($str));
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