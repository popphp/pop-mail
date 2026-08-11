<?php

namespace Pop\Mail\Test\Message;

use Pop\Mail\Message\Attachment;
use Pop\Mime\Part;
use PHPUnit\Framework\TestCase;

class AttachmentTest extends TestCase
{

    public function testCreateReturnsAttachmentInstance()
    {
        $attachment = Attachment::create(__DIR__ . '/../tmp/test.txt');
        $this->assertInstanceOf(Attachment::class, $attachment);
        $this->assertInstanceOf(Part::class, $attachment);
    }

    public function testGetFilenameReturnsSourcePath()
    {
        $path       = __DIR__ . '/../tmp/test.txt';
        $attachment = Attachment::create($path);
        $this->assertEquals($path, $attachment->getFilename());
    }

    public function testGetBasenameReturnsBasename()
    {
        $attachment = Attachment::create(__DIR__ . '/../tmp/test.txt');
        $this->assertEquals('test.txt', $attachment->getBasename());
    }

    public function testCreateSetsContentDescriptionHeader()
    {
        $attachment = Attachment::create(__DIR__ . '/../tmp/test.txt');
        $this->assertTrue($attachment->hasHeader('Content-Description'));
        $this->assertEquals('test.txt', (string)$attachment->getHeader('Content-Description')->getValue(0));
    }

    public function testCreateSetsContentDispositionHeader()
    {
        $attachment = Attachment::create(__DIR__ . '/../tmp/test.txt');
        $this->assertTrue($attachment->hasHeader('Content-Disposition'));
    }

    public function testCreateDetectsContentTypeFromExtension()
    {
        $attachment = Attachment::create(__DIR__ . '/../tmp/test.txt');
        $this->assertEquals('text/plain', (string)$attachment->getHeader('Content-Type')->getValue(0));
    }

    public function testCreateFromContentWorksWithoutRealFile()
    {
        $attachment = Attachment::createFromContent('raw content', 'generated.txt');
        $this->assertInstanceOf(Attachment::class, $attachment);
        $this->assertEquals('generated.txt', $attachment->getBasename());
        $this->assertEquals('raw content', $attachment->getContent());
    }

    public function testCreateFromContentHasNoSourcePath()
    {
        $attachment = Attachment::createFromContent('raw content', 'generated.txt');
        $this->assertNull($attachment->getFilename());
    }

    public function testCreateThrowsOnMissingFile()
    {
        $this->expectException(\Pop\Mail\Message\Exception::class);
        Attachment::create('bad-file.txt');
    }

}
