<?php

namespace Pop\Mail\Test\Transport;

use Pop\Http;
use Pop\Mail\Client;
use PHPUnit\Framework\TestCase;

class ClientTest extends TestCase
{

    public function testClient1()
    {
        $time = time() + 1000;
        $office365 = new Client\Office365();
        $office365->setClient(new Http\Client());
        $office365->setClientId('CLIENT_ID');
        $office365->setClientSecret('CLIENT_SECRET');
        $office365->setScope('SCOPE');
        $office365->setAccountId('ACCOUNT_ID');
        $office365->setUsername('USERNAME');
        $office365->setToken('TOKEN');
        $office365->setTokenExpires($time);

        $this->assertInstanceOf('Pop\Http\Client', $office365->getClient());
        $this->assertTrue($office365->hasClient());
        $this->assertEquals('CLIENT_ID', $office365->getClientId());
        $this->assertTrue($office365->hasClientId());
        $this->assertEquals('CLIENT_SECRET', $office365->getClientSecret());
        $this->assertTrue($office365->hasClientSecret());
        $this->assertEquals('SCOPE', $office365->getScope());
        $this->assertTrue($office365->hasScope());
        $this->assertEquals('ACCOUNT_ID', $office365->getAccountId());
        $this->assertTrue($office365->hasAccountId());
        $this->assertEquals('USERNAME', $office365->getUsername());
        $this->assertTrue($office365->hasUsername());
        $this->assertEquals('TOKEN', $office365->getToken());
        $this->assertTrue($office365->hasToken());
        $this->assertEquals($time, $office365->getTokenExpires());
        $this->assertTrue($office365->hasTokenExpires());
    }

    public function testClient2()
    {
        $google = new Client\Google();
        $google->createClient(__DIR__ . '/../tmp/my-google-app.json', 'me@gmail.com');
        $google->setToken('TOKEN')
            ->setTokenExpires(time() + 1000);

        $this->assertInstanceOf('Pop\Mail\Client\Google', $google->requestToken());
    }

    public function testClient2Exception1()
    {
        $this->expectException('Pop\Mail\Api\Exception');
        $google = new Client\Google();
        $google->createClient(__DIR__ . '/../tmp/my-google-app.json');
    }

    public function testClient2Exception2()
    {
        $this->expectException('Pop\Mail\Api\Exception');
        $google = new Client\Google();
        $google->requestToken();
    }


    public function testParseOptions()
    {
        $google  = new Client\Google();
        $options = $google->parseOptions(__DIR__ . '/../tmp/my-google-app.json');
        $this->assertTrue(is_array($options));
    }

    public function testParseOptionsException()
    {
        $this->expectException('Pop\Mail\Api\Exception');
        $google  = new Client\Google();
        $options = $google->parseOptions('bad data');
    }

}