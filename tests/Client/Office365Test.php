<?php

namespace Pop\Mail\Test\Client;

use Pop\Http\Client\Handler\Mock;
use Pop\Http\Client\Response;
use Pop\Mail\Client;
use PHPUnit\Framework\TestCase;

class Office365Test extends TestCase
{

    protected function createOffice365(): Client\Office365
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
        return $office365;
    }

    protected function queueJson(Client\Office365 $office365, array $body): Mock
    {
        $mock = new Mock();
        $mock->queue(new Response([
            'code'    => 200,
            'headers' => ['Content-Type' => 'application/json'],
            'body'    => json_encode($body),
        ]));
        $office365->getClient()->setHandler($mock);
        return $mock;
    }

    public function testGetMessages1()
    {
        $office365 = $this->createOffice365();
        $mock      = $this->queueJson($office365, ['value' => [['id' => 'MSG1', 'subject' => 'Test']]]);

        $result = $office365->getMessages();

        $this->assertEquals('MSG1', $result['value'][0]['id']);
        $this->assertStringContainsString("/ACCOUNT_ID/mailfolders('Inbox')/messages", $mock->getLastRequest()->getUriAsString(false));
    }

    public function testGetMessages2FilterBuildsExpectedODataQuery()
    {
        $office365 = $this->createOffice365();
        $mock      = $this->queueJson($office365, ['value' => []]);

        $search = [
            'subject%' => 'Test',
            '%to'      => '@outlook.com',
            'from'     => 'me@outlook.com',
            'sent>='   => '2023-10-01'
        ];

        $office365->getMessages('Inbox', $search, 25);

        $uri = urldecode($mock->getLastRequest()->getUriAsString());
        $this->assertStringContainsString("startsWith(subject, 'Test')", $uri);
        $this->assertStringContainsString("endsWith(to, '@outlook.com')", $uri);
        $this->assertStringContainsString("from eq 'me@outlook.com'", $uri);
    }

    public function testGetMessages3FilterOperators()
    {
        $office365 = $this->createOffice365();
        $mock      = $this->queueJson($office365, ['value' => []]);

        $search = [
            'subject'   => 'Test',
            'to!='      => 'me@outlook.com',
            'unread'    => true
        ];

        $office365->getMessages('Inbox', $search, 25);

        $uri = urldecode($mock->getLastRequest()->getUriAsString());
        $this->assertStringContainsString("subject eq 'Test'", $uri);
        $this->assertStringContainsString("to ne 'me@outlook.com'", $uri);
    }

    public function testGetMessage()
    {
        $office365 = $this->createOffice365();
        $this->queueJson($office365, ['id' => 'MSG1', 'subject' => 'Test Subject']);

        $result = $office365->getMessage('MSG1');

        $this->assertEquals('Test Subject', $result['subject']);
    }

    public function testGetMessageRaw()
    {
        $office365 = $this->createOffice365();
        $mock      = $this->queueJson($office365, ['id' => 'MSG1']);

        $office365->getMessage('MSG1', true);

        $this->assertStringEndsWith('/$value', $mock->getLastRequest()->getUriAsString(false));
    }

    public function testGetAttachments()
    {
        $office365 = $this->createOffice365();
        $this->queueJson($office365, ['value' => [['id' => 'ATT1', 'name' => 'test.pdf']]]);

        $result = $office365->getAttachments('MSG1');

        $this->assertEquals('test.pdf', $result['value'][0]['name']);
    }

    public function testGetAttachment()
    {
        $office365 = $this->createOffice365();
        $this->queueJson($office365, ['id' => 'ATT1', 'name' => 'test.pdf', 'contentBytes' => base64_encode('data')]);

        $result = $office365->getAttachment('MSG1', 'ATT1');

        $this->assertEquals('test.pdf', $result['name']);
    }

    public function testMarkAsRead()
    {
        $office365 = $this->createOffice365();
        $mock      = $this->queueJson($office365, ['id' => 'MSG1', 'isRead' => true]);

        $result = $office365->markAsRead('MSG1');

        $this->assertSame($office365, $result);
        $this->assertEquals('PATCH', $mock->getLastRequest()->getMethod());
        $this->assertTrue($mock->getLastRequest()->getData()->getData('isRead'));
    }

    public function testMarkAsUnread()
    {
        $office365 = $this->createOffice365();
        $mock      = $this->queueJson($office365, ['id' => 'MSG1', 'isRead' => false]);

        $office365->markAsUnread('MSG1');

        $this->assertFalse($mock->getLastRequest()->getData()->getData('isRead'));
    }

    public function testGetMessagesException()
    {
        $this->expectException('Pop\Mail\Client\Exception');
        $office365 = new Client\Office365();
        $office365->getMessages();
    }

    public function testGetMessageException()
    {
        $this->expectException('Pop\Mail\Client\Exception');
        $office365 = new Client\Office365();
        $office365->getMessage('123456789');
    }

    public function testGetAttachmentsException()
    {
        $this->expectException('Pop\Mail\Client\Exception');
        $office365 = new Client\Office365();
        $office365->getAttachments('123456789');
    }

    public function testGetAttachmentException()
    {
        $this->expectException('Pop\Mail\Client\Exception');
        $office365 = new Client\Office365();
        $office365->getAttachment('123456789', '123456798');
    }

    public function testMarkAsReadException()
    {
        $this->expectException('Pop\Mail\Client\Exception');
        $office365 = new Client\Office365();
        $office365->markAsRead('123456789');
    }

}