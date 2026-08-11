<?php

namespace Pop\Mail\Test;

use Pop\Mail\Message;
use Pop\Mail\Message\Text;
use Pop\Mail\Message\Html;
use Pop\Mail\Message\Attachment;
use PHPUnit\Framework\TestCase;

class MessageParseTest extends TestCase
{

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
        $file->addFile(__DIR__ . '/../tmp/test.pdf');

        $message->addParts([$html, $text, $file]);

        $mailMessage = Message::parse($message->render());

        $this->assertInstanceOf(Message::class, $mailMessage);
        $this->assertEquals(3, count($mailMessage->getParts()));
        $this->assertInstanceOf(Html::class, $mailMessage->getParts()[0]);
        $this->assertInstanceOf(Text::class, $mailMessage->getParts()[1]);
        $this->assertInstanceOf(Attachment::class, $mailMessage->getParts()[2]);
    }

    public function testDecodeTextWithoutExtImap()
    {
        $str = "=?ISO-8859-1?Q?John_D=F8e?= <john@doe.com>";
        $this->assertStringContainsString('<john@doe.com>', Message::decodeText($str));
        $this->assertStringContainsString('John D', Message::decodeText($str));
    }

}
