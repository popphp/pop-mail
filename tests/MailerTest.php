<?php

namespace Pop\Mail\Test;

use Pop\Mail\Mailer;
use Pop\Mail\Transport;

class MailerTest extends \PHPUnit_Framework_TestCase
{

    public function testConstructor()
    {
        $mailer = new Mailer(new Transport\Sendmail());
        $this->assertInstanceOf('Pop\Mail\Mailer', $mailer);
        $this->assertInstanceOf('Pop\Mail\Transport\Sendmail', $mailer->transport());
    }

}