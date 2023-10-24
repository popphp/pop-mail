<?php

namespace Pop\Mail\Test\Client;

use Pop\Http;
use Pop\Mail\Client;
use PHPUnit\Framework\TestCase;

class GoogleTest extends TestCase
{

    public function testGetMessages1()
    {
        $google = new Client\Google();
        $google->createClient(__DIR__ . '/../tmp/my-google-app.json', 'test@gmail.com');
        $google->setToken('AUTH_TOKEN');
        $google->setTokenExpires(time() + 1000);

        $this->expectException('DomainException');
        $messages = $google->getMessages();
    }

    public function testGetMessages2()
    {
        $google = new Client\Google();
        $google->createClient(__DIR__ . '/../tmp/my-google-app.json', 'test@gmail.com');
        $google->setToken('AUTH_TOKEN');
        $google->setTokenExpires(time() + 1000);

        $search = [
            'subject%' => 'Test',
            '%to'      => '@outlook.com',
            'from'     => 'me@outlook.com',
            'sent>='   => '2023-10-01'
        ];

        $this->expectException('DomainException');
        $messages = $google->getMessages('Inbox', $search, 25);
    }

    public function testGetMessages3()
    {
        $google = new Client\Google();
        $google->createClient(__DIR__ . '/../tmp/my-google-app.json', 'test@gmail.com');
        $google->setToken('AUTH_TOKEN');
        $google->setTokenExpires(time() + 1000);

        $search = [
            'subject'   => 'Test',
            'to!='      => 'me@outlook.com',
            'sent>'     => '2023-10-01',
            'received<' => '2023-10-01',
            'date<='    => '2023-10-01',
            'unread'    => true
        ];

        $this->expectException('DomainException');
        $messages = $google->getMessages('Inbox', $search, 25);
    }

    public function testGetMessage()
    {
        $google = new Client\Google();
        $google->createClient(__DIR__ . '/../tmp/my-google-app.json', 'test@gmail.com');
        $google->setToken('AUTH_TOKEN');
        $google->setTokenExpires(time() + 1000);

        $this->expectException('DomainException');
        $message = $google->getMessage('123456789');
    }

    public function testGetMessageRaw()
    {
        $google = new Client\Google();
        $google->createClient(__DIR__ . '/../tmp/my-google-app.json', 'test@gmail.com');
        $google->setToken('AUTH_TOKEN');
        $google->setTokenExpires(time() + 1000);

        $this->expectException('DomainException');
        $message = $google->getMessage('123456789', true);
    }

    public function testGetAttachments()
    {
        $google = new Client\Google();
        $google->createClient(__DIR__ . '/../tmp/my-google-app.json', 'test@gmail.com');
        $google->setToken('AUTH_TOKEN');
        $google->setTokenExpires(time() + 1000);

        $this->expectException('DomainException');
        $attachments = $google->getAttachments('123456789');
    }

    public function testGetAttachment()
    {
        $google = new Client\Google();
        $google->createClient(__DIR__ . '/../tmp/my-google-app.json', 'test@gmail.com');
        $google->setToken('AUTH_TOKEN');
        $google->setTokenExpires(time() + 1000);

        $this->expectException('DomainException');
        $attachment = $google->getAttachment('123456789', '123456789');
    }

    public function testMarkAsRead()
    {
        $google = new Client\Google();
        $google->createClient(__DIR__ . '/../tmp/my-google-app.json', 'test@gmail.com');
        $google->setToken('AUTH_TOKEN');
        $google->setTokenExpires(time() + 1000);

        $this->expectException('DomainException');
        $google->markAsRead('123456789');
    }

    public function testMarkAsUnread()
    {
        $google = new Client\Google();
        $google->createClient(__DIR__ . '/../tmp/my-google-app.json', 'test@gmail.com');
        $google->setToken('AUTH_TOKEN');
        $google->setTokenExpires(time() + 1000);

        $this->expectException('DomainException');
        $google->markAsUnread('123456789');
    }

    public function testGetMessagesException()
    {
        $this->expectException('Pop\Mail\Client\Exception');
        $google = new Client\Google();
        $messages = $google->getMessages();
    }

    public function testGetMessageException()
    {
        $this->expectException('Pop\Mail\Client\Exception');
        $google = new Client\Google();
        $message = $google->getMessage('123456789');
    }

    public function testGetAttachmentsException()
    {
        $this->expectException('Pop\Mail\Client\Exception');
        $google = new Client\Google();
        $message = $google->getAttachments('123456789');
    }

    public function testGetAttachmentException()
    {
        $this->expectException('Pop\Mail\Client\Exception');
        $google = new Client\Google();
        $message = $google->getAttachment('123456789', '123456798');
    }

    public function testMarkAsReadException()
    {
        $this->expectException('Pop\Mail\Client\Exception');
        $google = new Client\Google();
        $google->markAsRead('123456789');
    }

}