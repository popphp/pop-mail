<?php

namespace Pop\Mail\Test\Transport;

use Pop\Mail;
use Pop\Mail\Transport;
use PHPUnit\Framework\TestCase;

class SendmailTest extends TestCase
{

    public function testSendmail()
    {
        $transport = new Transport\Sendmail('-f');
        $this->assertInstanceOf('Pop\Mail\Transport\Sendmail', $transport);
        $this->assertEquals('-f', $transport->getParams());
    }

}