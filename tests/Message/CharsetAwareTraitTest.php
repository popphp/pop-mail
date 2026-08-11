<?php

namespace Pop\Mail\Test\Message;

use Pop\Mime\Part;
use Pop\Mail\Message\CharsetAwareTrait;
use PHPUnit\Framework\TestCase;

class CharsetAwareTraitFixture extends Part
{
    use CharsetAwareTrait;
}

class CharsetAwareTraitTest extends TestCase
{

    public function testSetContentTypeAddsHeader()
    {
        $fixture = new CharsetAwareTraitFixture();
        $fixture->setContentType('text/plain');
        $this->assertTrue($fixture->hasHeader('Content-Type'));
        $this->assertEquals('text/plain', (string)$fixture->getHeader('Content-Type')->getValue(0));
    }

    public function testCharSetRoundTrip()
    {
        $fixture = new CharsetAwareTraitFixture();
        $this->assertNull($fixture->getCharSet());
        $fixture->setCharSet('utf-8');
        $this->assertEquals('utf-8', $fixture->getCharSet());
        $fixture->setCharSet(null);
        $this->assertNull($fixture->getCharSet());
    }

}
