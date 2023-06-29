<?php

namespace Pop\Mail\Test;

use Pop\Mail\Mailer;
use Pop\Mail\Transport;
use PHPUnit\Framework\TestCase;

class MailerTest extends TestCase
{

    public function testConstructor()
    {
        $mailer = new Mailer(new Transport\Sendmail());
        $this->assertInstanceOf('Pop\Mail\Mailer', $mailer);
        $this->assertInstanceOf('Pop\Mail\Transport\Sendmail', $mailer->transport());
    }

    public function testDefaultFrom()
    {
        $mailer = new Mailer(new Transport\Sendmail(), 'root@localhost');
        $this->assertTrue($mailer->hasDefaultFrom());
        $this->assertEquals('root@localhost', $mailer->getDefaultFrom());

        $mailer->setDefaultFrom('other@localhost');
        $this->assertEquals('other@localhost', $mailer->getDefaultFrom());
    }

}