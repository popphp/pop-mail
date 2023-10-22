<?php

namespace Pop\Mail\Test;

use Pop\Mail;
use Pop\Mail\Transport;
use PHPUnit\Framework\TestCase;

class TransportTest extends TestCase
{

    public function testSendmail()
    {
        $transport = new Transport\Sendmail('-f');
        $this->assertInstanceOf('Pop\Mail\Transport\Sendmail', $transport);
        $this->assertEquals('-f', $transport->getParams());
    }

    public function testSmtp()
    {
        $transport = new Transport\Smtp('localhost', 25, 'tls');
        $this->assertInstanceOf('Pop\Mail\Transport\Smtp', $transport);
        $this->assertEquals('localhost', $transport->getHost());
        $this->assertEquals(25, $transport->getPort());
    }

    public function testMailgun()
    {
        $transport = new Transport\Mailgun('http://localhost', 'MY_API_KEY');
        $mailer    = new Mail\Mailer($transport);
        $message   = new Mail\Message('Test Subject!');
        $message->setTo('root@localhost')
            ->setFrom('root@localhost')
            ->addText('Hey, this is a test!')
            ->addHtml('<html><body><h3>Hey!</h3><p>This is a test!</p></body></html>')
            ->attachFile(__DIR__ . '/tmp/test.pdf');

        $mailer->send($message);


        $this->assertInstanceOf('Pop\Mail\Transport\Mailgun', $transport);
        $this->assertInstanceOf('Pop\Http\Client', $transport->getClient());
    }

    public function testSendgrid()
    {
        $transport = new Transport\Sendgrid('http://localhost', 'MY_API_KEY');
        $mailer    = new Mail\Mailer($transport, 'root@localhost');
        $message   = new Mail\Message('Test Subject!');
        $message->setTo('root@localhost')
            ->addText('Hey, this is a test!')
            ->addHtml('<html><body><h3>Hey!</h3><p>This is a test!</p></body></html>')
            ->attachFile(__DIR__ . '/tmp/test.pdf');

        $mailer->send($message);

        $this->assertInstanceOf('Pop\Mail\Transport\Sendgrid', $transport);
        $this->assertInstanceOf('Pop\Http\Client', $transport->getClient());
    }

}