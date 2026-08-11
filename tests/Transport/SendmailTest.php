<?php

namespace Pop\Mail\Transport {
    function mail(string $to, string $subject, string $message, mixed $headers = null, mixed $params = null): bool
    {
        \Pop\Mail\Test\Transport\SendmailTest::$lastMailArgs = [
            'to'      => $to,
            'subject' => $subject,
            'message' => $message,
            'headers' => $headers,
            'params'  => $params,
        ];
        return \Pop\Mail\Test\Transport\SendmailTest::$mailReturnValue;
    }
}

namespace Pop\Mail\Test\Transport {

    use Pop\Mail;
    use Pop\Mail\Transport;
    use PHPUnit\Framework\TestCase;

    class SendmailTest extends TestCase
    {
        public static array $lastMailArgs   = [];
        public static bool  $mailReturnValue = true;

        protected function setUp(): void
        {
            self::$lastMailArgs    = [];
            self::$mailReturnValue = true;
        }

        public function testSendmail()
        {
            $transport = new Transport\Sendmail('-f');
            $this->assertInstanceOf('Pop\Mail\Transport\Sendmail', $transport);
            $this->assertEquals('-f', $transport->getParams());
        }

        public function testSendWithoutParams()
        {
            $transport = new Transport\Sendmail();
            $message   = new Mail\Message('Test Subject!');
            $message->setTo('root@localhost')
                ->setFrom('sender@localhost')
                ->addText('Hey, this is a test!');

            $result = $transport->send($message);

            $this->assertTrue($result);
            $this->assertEquals('root@localhost', self::$lastMailArgs['to']);
            $this->assertEquals('Test Subject!', self::$lastMailArgs['subject']);
            $this->assertStringContainsString('Hey, this is a test!', self::$lastMailArgs['message']);
            $this->assertNull(self::$lastMailArgs['params']);
        }

        public function testSendWithParams()
        {
            $transport = new Transport\Sendmail('-f sender@localhost');
            $message   = new Mail\Message('Test Subject!');
            $message->setTo('root@localhost')
                ->setFrom('sender@localhost')
                ->addText('Hey, this is a test!');

            self::$mailReturnValue = false;
            $result                = $transport->send($message);

            $this->assertFalse($result);
            $this->assertEquals('-f sender@localhost', self::$lastMailArgs['params']);
        }

        public function testSendMultipartComputesBoundaryAndContentTypeBeforeHeaders()
        {
            $transport = new Transport\Sendmail();
            $message   = new Mail\Message('Test Subject!');
            $message->setTo('root@localhost')
                ->setFrom('sender@localhost')
                ->addText('Hey, this is a test!')
                ->addHtml('<html><body><p>Hey!</p></body></html>');

            $result = $transport->send($message);

            $this->assertTrue($result);
            $this->assertStringContainsString('Content-Type: multipart/', self::$lastMailArgs['headers']);
            $this->assertStringContainsString('MIME-Version:', self::$lastMailArgs['headers']);
            $this->assertStringNotContainsString('Subject:', self::$lastMailArgs['headers']);
            $this->assertStringNotContainsString('To:', self::$lastMailArgs['headers']);
        }

    }
}
