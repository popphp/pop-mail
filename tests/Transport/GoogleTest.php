<?php

namespace Pop\Mail\Test\Transport;

use Pop\Mail;
use Pop\Mail\Transport;
use PHPUnit\Framework\TestCase;

class GoogleTest extends TestCase
{

    public function testGoogle1()
    {
        $google = new Transport\Google();
        $google->createClient(__DIR__ . '/../tmp/my-google-app.json', 'test@gmail.com');
        $google->setToken('ACCESS_TOKEN');

        $mailer    = new Mail\Mailer($google);
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

        $this->expectException('DomainException');
        $mailer->send($message);
    }

}