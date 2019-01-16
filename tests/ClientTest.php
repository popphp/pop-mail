<?php

namespace Pop\Mail\Test;

use Pop\Mail\Client;
use PHPUnit\Framework\TestCase;

class ClientTest extends TestCase
{

    public function testConstructor()
    {
        $client = new Client\Imap('domain.com', 993);
        $client->setUsername('me@domain.com')
            ->setPassword('12test34');

        $client->setFolder('INBOX');
        $this->assertInstanceOf('Pop\Mail\Client\Imap', $client);
        $this->assertEquals('domain.com', $client->getHost());
        $this->assertEquals(993, $client->getPort());
        $this->assertEquals('INBOX', $client->getFolder());
        $this->assertEquals('me@domain.com', $client->getUsername());
        $this->assertEquals('12test34', $client->getPassword());
        $this->assertEquals('domain.com', $client->getHost());
        $this->assertEquals('imap', $client->getService());
    }

    public function testPopConstructor()
    {
        $client = new Client\Pop('domain.com', 993);
        $this->assertEquals('pop3', $client->getService());
    }

    public function testImapClient()
    {
        $client = new Client\Imap('domain.com', 993);
        $this->assertNull($client->connection());
        $this->assertFalse($client->isOpen());
        $this->assertNull($client->getConnectionString());
    }

}