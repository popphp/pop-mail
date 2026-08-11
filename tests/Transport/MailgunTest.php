<?php

namespace Pop\Mail\Test\Transport;

use Pop\Http\Client\Handler\Mock;
use Pop\Http\Client\Response;
use Pop\Mail\Message;
use Pop\Mail\Transport;
use PHPUnit\Framework\TestCase;

class MailgunTest extends TestCase
{

    public function testMailgunSendPostsExpectedFieldsAndReturnsParsedResponse()
    {
        $transport = new Transport\Mailgun(json_encode(['api_url' => 'https://api.mailgun.net/v3/example.com/messages', 'api_key' => 'MY_API_KEY']));

        $mock = new Mock();
        $mock->queue(new Response([
            'code'    => 200,
            'headers' => ['Content-Type' => 'application/json'],
            'body'    => json_encode(['id' => '<20260808.abc@example.com>', 'message' => 'Queued. Thank you.']),
        ]));
        $transport->getClient()->setHandler($mock);

        $message = new Message('Test Subject!');
        $message->setTo('root@localhost')
            ->setFrom('root@localhost')
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
        $parsedResult = $result->json();
        $this->assertIsArray($parsedResult);
        $this->assertEquals('Queued. Thank you.', $parsedResult['message']);

        $sentRequest = $mock->getLastRequest();
        $sentData    = $sentRequest->getData();

        $this->assertEquals('CustomValue', $sentData->getData('h:X-Custom-Header'));
        $this->assertEquals('Hey, this is a test!', $sentData->getData('text'));

        $attachmentFile = $sentData->getData('attachment[0]');
        $this->assertInstanceOf('CURLFile', $attachmentFile);
        $this->assertEquals('application/pdf', $attachmentFile->mime);
    }

    public function testMailgunMissingOptionsThrows()
    {
        $this->expectException('Pop\Mail\Transport\Exception');
        $transport = new Transport\Mailgun([]);
    }

}
