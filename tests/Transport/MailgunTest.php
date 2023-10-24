<?php

namespace Pop\Mail\Test\Transport;

use Pop\Mail;
use Pop\Mail\Transport;
use PHPUnit\Framework\TestCase;

class MailgunTest extends TestCase
{

    public function testMailgun1()
    {
        $transport = new Transport\Mailgun(json_encode(['api_url' => 'http://localhost', 'api_key' => 'MY_API_KEY']));
        $mailer    = new Mail\Mailer($transport);
        $message   = new Mail\Message('Test Subject!');
        $message->setTo('root@localhost')
            ->setFrom('root@localhost')
            ->addText('Hey, this is a test!')
            ->addHtml('<html><body><h3>Hey!</h3><p>This is a test!</p></body></html>')
            ->attachFile(__DIR__ . '/../tmp/test.pdf');

        $mailer->send($message);

        $this->assertInstanceOf('Pop\Mail\Transport\Mailgun', $transport);
        $this->assertInstanceOf('Pop\Http\Client', $transport->getClient());
    }

    public function testMailgun2()
    {
        $this->expectException('Pop\Mail\Transport\Exception');
        $transport = new Transport\Mailgun([]);
    }

}