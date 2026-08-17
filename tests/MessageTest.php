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
        $this->assertTrue($message->hasTo());
        $this->assertEquals(['test@domain.com' => null], $message->getTo());
    }

    public function testSetAndGetCc()
    {
        $message = new Message('Hello World');
        $message->setCc('test@domain.com');
        $this->assertTrue($message->hasCc());
        $this->assertEquals(['test@domain.com' => null], $message->getCc());
    }

    public function testSetAndGetBcc()
    {
        $message = new Message('Hello World');
        $message->setBcc('test@domain.com');
        $this->assertTrue($message->hasBcc());
        $this->assertEquals(['test@domain.com' => null], $message->getBcc());
    }

    public function testSetAndGetFrom()
    {
        $message = new Message('Hello World');
        $message->setFrom('test@domain.com');
        $this->assertTrue($message->hasFrom());
        $this->assertEquals(['test@domain.com' => null], $message->getFrom());
    }

    public function testSetAndGetReplyTo()
    {
        $message = new Message('Hello World');
        $message->setReplyTo('test@domain.com');
        $this->assertTrue($message->hasReplyTo());
        $this->assertEquals(['test@domain.com' => null], $message->getReplyTo());
    }

    public function testSetAndGetSender()
    {
        $message = new Message('Hello World');
        $message->setSender('test@domain.com');
        $this->assertTrue($message->hasSender());
        $this->assertEquals(['test@domain.com' => null], $message->getSender());
    }

    public function testSetAndGetReturnPath()
    {
        $message = new Message('Hello World');
        $message->setReturnPath('test@domain.com');
        $this->assertTrue($message->hasReturnPath());
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
        $this->assertFalse($message->hasAttachments());
        $this->assertEquals('<h1>Hello World</h1>', $message->getPart(0)->getContent());
    }

    public function testAttachFile()
    {
        $message = new Message('Hello World');
        $message->attachFile(__DIR__ . '/tmp/test.txt');
        $this->assertTrue($message->hasAttachments());
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
        $this->assertStringContainsString('Hello World', $message->getBodyContent());
    }

    public function testGetBodyHtml()
    {
        $message = new Message('Hello World');
        $message->addHtml('<h1>Hello World</h1>');
        $this->assertStringContainsString('<h1>Hello World</h1>', $message->getBodyContent());
    }

    public function testGetMultipartBody()
    {
        $message = new Message('Hello World');
        $message->addText('Hello World');
        $message->addHtml('<h1>Hello World</h1>');
        $this->assertStringContainsString('Hello World', $message->getBodyContent());
        $this->assertStringContainsString('<h1>Hello World</h1>', $message->getBodyContent());
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
        $this->assertEquals('Test', $message->getHeaderValue('X-Test-Header'));
        $message->removeHeader('X-Test-Header');
        $this->assertNull($message->getHeaderValue('X-Test-Header'));
    }

    public function testMessageId()
    {
        $message = new Message('Hello World');
        $message->setMessageId('<abcdef@example.com>');
        $this->assertEquals('<abcdef@example.com>', $message->getHeaderValue('Message-ID'));
    }

    public function testParseStreamMalformedException()
    {
        $this->expectException('Pop\Mail\Exception');
        $message = Message::parse('some bad content');
    }

    public function testParseStreamNoSubjectException()
    {
        $this->expectException('Pop\Mail\Exception');
        $message = Message::parse("To: to@test.com\r\n\r\nHello World");
    }

    public function testParseStreamNoToException()
    {
        $this->expectException('Pop\Mail\Exception');
        $message = Message::parse("Subject: This is a subject\r\n\r\nHello World");
    }

    public function testParseFromFileException()
    {
        $this->expectException('Pop\Mail\Exception');
        $message = Message::parseFromFile('bad-file.msg');
    }

    public function testDecodeText()
    {
        $str = "=?ISO-8859-1?Q?John_D=F8e?= <john@doe.com>";
        $this->assertStringContainsString('<john@doe.com>', Message::decodeText($str));
    }

    public function testGenerateId()
    {
        $message = new Message();
        $this->assertTrue(str_contains($message->generateId('testdomain.com'), '@testdomain.com>'));
        $this->assertTrue(str_contains($message->generateId(), '@localhost>'));
    }

    public function testGetHeadersAsString()
    {
        $message = new Message();
        $message->setContentType('text/plain');
        $message->setCharSet('utf-8');
        $message->addHeader('X-Test', 'Test-Value');
        $this->assertTrue(str_contains($message->generateId('testdomain.com'), '@testdomain.com>'));
        $headers = $message->getHeadersAsString();
        $this->assertTrue(str_contains($headers, '@testdomain.com>'));
        $this->assertTrue(str_contains($headers, 'text/plain; charset="utf-8"'));
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
        $this->assertStringContainsString('Subject: Hello World', $message->render());
        $this->assertStringContainsString('Hello World', $message->render());
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
        $this->assertStringContainsString('Subject: Hello World', file_get_contents(__DIR__ . '/tmp/save.msg'));
        $this->assertStringContainsString('Hello World', file_get_contents(__DIR__ . '/tmp/save.msg'));

        if (file_exists(__DIR__ . '/tmp/save.msg')) {
            unlink(__DIR__ . '/tmp/save.msg');
        }
    }

    /**
     * Regression for final-review Critical #1: Sendmail::send() called the
     * inherited Part::getBody() (returns Part\Body, non-nullable) instead of
     * getBodyContent() (returns ?string), fataling with a TypeError on every
     * real send. This exercises the Message-level call directly; the full
     * Sendmail::send() path (including the real mail() invocation, via the
     * namespaced mail() override) is covered in tests/Transport/SendmailTest.php.
     */
    public function testGetBodyContentDoesNotThrowOnMultiPartMessage()
    {
        $message = new Message('Hello World');
        $message->setTo('test@domain.com');
        $message->addText('Plain text part');
        $message->addHtml('<p>HTML part</p>');

        $body = $message->getBodyContent();

        $this->assertIsString($body);
        $this->assertStringContainsString('Plain text part', $body);
        $this->assertStringContainsString('<p>HTML part</p>', $body);
    }

    /**
     * Regression for final-review Critical: Sendmail::send() computed
     * getHeadersAsString() BEFORE getBodyContent() - the same evaluation-
     * ordering hazard fixed in Message::render() (see
     * testRenderMultiPartMessageContainsMimeVersion() above). Content-Type
     * is synthesized by Part::renderParts() (invoked via getBodyContent())
     * and MIME-Version is emitted as a side effect of getBoundary(), but
     * ONLY if those are triggered before the headers are stringified. This
     * exercises that same getBoundary()-before-headers sequence directly;
     * Sendmail::send()'s real ordering (and its real mail() invocation) is
     * covered in tests/Transport/SendmailTest.php.
     */
    public function testSendmailHeaderOrderingIncludesContentTypeAndMimeVersion()
    {
        $message = new Message('Hello World');
        $message->setTo('test@domain.com');
        $message->addText('Plain text part');
        $message->addHtml('<p>HTML part</p>');

        if ($message->hasParts()) {
            $message->getBoundary();
        }

        $body    = $message->getBodyContent();
        $headers = $message->getHeadersAsString(['Subject', 'To']);

        $this->assertIsString($body);
        $this->assertStringContainsString('Content-Type: multipart/', $headers);
        $this->assertStringContainsString('MIME-Version: 1.0', $headers);
    }

    /**
     * Regression for final-review Critical #2: Mailgun/Sendgrid/Office365/Ses
     * transports called the inherited Part::getBody() (returns a Part\Body
     * object) on individual Text/Html/Attachment parts, expecting a string -
     * silently serializing the object to "{}" in JSON payloads instead of the
     * real content. getContent() (from PartContentTrait) is the correct call.
     */
    public function testPartGetContentReturnsString()
    {
        $text = \Pop\Mail\Message\Text::create('Plain text content');
        $html = \Pop\Mail\Message\Html::create('<p>HTML content</p>');

        $this->assertIsString($text->getContent());
        $this->assertEquals('Plain text content', $text->getContent());
        $this->assertIsString($html->getContent());
        $this->assertEquals('<p>HTML content</p>', $html->getContent());
    }

    /**
     * Regression for final-review Critical #3 (and Important #7, same root
     * cause/fix): Message::parse() used to copy the source message's
     * Content-Type header (with its stale boundary parameter) verbatim onto
     * the new Message before parts were added. Part::renderParts() would then
     * see a Content-Type already present and skip regenerating it, while still
     * generating a NEW boundary for the actual body separators - producing a
     * declared boundary that didn't match the boundary actually used in the
     * body. addPart() now strips any pre-existing Content-Type once parts
     * exist, so renderParts() always regenerates a self-consistent header.
     * This round-trips a message through parse()+render() twice and confirms
     * the declared boundary matches the boundary markers in its own body.
     */
    public function testParseAndRenderProducesConsistentBoundary()
    {
        $message = new Message('Round Trip Test');
        $message->setTo('test@domain.com');
        $message->addText('Plain text body');
        $message->addHtml('<p>HTML body</p>');

        $rendered = $message->render();
        $parsed   = Message::parse($rendered);
        $reRendered = $parsed->render();

        $this->assertMatchesRegularExpression(
            '/Content-Type:\s*multipart\/\S+;\s*boundary="?([^";\r\n]+)"?/i',
            $reRendered
        );
        preg_match('/Content-Type:\s*multipart\/\S+;\s*boundary="?([^";\r\n]+)"?/i', $reRendered, $matches);
        $declaredBoundary = $matches[1];

        $this->assertStringContainsString('--' . $declaredBoundary, $reRendered);
        $this->assertStringContainsString('--' . $declaredBoundary . '--', $reRendered);
    }

    /**
     * Regression for final-review Important #4/#5: MIME-Version and charset
     * never reached real rendered output because render() bypassed the
     * compat methods (getBoundary()'s side effect, getHeadersAsString()'s
     * charset-append logic) that carried them. render() now explicitly
     * routes through both.
     */
    public function testRenderMultiPartMessageContainsMimeVersion()
    {
        $message = new Message('Hello World');
        $message->setTo('test@domain.com');
        $message->addText('Plain text part');
        $message->addHtml('<p>HTML part</p>');

        $this->assertStringContainsString('MIME-Version: 1.0', $message->render());
    }

    /**
     * Verified real behavior (see testGetHeadersAsString() above, which
     * already covers charset-appending on a Content-Type set via
     * setContentType()+setCharSet() with no parts present). Confirms
     * render() itself - not just getHeadersAsString() - carries the charset
     * through on a parts-free message, since addPart()'s Content-Type strip
     * (fix #3/#7) only applies once at least one part exists.
     */
    public function testRenderNoPartsMessageContainsCharSet()
    {
        $message = new Message('Hello World');
        $message->setTo('test@domain.com');
        $message->setContentType('text/html');
        $message->setCharSet('iso-8859-1');

        $rendered = $message->render();
        $this->assertStringContainsString('text/html; charset="iso-8859-1"', $rendered);
    }

    /**
     * Regression for final-review Important #6: AbstractSmtp calls
     * setBcc([]) on every send to clear Bcc before the main send (Bcc
     * recipients get individual copies). getHeadersAsString() dropped the
     * old !empty($value) guard, so an empty-address-list Bcc still rendered
     * as a blank "BCC: " line, leaking into the wire output. This matches
     * AbstractSmtp's exact usage pattern.
     */
    public function testEmptyBccIsNotRenderedAsBlankHeader()
    {
        $message = new Message('Hello World');
        $message->setTo('test@domain.com');
        $message->setBcc(['bcc@domain.com']);
        $message->setBcc([]);
        $message->addText('Hello World');

        $rendered = $message->render();
        $this->assertStringNotContainsString('BCC:', $rendered);
    }

    /**
     * Regression for final-review Important #8: Message::parse()'s
     * part-dispatch fallback branch (parts that are neither attachments,
     * HTML, nor recognizable text) used to build a bare Pop\Mime\Part and
     * call setBody() on it. Queue::prepare() later calls getContent() on
     * every part, but bare Pop\Mime\Part has no getContent() (only
     * Text/Html/Attachment, via PartContentTrait, do) - fataling with
     * "Call to undefined method". The fallback now uses Text::create() so
     * getContent() is available, while preserving the original Content-Type.
     */
    public function testParseUnrecognizedContentTypeProducesUsablePart()
    {
        $boundary = 'unrecognizedtypeboundary';
        $msg = "Subject: Custom Type Test\r\n"
             . "To: test@test.com\r\n"
             . "MIME-Version: 1.0\r\n"
             . "Content-Type: multipart/mixed; boundary=\"" . $boundary . "\"\r\n"
             . "\r\n"
             . "--" . $boundary . "\r\n"
             . "Content-Type: application/x-custom-unknown\r\n"
             . "\r\n"
             . "custom payload data\r\n"
             . "--" . $boundary . "--\r\n";

        $parsed = Message::parse($msg);
        $part   = $parsed->getPart(0);

        $this->assertEquals('custom payload data', $part->getContent());
        $this->assertEquals('application/x-custom-unknown', $part->getContentType());
    }

}