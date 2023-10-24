<?php

namespace Pop\Mail\Test\Transport;

use Pop\Mail;
use Pop\Mail\Transport;
use PHPUnit\Framework\TestCase;

class SmtpTest extends TestCase
{

    public function testSmtp()
    {
        $transport = new Transport\Smtp('localhost', 25, 'tls');
        $this->assertInstanceOf('Pop\Mail\Transport\Smtp', $transport);
        $this->assertEquals('localhost', $transport->getHost());
        $this->assertEquals(25, $transport->getPort());
    }

    public function testSmtpCreate()
    {
        $transport = Transport\Smtp::create([
            'host'       => 'localhost',
            'port'       => 25,
            'username'   => 'username',
            'password'   => 'password',
            'encryption' => 'tls'
        ]);
        $this->assertInstanceOf('Pop\Mail\Transport\Smtp', $transport);
        $this->assertEquals('localhost', $transport->getHost());
        $this->assertEquals(25, $transport->getPort());
        $this->assertEquals('username', $transport->getUsername());
        $this->assertEquals('password', $transport->getPassword());
        $this->assertEquals('tls', $transport->getEncryption());
    }

    public function testSmtpCreateException()
    {
        $this->expectException('Pop\Mail\Transport\Exception');
        $transport = Transport\Smtp::create([]);
    }

}