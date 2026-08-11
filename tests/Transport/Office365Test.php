<?php

namespace Pop\Mail\Test\Transport;

use Pop\Http\Client\Handler\Mock;
use Pop\Http\Client\Response;
use Pop\Mail\Message;
use Pop\Mail\Transport;
use PHPUnit\Framework\TestCase;

class Office365Test extends TestCase
{

    public function testOffice365SendPostsExpectedFieldsAndReturnsParsedResponse()
    {
        $office365 = new Transport\Office365();
        $office365->createClient(json_encode([
            'client_id'     => 'CLIENT_ID',
            'client_secret' => 'CLIENT_SECRET',
            'scope'         => 'https://graph.microsoft.com/.default',
            'tenant_id'     => 'TENANT_ID',
            'account_id'    => 'ACCOUNT_ID',
        ]));
        $office365->setToken('ACCESS_TOKEN');
        $office365->setTokenExpires(time() + 1000);

        $mock = new Mock();
        $mock->queue(new Response(['code' => 202, 'headers' => ['Content-Type' => 'text/plain'], 'body' => '']));
        $office365->getClient()->setHandler($mock);

        $message = new Message('Test Subject!');
        $message->setTo(['root@localhost' => 'root'])
            ->setCc(['root@localhost' => 'root'])
            ->setBcc(['root@localhost' => 'root'])
            ->setFrom(['root@localhost' => 'root'])
            ->setReplyTo(['root@localhost' => 'root'])
            ->setSender(['root@localhost' => 'root'])
            ->addText('Hey, this is a test!')
            ->addHtml('<html><body><h3>Hey!</h3><p>This is a test!</p></body></html>')
            ->attachFile(__DIR__ . '/../tmp/test.pdf');

        foreach ($message->getParts() as $part) {
            if ($part instanceof Message\Attachment) {
                $part->removeHeader('Content-Type');
                $part->addHeader('Content-Type', 'application/pdf; charset=binary');
            }
        }

        $office365->send($message);

        $sentRequest = $mock->getLastRequest();
        $sentFields  = json_decode($sentRequest->getData()->getDataContent(), true);

        $this->assertEquals('application/pdf', $sentFields['message']['attachments'][0]['contentType']);
        $this->assertEquals('root', $sentFields['message']['toRecipients'][0]['emailAddress']['name']);
        $this->assertStringEndsWith('/ACCOUNT_ID/sendmail', $sentRequest->getUriAsString(false));
    }

    public function testOffice365SendWithBareAddressesOmitsNameFromEmailAddress()
    {
        $office365 = new Transport\Office365();
        $office365->createClient(json_encode([
            'client_id'     => 'CLIENT_ID',
            'client_secret' => 'CLIENT_SECRET',
            'scope'         => 'https://graph.microsoft.com/.default',
            'tenant_id'     => 'TENANT_ID',
            'account_id'    => 'ACCOUNT_ID',
        ]));
        $office365->setToken('ACCESS_TOKEN');
        $office365->setTokenExpires(time() + 1000);

        $mock = new Mock();
        $mock->queue(new Response(['code' => 202, 'headers' => ['Content-Type' => 'text/plain'], 'body' => '']));
        $office365->getClient()->setHandler($mock);

        $message = new Message('Test Subject!');
        $message->setTo('root@localhost')
            ->setCc('root@localhost')
            ->setBcc('root@localhost')
            ->setFrom('root@localhost')
            ->addText('Hey, this is a test!');

        $office365->send($message);

        $sentRequest = $mock->getLastRequest();
        $sentFields  = json_decode($sentRequest->getData()->getDataContent(), true);

        $this->assertEquals(['address' => 'root@localhost'], $sentFields['message']['toRecipients'][0]['emailAddress']);
        $this->assertEquals(['address' => 'root@localhost'], $sentFields['message']['ccRecipients'][0]['emailAddress']);
        $this->assertEquals(['address' => 'root@localhost'], $sentFields['message']['bccRecipients'][0]['emailAddress']);
    }

    public function testCreateClientMissingAccountIdThrows()
    {
        $this->expectException('Pop\Mail\Api\Exception');
        $office365 = new Transport\Office365();
        $office365->createClient([
            'client_id'     => 'CLIENT_ID',
            'client_secret' => 'CLIENT_SECRET',
            'scope'         => 'https://graph.microsoft.com/.default',
            'tenant_id'     => 'TENANT_ID'
        ]);
    }

    public function testRequestTokenMissingTenantIdThrows()
    {
        $this->expectException('Pop\Mail\Api\Exception');
        $office365 = new Transport\Office365();
        $office365->createClient([
            'client_id'     => 'CLIENT_ID',
            'client_secret' => 'CLIENT_SECRET',
            'scope'         => 'https://graph.microsoft.com/.default',
            'account_id'    => 'ACCOUNT_ID',
        ]);

        $office365->requestToken();
    }

    public function testOffice365Test5AlreadyValidTokenShortCircuits()
    {
        $office365 = new Transport\Office365();
        $office365->createClient([
            'client_id'     => 'CLIENT_ID',
            'client_secret' => 'CLIENT_SECRET',
            'scope'         => 'https://graph.microsoft.com/.default',
            'tenant_id'     => 'TENANT_ID',
            'account_id'    => 'ACCOUNT_ID',
        ]);

        $office365->setToken('ACCESS_TOKEN');
        $office365->setTokenExpires(time() + 1000);

        $this->assertInstanceOf('Pop\Mail\Transport\Office365', $office365->requestToken());
        $this->assertEquals('ACCESS_TOKEN', $office365->getToken());
    }

    public function testRequestTokenSuccessPathSetsTokenAndExpiry()
    {
        $office365 = new Transport\Office365();
        $office365->createClient([
            'client_id'     => 'CLIENT_ID',
            'client_secret' => 'CLIENT_SECRET',
            'scope'         => 'https://graph.microsoft.com/.default',
            'tenant_id'     => 'TENANT_ID',
            'account_id'    => 'ACCOUNT_ID',
        ]);

        $mock = new Mock();
        $mock->queue(new Response([
            'code'    => 200,
            'headers' => ['Content-Type' => 'application/json'],
            'body'    => json_encode(['access_token' => 'NEW_ACCESS_TOKEN', 'expires_in' => 3600]),
        ]));
        $office365->setHandler($mock);

        $before = time();
        $office365->requestToken();

        $this->assertEquals('NEW_ACCESS_TOKEN', $office365->getToken());
        $this->assertGreaterThanOrEqual($before + 3600, $office365->getTokenExpires());
    }

}
