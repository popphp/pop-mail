<?php

namespace Pop\Mail\Test;

use Pop\Mail\Transport;

class TransportTest extends \PHPUnit_Framework_TestCase
{

    public function testSendmail()
    {
        $transport = new Transport\Sendmail('-f');
        $this->assertInstanceOf('Pop\Mail\Transport\Sendmail', $transport);
        $this->assertEquals('-f', $transport->getParams());
    }

    public function testSmtp()
    {
        $transport = new Transport\Smtp('localhost', 25, 'tls');
        $this->assertInstanceOf('Pop\Mail\Transport\Smtp', $transport);
        $this->assertEquals('localhost', $transport->getHost());
        $this->assertEquals(25, $transport->getPort());
    }

}