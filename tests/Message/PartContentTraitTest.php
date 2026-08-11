<?php

namespace Pop\Mail\Test\Message;

use Pop\Mime\Part;
use Pop\Mail\Message\PartContentTrait;
use PHPUnit\Framework\TestCase;

class PartContentTraitFixture extends Part
{
    use PartContentTrait;
}

class PartContentTraitTest extends TestCase
{

    public function testGetContentReturnsNullWithNoBody()
    {
        $fixture = new PartContentTraitFixture();
        $this->assertNull($fixture->getContent());
    }

    public function testSetAndGetContent()
    {
        $fixture = new PartContentTraitFixture();
        $fixture->setContent('Hello World');
        $this->assertEquals('Hello World', $fixture->getContent());
    }

    public function testRenderAsLines()
    {
        $fixture = new PartContentTraitFixture();
        $fixture->addHeader('Content-Type', 'text/plain');
        $fixture->setContent('Hello World');
        $lines = $fixture->renderAsLines();
        $this->assertIsArray($lines);
        $this->assertContains('Content-Type: text/plain', $lines);
        $this->assertContains('Hello World', $lines);
    }

}
