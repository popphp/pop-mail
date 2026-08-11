<?php

namespace Pop\Mail\Test\Message;

use Pop\Mail\Message\Text;
use Pop\Mime\Part;
use PHPUnit\Framework\TestCase;

class TextTest extends TestCase
{

    public function testCreateReturnsTextInstance()
    {
        $text = Text::create('Hello World');
        $this->assertInstanceOf(Text::class, $text);
        $this->assertInstanceOf(Part::class, $text);
    }

    public function testCreateSetsContentType()
    {
        $text = Text::create('Hello World');
        $this->assertEquals('text/plain', (string)$text->getHeader('Content-Type')->getValue(0));
    }

    public function testGetContent()
    {
        $text = Text::create('Hello World');
        $this->assertEquals('Hello World', $text->getContent());
    }

}
