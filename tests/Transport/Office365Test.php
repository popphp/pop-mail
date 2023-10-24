<?php

namespace Pop\Mail\Test\Transport;

use Pop\Mail;
use Pop\Mail\Transport;
use PHPUnit\Framework\TestCase;

class Office365Test extends TestCase
{

    public function testOffice365Test1()
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

        $office365->setTenantId('TENANT_ID2');
        $this->assertEquals('TENANT_ID2', $office365->getTenantId());
        $this->assertTrue($office365->hasTenantId());

        $mailer    = new Mail\Mailer($office365);
        $message   = new Mail\Message('Test Subject!');
        $message->setTo(['root@localhost' => 'root'])
            ->setCc(['root@localhost' => 'root'])
            ->setBcc(['root@localhost' => 'root'])
            ->setFrom(['root@localhost' => 'root'])
            ->setReplyTo(['root@localhost' => 'root'])
            ->setSender(['root@localhost' => 'root'])
            ->addText('Hey, this is a test!')
            ->addHtml('<html><body><h3>Hey!</h3><p>This is a test!</p></body></html>')
            ->attachFile(__DIR__ . '/../tmp/test.pdf');

        $mailer->send($message);

        $this->assertInstanceOf('Pop\Mail\Transport\Office365', $office365);
        $this->assertInstanceOf('Pop\Http\Client', $office365->getClient());
    }

    public function testOffice365Test2()
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

        $mailer    = new Mail\Mailer($office365);
        $message   = new Mail\Message('Test Subject!');
        $message->setTo('root@localhost')
            ->setCc('root@localhost')
            ->setBcc('root@localhost')
            ->setFrom('root@localhost')
            ->addText('Hey, this is a test!')
            ->addHtml('<html><body><h3>Hey!</h3><p>This is a test!</p></body></html>')
            ->attachFile(__DIR__ . '/../tmp/test.pdf');

        $mailer->send($message);

        $this->assertInstanceOf('Pop\Mail\Transport\Office365', $office365);
        $this->assertInstanceOf('Pop\Http\Client', $office365->getClient());
    }

    public function testOffice365Test3()
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

    public function testOffice365Test4()
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

    public function testOffice365Test5()
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
    }

}