<?php

namespace Pop\Mail\Test;

use Pop\Mail\Attachment;

class AttachmentTest extends \PHPUnit_Framework_TestCase
{

    public function testConstructor()
    {
        $attachment = new Attachment(__DIR__ . '/tmp/test.txt');
        $this->assertInstanceOf('Pop\Mail\Attachment', $attachment);
    }

    public function testConstructorException()
    {
        $this->setExpectedException('Pop\Mail\Exception');
        $attachment = new Attachment(__DIR__ . '/tmp/bad.txt');
    }

}
