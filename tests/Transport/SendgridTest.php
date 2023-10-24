<?php

namespace Pop\Mail\Test\Transport;

use Pop\Mail;
use Pop\Mail\Transport;
use PHPUnit\Framework\TestCase;

class SendgridTest extends TestCase
{

    public function testSendgrid1()
    {
        $transport = new Transport\Sendgrid(json_encode(['api_url' => 'http://localhost', 'api_key' => 'MY_API_KEY']));
        $mailer    = new Mail\Mailer($transport, 'root@localhost');
        $message   = new Mail\Message('Test Subject!');
        $message->setTo('root@localhost')
            ->setCc('root@localhost')
            ->setBcc('root@localhost')
            ->setFrom('root@localhost')
            ->setReplyTo('root@localhost')
            ->addText('Hey, this is a test!')
            ->addHtml('<html><body><h3>Hey!</h3><p>This is a test!</p></body></html>')
            ->attachFile(__DIR__ . '/../tmp/test.pdf');

        $mailer->send($message);

        $this->assertInstanceOf('Pop\Mail\Transport\Sendgrid', $transport);
        $this->assertInstanceOf('Pop\Http\Client', $transport->getClient());
    }

    public function testSendgrid2()
    {
        $transport = new Transport\Sendgrid(['api_url' => 'http://localhost', 'api_key' => 'MY_API_KEY']);
        $mailer    = new Mail\Mailer($transport, 'root@localhost');
        $message   = new Mail\Message('Test Subject!');
        $message->setTo(['root@localhost' => 'root'])
            ->setCc(['root@localhost' => 'root'])
            ->setBcc(['root@localhost' => 'root'])
            ->setFrom(['root@localhost' => 'root'])
            ->addText('Hey, this is a test!')
            ->addHtml('<html><body><h3>Hey!</h3><p>This is a test!</p></body></html>')
            ->attachFile(__DIR__ . '/../tmp/test.pdf');

        $mailer->send($message);

        $this->assertInstanceOf('Pop\Mail\Transport\Sendgrid', $transport);
        $this->assertInstanceOf('Pop\Http\Client', $transport->getClient());
    }

    public function testSendgrid3()
    {
        $transport = new Transport\Sendgrid(['api_url' => 'http://localhost', 'api_key' => 'MY_API_KEY']);
        $mailer    = new Mail\Mailer($transport, 'root@localhost');
        $message   = new Mail\Message('Test Subject!');
        $message->setTo(['root@localhost' => 'root'])
            ->setCc(['root@localhost' => 'root'])
            ->setBcc(['root@localhost' => 'root'])
            ->setReplyTo(['root@localhost' => 'root'])
            ->addText('Hey, this is a test!')
            ->addHtml('<html><body><h3>Hey!</h3><p>This is a test!</p></body></html>')
            ->attachFile(__DIR__ . '/../tmp/test.pdf');

        $mailer->send($message);

        $this->assertInstanceOf('Pop\Mail\Transport\Sendgrid', $transport);
        $this->assertInstanceOf('Pop\Http\Client', $transport->getClient());
    }

    public function testSendgrid4()
    {
        $this->expectException('Pop\Mail\Transport\Exception');
        $transport = new Transport\Sendgrid([]);
    }

}