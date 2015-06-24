<?php

namespace Pop\Mail\Test;

use Pop\Mail\Mail;

class MailTest extends \PHPUnit_Framework_TestCase
{

    public function testConstructor()
    {
        $mail = new Mail('Hello World', ['email' => 'test@test.com']);
        $mail->to('another@test.com', 'Another Person');
        $mail->setParams('-fsomebody@test.com');
        $mail->setParams(['-t', '-i']);
        $mail->setParams(null);
        $mail->sendAsGroup(true);
        $this->assertEquals(2, count($mail->getQueue()));
        $this->assertInstanceOf('Pop\Mail\Mail', $mail);
        $this->assertInstanceOf('Pop\Mail\Queue', $mail->getQueue());
        $this->assertInstanceOf('Pop\Mail\Message', $mail->getMessage());
        $this->assertEquals('Hello World', $mail->getSubject());
    }

    public function testSetHeaders()
    {
        $mail = new Mail('Hello World', ['email' => 'test@test.com']);
        $mail->setHeader('Reply-To', 'noreply@localhost');
        $mail->setHeaders([
            'CC'  => 'cc@localhost',
            'BCC' => 'bcc@localhost'
        ]);
        $this->assertEquals('noreply@localhost', $mail->getHeader('Reply-To'));
        $this->assertEquals(3, count($mail->getHeaders()));
    }

    public function testFrom()
    {
        $mail = new Mail('Hello World', ['email' => 'test@test.com']);
        $mail->from('noreply@localhost', 'No Reply', true);
        $this->assertEquals('No Reply <noreply@localhost>', $mail->getHeader('From'));
        $this->assertEquals('No Reply <noreply@localhost>', $mail->getHeader('Reply-To'));
    }

    public function testReplyTo()
    {
        $mail = new Mail('Hello World', ['email' => 'test@test.com']);
        $mail->replyTo('noreply@localhost', 'No Reply', true);
        $this->assertEquals('No Reply <noreply@localhost>', $mail->getHeader('Reply-To'));
        $this->assertEquals('No Reply <noreply@localhost>', $mail->getHeader('From'));
    }

    public function testCc()
    {
        $mail = new Mail('Hello World', ['email' => 'test@test.com']);
        $mail->cc('cc@localhost', 'CC');
        $this->assertEquals('CC <cc@localhost>', $mail->getHeader('Cc'));
    }

    public function testCcMultiple()
    {
        $mail = new Mail('Hello World', ['email' => 'test@test.com']);
        $mail->cc(['cc1@localhost', 'cc2@localhost']);
        $this->assertEquals('cc1@localhost, cc2@localhost', $mail->getHeader('Cc'));
    }

    public function testBcc()
    {
        $mail = new Mail('Hello World', ['email' => 'test@test.com']);
        $mail->bcc('bcc@localhost', 'BCC');
        $this->assertEquals('BCC <bcc@localhost>', $mail->getHeader('Bcc'));
    }

    public function testBccMultiple()
    {
        $mail = new Mail('Hello World', ['email' => 'test@test.com']);
        $mail->bcc(['bcc1@localhost', 'bcc2@localhost']);
        $this->assertEquals('bcc1@localhost, bcc2@localhost', $mail->getHeader('Bcc'));
    }

    public function testSetBoundary()
    {
        $mail = new Mail('Hello World', ['email' => 'test@test.com']);
        $mail->setBoundary('123456789');
        $this->assertEquals('123456789', $mail->getBoundary());
    }

    public function testSetEol()
    {
        $mail = new Mail('Hello World', ['email' => 'test@test.com']);
        $mail->setEol(Mail::LF);
        $this->assertEquals(Mail::LF, $mail->getEol());
    }

    public function testSetCharset()
    {
        $mail = new Mail('Hello World', ['email' => 'test@test.com']);
        $mail->setCharset('utf-8');
        $this->assertEquals('utf-8', $mail->getCharset());
    }

    public function testSetText()
    {
        $mail = new Mail('Hello World', ['email' => 'test@test.com']);
        $mail->setText('How are you?');
        $this->assertEquals('How are you?', $mail->getText());
    }

