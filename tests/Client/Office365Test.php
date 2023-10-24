<?php

namespace Pop\Mail\Test\Client;

use Pop\Http;
use Pop\Mail\Client;
use PHPUnit\Framework\TestCase;

class Office365Test extends TestCase
{

    public function testGetMessages1()
    {
        $office365 = new Client\Office365();
        $office365->createClient(json_encode([
            'client_id'     => 'CLIENT_ID',
            'client_secret' => 'CLIENT_SECRET',
            'scope'         => 'https://graph.microsoft.com/.default',
            'tenant_id'     => 'TENANT_ID',
            'account_id'    => 'ACCOUNT_ID',
        ]));
        $office365->setToken('AUTH_TOKEN');
        $office365->setTokenExpires(time() + 1000);

        $this->assertTrue(is_array($office365->getMessages()));
    }

    public function testGetMessages2()
    {
        $office365 = new Client\Office365();
        $office365->createClient(json_encode([
            'client_id'     => 'CLIENT_ID',
            'client_secret' => 'CLIENT_SECRET',
            'scope'         => 'https://graph.microsoft.com/.default',
            'tenant_id'     => 'TENANT_ID',
            'account_id'    => 'ACCOUNT_ID',
        ]));
        $office365->setToken('AUTH_TOKEN');
        $office365->setTokenExpires(time() + 1000);

        $search = [
            'subject%' => 'Test',
            '%to'      => '@outlook.com',
            'from'     => 'me@outlook.com',
            'sent>='   => '2023-10-01'
        ];

        $this->assertTrue(is_array($office365->getMessages('Inbox', $search, 25)));
    }

    public function testGetMessages3()
    {
        $office365 = new Client\Office365();
        $office365->createClient(json_encode([
            'client_id'     => 'CLIENT_ID',
            'client_secret' => 'CLIENT_SECRET',
            'scope'         => 'https://graph.microsoft.com/.default',
            'tenant_id'     => 'TENANT_ID',
            'account_id'    => 'ACCOUNT_ID',
        ]));
        $office365->setToken('AUTH_TOKEN');
        $office365->setTokenExpires(time() + 1000);

        $search = [
            'subject'   => 'Test',
            'to!='      => 'me@outlook.com',
            'sent>'     => '2023-10-01',
            'received<' => '2023-10-01',
            'date<='    => '2023-10-01',
            'unread'    => true
        ];

        $this->assertTrue(is_array($office365->getMessages('Inbox', $search, 25)));
    }

    public function testGetMessage()
    {
        $office365 = new Client\Office365();
        $office365->createClient(json_encode([
            'client_id'     => 'CLIENT_ID',
            'client_secret' => 'CLIENT_SECRET',
            'scope'         => 'https://graph.microsoft.com/.default',
            'tenant_id'     => 'TENANT_ID',
            'account_id'    => 'ACCOUNT_ID',
        ]));
        $office365->setToken('AUTH_TOKEN');
        $office365->setTokenExpires(time() + 1000);

        $this->assertTrue(is_array($office365->getMessage('123456789')));
    }

    public function testGetMessageRaw()
    {
        $office365 = new Client\Office365();
        $office365->createClient(json_encode([
            'client_id'     => 'CLIENT_ID',
            'client_secret' => 'CLIENT_SECRET',
            'scope'         => 'https://graph.microsoft.com/.default',
            'tenant_id'     => 'TENANT_ID',
            'account_id'    => 'ACCOUNT_ID',
        ]));
        $office365->setToken('AUTH_TOKEN');
        $office365->setTokenExpires(time() + 1000);

        $this->assertTrue(is_array($office365->getMessage('123456789', true)));
    }

    public function testGetAttachments()
    {
        $office365 = new Client\Office365();
        $office365->createClient(json_encode([
            'client_id'     => 'CLIENT_ID',
            'client_secret' => 'CLIENT_SECRET',
            'scope'         => 'https://graph.microsoft.com/.default',
            'tenant_id'     => 'TENANT_ID',
            'account_id'    => 'ACCOUNT_ID',
        ]));
        $office365->setToken('AUTH_TOKEN');
        $office365->setTokenExpires(time() + 1000);

        $this->assertTrue(is_array($office365->getAttachments('123456789')));
    }

    public function testGetAttachment()
    {
        $office365 = new Client\Office365();
        $office365->createClient(json_encode([
            'client_id'     => 'CLIENT_ID',
            'client_secret' => 'CLIENT_SECRET',
            'scope'         => 'https://graph.microsoft.com/.default',
            'tenant_id'     => 'TENANT_ID',
            'account_id'    => 'ACCOUNT_ID',
        ]));
        $office365->setToken('AUTH_TOKEN');
        $office365->setTokenExpires(time() + 1000);

        $this->assertTrue(is_array($office365->getAttachment('123456789', '123456789')));
    }

    public function testMarkAsRead()
    {
        $office365 = new Client\Office365();
        $office365->createClient(json_encode([
            'client_id'     => 'CLIENT_ID',
            'client_secret' => 'CLIENT_SECRET',
            'scope'         => 'https://graph.microsoft.com/.default',
            'tenant_id'     => 'TENANT_ID',
            'account_id'    => 'ACCOUNT_ID',
        ]));
        $office365->setToken('AUTH_TOKEN');
        $office365->setTokenExpires(time() + 1000);

        $this->assertInstanceOf('Pop\Mail\Client\Office365', $office365->markAsRead('123456789'));
        $this->assertInstanceOf('Pop\Mail\Client\Office365', $office365->markAsUnread('123456789'));
    }

    public function testGetMessagesException()
    {
        $this->expectException('Pop\Mail\Client\Exception');
        $office365 = new Client\Office365();
        $messages = $office365->getMessages();
    }

    public function testGetMessageException()
    {
        $this->expectException('Pop\Mail\Client\Exception');
        $office365 = new Client\Office365();
        $message = $office365->getMessage('123456789');
    }

    public function testGetAttachmentsException()
    {
        $this->expectException('Pop\Mail\Client\Exception');
        $office365 = new Client\Office365();
        $message = $office365->getAttachments('123456789');
    }

    public function testGetAttachmentException()
    {
        $this->expectException('Pop\Mail\Client\Exception');
        $office365 = new Client\Office365();
        $message = $office365->getAttachment('123456789', '123456798');
    }

    public function testMarkAsReadException()
    {
        $this->expectException('Pop\Mail\Client\Exception');
        $office365 = new Client\Office365();
        $office365->markAsRead('123456789');
    }

}