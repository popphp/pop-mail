<?php

namespace Pop\Mail\Test;

use Pop\Mail\Message;
use Pop\Mail\Message\Text;
use Pop\Mail\Message\Html;
use Pop\Mail\Message\Attachment;
use PHPUnit\Framework\TestCase;

class MessagePartsTest extends TestCase
{

    public function testAddTextCreatesTextPart()
    {
        $message = new Message('Hello World');
        $message->addText('Hello World');
        $this->assertInstanceOf(Text::class, $message->getPart(0));
        $this->assertEquals('Hello World', $message->getPart(0)->getContent());
    }

    public function testAddHtmlCreatesHtmlPart()
    {
        $message = new Message('Hello World');
        $message->addHtml('<h1>Hello World</h1>');
        $this->assertInstanceOf(Html::class, $message->getPart(0));
        $this->assertFalse($message->hasAttachments());
    }

    public function testAttachFileCreatesAttachmentPart()
    {
        $message = new Message('Hello World');
        $message->attachFile(__DIR__ . '/../tmp/test.txt');
        $this->assertInstanceOf(Attachment::class, $message->getPart(0));
        $this->assertTrue($message->hasAttachments());
        $this->assertEquals('test.txt', $message->getPart(0)->getBasename());
    }

    public function testAttachFileFromStreamCreatesAttachmentPart()
    {
        $message = new Message('Hello World');
        $message->attachFileFromStream(file_get_contents(__DIR__ . '/../tmp/test.txt'), 'test1.txt');
        $this->assertInstanceOf(Attachment::class, $message->getPart(0));
        $this->assertEquals('test1.txt', $message->getPart(0)->getBasename());
    }

    public function testSinglePartMessageIsValidMultipartMixed()
    {
        $message = new Message('Hello World');
        $message->addText('Hello World');
        $this->assertEquals('mixed', $message->getSubType());
        $rendered = $message->render();
        $this->assertStringContainsString('Content-Type: multipart/mixed; boundary=', $rendered);
    }

    public function testTextAndHtmlTogetherInferAlternative()
    {
        $message = new Message('Hello World');
        $message->addText('Hello World');
        $message->addHtml('<h1>Hello World</h1>');
        $this->assertEquals('alternative', $message->getSubType());
    }

    public function testFileWinsOverTextAndHtml()
    {
        $message = new Message('Hello World');
        $message->addText('Hello World');
        $message->addHtml('<h1>Hello World</h1>');
        $message->attachFile(__DIR__ . '/../tmp/test.pdf');
        $this->assertEquals('mixed', $message->getSubType());
    }

}