    public function testSetHtml()
    {
        $mail = new Mail('Hello World', ['email' => 'test@test.com']);
        $mail->setHtml('<p>How are you?</p>');
        $this->assertEquals('<p>How are you?</p>', $mail->getHtml());
    }

    public function testAttachFile()
    {
        $mail = new Mail('Hello World', ['email' => 'test@test.com']);
        $mail->attachFile(__DIR__ . '/tmp/test.txt');
        $this->assertEquals(1, count($mail->getAttachments()));
    }

    public function testSend()
    {
        $mail = new Mail('Hello World', ['email' => 'nobody@localhost']);
        $mail->to('root@localhost', 'Another Person');
        $mail->attachFile(__DIR__ . '/tmp/test.txt');
        $mail->setText('How are you?');
        $mail->setHtml('<p>How are you?</p>');
        $mail->send();
    }

    public function testSendAsGroup()
    {
        $mail = new Mail('Hello World', ['email' => 'nobody@localhost']);
        $mail->to('root@localhost', 'Another Person');
        $mail->sendAsGroup(true);
        $mail->attachFile(__DIR__ . '/tmp/test.txt');
        $mail->setText('How are you?');
        $mail->setHtml('<p>How are you?</p>');
        $mail->send();
    }

    public function testSaveTo()
    {
        $mail = new Mail('Hello World', ['email' => 'nobody@localhost']);
        $mail->to('root@localhost', 'Another Person');
        $mail->attachFile(__DIR__ . '/tmp/test.txt');
        $mail->setText('How are you?');
        $mail->setHtml('<p>How are you?</p>');
        mkdir(__DIR__ . '/tmp2');
        $mail->saveTo(__DIR__ . '/tmp2');
        $this->assertTrue((count(scandir(__DIR__ . '/tmp2')) > 2));
    }

    public function testSaveToAsGroup()
    {
        $mail = new Mail('Hello World', ['email' => 'nobody@localhost']);
        $mail->to('root@localhost', 'Another Person');
        $mail->sendAsGroup(true);
        $mail->attachFile(__DIR__ . '/tmp/test.txt');
        $mail->setText('How are you?');
        $mail->setHtml('<p>How are you?</p>');
        $mail->saveTo(__DIR__ . '/tmp2');
    }

    public function testSendFrom()
    {
        $mail = new Mail();
        $mail->sendFrom(__DIR__ . '/tmp2', true);
        $this->assertTrue((count(scandir(__DIR__ . '/tmp2')) == 2));
        rmdir(__DIR__ . '/tmp2');
    }

    public function testSendNoMessageBodyException()
    {
        $this->setExpectedException('Pop\Mail\Exception');
        $mail = new Mail('Hello World', ['email' => 'nobody@localhost']);
        $mail->to('root@localhost', 'Another Person');
        $mail->send();
    }

    public function testSendNoRecipientsException()
    {
        $this->setExpectedException('Pop\Mail\Exception');
        $mail = new Mail('Hello World');
        $mail->setText('Hello World');
        $mail->send();
    }

    public function testSendTextOnly()
    {
        $mail = new Mail('Hello World', ['email' => 'nobody@localhost']);
        $mail->setText('How are you?');
        $mail->send();
    }

    public function testSendHtmlOnly()
    {
        $mail = new Mail('Hello World', ['email' => 'nobody@localhost']);
        $mail->setHtml('<p>How are you?</p>');
        $mail->send();
    }

    public function testSendTextAndHtml()
    {
        $mail = new Mail('Hello World', ['email' => 'nobody@localhost']);
        $mail->setText('How are you?');
        $mail->setHtml('<p>How are you?</p>');
        $mail->send();
    }

    public function testSendTextAndFile()
    {
        $mail = new Mail('Hello World', ['email' => 'nobody@localhost']);
        $mail->setText('How are you?');
        $mail->attachFile(__DIR__ . '/tmp/test.txt');
        $mail->send();
    }

    public function testSendHtmlAndFile()
    {
        $mail = new Mail('Hello World', ['email' => 'nobody@localhost']);
        $mail->setHtml('<p>How are you?</p>');
        $mail->attachFile(__DIR__ . '/tmp/test.txt');
        $mail->send();
    }


}
