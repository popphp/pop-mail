<?php

namespace Pop\Mail\Test\Transport;

use Pop\Http\Client\Handler\Mock;
use Pop\Http\Client\Response;
use Pop\Mail\Message;
use Pop\Mail\Transport;
use PHPUnit\Framework\TestCase;

class SendgridTest extends TestCase
{

    public function testSendgridSendPostsExpectedFieldsAndReturnsParsedResponse()
    {
        $transport = new Transport\Sendgrid(json_encode(['api_url' => 'https://api.sendgrid.com/v3/mail/send', 'api_key' => 'MY_API_KEY']));

        $mock = new Mock();
        $mock->queue(new Response(['code' => 202, 'headers' => [], 'body' => '']));
        $transport->getClient()->setHandler($mock);

        $message = new Message('Test Subject!');
        $message->setTo(['root@localhost' => 'Root User'])
            ->setCc(['root@localhost' => 'CC User'])
            ->setBcc(['root@localhost' => 'BCC User'])
            ->setFrom(['root@localhost' => 'From User'])
            ->setReplyTo(['root@localhost' => 'Reply User'])
            ->addHeader('X-Custom-Header', 'CustomValue')
            ->addText('Hey, this is a test!')
            ->addHtml('<html><body><h3>Hey!</h3><p>This is a test!</p></body></html>')
            ->attachFile(__DIR__ . '/../tmp/test.pdf');

        foreach ($message->getParts() as $part) {
            if ($part instanceof Message\Attachment) {
                $part->removeHeader('Content-Type');
                $part->addHeader('Content-Type', 'application/pdf; charset=binary');
            }
        }

        $result = $transport->send($message);

        $this->assertInstanceOf('Pop\Http\Client\Response', $result);
        $this->assertEquals(202, $result->getStatusCode());

        $sentRequest = $mock->getLastRequest();
        $sentFields  = json_decode($sentRequest->getData()->getDataContent(), true);

        $this->assertEquals('CustomValue', $sentFields['headers']['X-Custom-Header']);
        $this->assertEquals('application/pdf', $sentFields['attachments'][0]['type']);
        $this->assertEquals('root@localhost', $sentFields['personalizations'][0]['to'][0]['email']);
        $this->assertEquals('Root User', $sentFields['personalizations'][0]['to'][0]['name']);
        $this->assertEquals('CC User', $sentFields['personalizations'][1]['cc'][0]['name']);
        $this->assertEquals('BCC User', $sentFields['personalizations'][2]['bcc'][0]['name']);
        $this->assertEquals('From User', $sentFields['from']['name']);
        $this->assertEquals('Reply User', $sentFields['reply_to']['name']);
    }

    public function testSendgridMissingOptionsThrows()
    {
        $this->expectException('Pop\Mail\Transport\Exception');
        $transport = new Transport\Sendgrid([]);
    }

    public function testSendgridSendWithBareAddressesOmitsNameAndDefaultsReplyToFrom()
    {
        $transport = new Transport\Sendgrid(json_encode(['api_url' => 'https://api.sendgrid.com/v3/mail/send', 'api_key' => 'MY_API_KEY']));

        $mock = new Mock();
        $mock->queue(new Response(['code' => 202, 'headers' => [], 'body' => '']));
        $transport->getClient()->setHandler($mock);

        $message = new Message('Test Subject!');
        $message->setTo('root@localhost')
            ->setCc('root@localhost')
            ->setBcc('root@localhost')
            ->setFrom('root@localhost')
            ->addText('Hey, this is a test!');

        $transport->send($message);

        $sentRequest = $mock->getLastRequest();
        $sentFields  = json_decode($sentRequest->getData()->getDataContent(), true);

        $this->assertEquals(['email' => 'root@localhost'], $sentFields['personalizations'][0]['to'][0]);
        $this->assertArrayNotHasKey('name', $sentFields['personalizations'][0]['to'][0]);
        $this->assertEquals(['email' => 'root@localhost'], $sentFields['personalizations'][1]['cc'][0]);
        $this->assertArrayNotHasKey('name', $sentFields['personalizations'][1]['cc'][0]);
        $this->assertEquals(['email' => 'root@localhost'], $sentFields['personalizations'][2]['bcc'][0]);
        $this->assertArrayNotHasKey('name', $sentFields['personalizations'][2]['bcc'][0]);
        $this->assertEquals($sentFields['from'], $sentFields['reply_to']);
    }

}
