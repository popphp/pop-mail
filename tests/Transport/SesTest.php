<?php

namespace Pop\Mail\Test\Transport;

use Pop\Mail;
use Pop\Mail\Transport;
use PHPUnit\Framework\TestCase;

class SesTest extends TestCase
{

    public function testSes1()
    {
        $transport = new Transport\Ses(json_encode(['key' => 'API_KEY', 'secret' => 'API_SECRET']));
        $mailer    = new Mail\Mailer($transport);
        $message   = new Mail\Message('Test Subject!');
        $message->setTo('root@localhost')
            ->setFrom('root@localhost')
            ->setCc(['root@localhost' => 'root'])
            ->setBcc(['root@localhost' => 'root'])
            ->setFrom(['root@localhost' => 'root'])
            ->setReplyTo(['root@localhost' => 'root'])
            ->setReturnPath(['root@localhost' => 'root'])
            ->addText('Hey, this is a test!');

        $this->assertInstanceOf('Pop\Mail\Transport\Ses', $transport);

        $this->expectException('Aws\Ses\Exception\SesException');
        $mailer->send($message);
    }

    public function testSes2()
    {
        $transport = new Transport\Ses(json_encode(['key' => 'API_KEY', 'secret' => 'API_SECRET']));
        $mailer    = new Mail\Mailer($transport);
        $message   = new Mail\Message('Test Subject!');
        $message->setTo('root@localhost')
            ->setFrom('root@localhost')
            ->setCc(['root@localhost' => 'root'])
            ->setBcc(['root@localhost' => 'root'])
            ->setFrom(['root@localhost' => 'root'])
            ->setReplyTo(['root@localhost' => 'root'])
            ->setReturnPath('root@localhost')
            ->addHtml('<html><body><h3>Hey!</h3><p>This is a test!</p></body></html>');

        $this->assertInstanceOf('Pop\Mail\Transport\Ses', $transport);

        $this->expectException('Aws\Ses\Exception\SesException');
        $mailer->send($message);
    }

    public function testSes3()
    {
        $transport = new Transport\Ses(json_encode(['key' => 'API_KEY', 'secret' => 'API_SECRET']));
        $mailer    = new Mail\Mailer($transport);
        $message   = new Mail\Message('Test Subject!');
        $message->setTo('root@localhost')
            ->setFrom('root@localhost')
            ->addText('Hey, this is a test!')
            ->addHtml('<html><body><h3>Hey!</h3><p>This is a test!</p></body></html>')
            ->attachFile(__DIR__ . '/../tmp/test.pdf');

        $this->assertInstanceOf('Pop\Mail\Transport\Ses', $transport);

        $this->expectException('Aws\Ses\Exception\SesException');
        $mailer->send($message);
    }

    public function testSes4()
    {
        $this->expectException('Pop\Mail\Transport\Exception');
        $transport = new Transport\Ses([]);
    }

}