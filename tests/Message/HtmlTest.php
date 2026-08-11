<?php

namespace Pop\Mail\Test\Message;

use Pop\Mail\Message\Html;
use Pop\Mime\Part;
use PHPUnit\Framework\TestCase;

class HtmlTest extends TestCase
{

    public function testCreateReturnsHtmlInstance()
    {
        $html = Html::create('<h1>Hello</h1>');
        $this->assertInstanceOf(Html::class, $html);
        $this->assertInstanceOf(Part::class, $html);
    }

    public function testCreateSetsContentType()
    {
        $html = Html::create('<h1>Hello</h1>');
        $this->assertEquals('text/html', (string)$html->getHeader('Content-Type')->getValue(0));
    }

    public function testGetContent()
    {
        $html = Html::create('<h1>Hello</h1>');
        $this->assertEquals('<h1>Hello</h1>', $html->getContent());
    }

}
