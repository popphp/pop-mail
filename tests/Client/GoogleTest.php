<?php

namespace Pop\Mail\Test\Client;

use Google\Service\Gmail;
use Google\Service\Gmail\Message;
use Google\Service\Gmail\MessagePart;
use Google\Service\Gmail\MessagePartBody;
use GuzzleHttp\Psr7\Response as GuzzleResponse;
use Pop\Mail\Client;
use PHPUnit\Framework\TestCase;

class GoogleTestGmailStub extends Client\Google
{
    public $gmailStub;

    protected function gmailService(): Gmail
    {
        return $this->gmailStub;
    }
}

class GoogleTest extends TestCase
{

    protected function createGoogleWithGmailStub(): array
    {
        $google = new GoogleTestGmailStub();
        $google->createClient(__DIR__ . '/../tmp/my-google-app.json', 'test@gmail.com');
        $google->setToken('AUTH_TOKEN');
        $google->setTokenExpires(time() + 1000);

        $gmailStub         = $this->createStub(Gmail::class);
        $google->gmailStub = $gmailStub;

        return [$google, $gmailStub];
    }

    public function testGetMessages1()
    {
        [$google, $gmail] = $this->createGoogleWithGmailStub();

        $usersMessages = $this->createStub(Gmail\Resource\UsersMessages::class);
        $usersMessages->method('listUsersMessages')->willReturn([(object)['id' => 'MSG1']]);
        $gmail->users_messages = $usersMessages;

        $batch = $this->createStub(\Google\Http\Batch::class);
        $batch->method('execute')->willReturn([
            new GuzzleResponse(200, [], json_encode([
                'id'      => 'MSG1',
                'payload' => ['headers' => [['name' => 'Subject', 'value' => 'Test']]],
            ])),
        ]);
        $gmail->method('createBatch')->willReturn($batch);

        $messages = $google->getMessages();

        $this->assertArrayHasKey('MSG1', $messages);
        $this->assertEquals('Test', $messages['MSG1']['Subject']);
        $this->assertFalse($messages['MSG1']['attachments']);
    }

    public function testGetMessages1WithAttachmentsAndUnreadFlag()
    {
        [$google, $gmail] = $this->createGoogleWithGmailStub();

        $usersMessages = $this->createStub(Gmail\Resource\UsersMessages::class);
        $usersMessages->method('listUsersMessages')->willReturn([(object)['id' => 'MSG2']]);
        $gmail->users_messages = $usersMessages;

        $batch = $this->createStub(\Google\Http\Batch::class);
        $batch->method('execute')->willReturn([
            new GuzzleResponse(200, [], json_encode([
                'id'       => 'MSG2',
                'payload'  => [
                    'headers' => [['name' => 'Subject', 'value' => 'Has Attachment']],
                    'parts'   => [['filename' => 'a.pdf']],
                ],
                'labelIds' => ['UNREAD'],
            ])),
        ]);
        $gmail->method('createBatch')->willReturn($batch);

        $messages = $google->getMessages();

        $this->assertArrayHasKey('MSG2', $messages);
        $this->assertTrue($messages['MSG2']['attachments']);
        $this->assertTrue($messages['MSG2']['unread']);
    }

    public function testGetMessagesWithSearchFiltersBuildsExpectedQuery()
    {
        [$google, $gmail] = $this->createGoogleWithGmailStub();

        $usersMessages = $this->createMock(Gmail\Resource\UsersMessages::class);
        $usersMessages->expects($this->once())->method('listUsersMessages')
            ->with('test@gmail.com', $this->callback(function ($options) {
                return ($options['maxResults'] ?? null) === 25
                    && ($options['q'] ?? null) === 'in:Inbox is:unread in:sent after 10/01/2023 from:me@outlook.com';
            }))
            ->willReturn([]);
        $gmail->users_messages = $usersMessages;

        $batch = $this->createStub(\Google\Http\Batch::class);
        $batch->method('execute')->willReturn([]);
        $gmail->method('createBatch')->willReturn($batch);

        $messages = $google->getMessages('Inbox', [
            'unread'     => true,
            'sent after' => '2023-10-01',
            'from'       => 'me@outlook.com',
        ], 25);

        $this->assertEquals([], $messages);
    }

    public function testGetMessage()
    {
        [$google, $gmail] = $this->createGoogleWithGmailStub();

        $usersMessages = $this->createStub(Gmail\Resource\UsersMessages::class);
        $expectedMessage = new Message();
        $expectedMessage->setId('MSG1');
        $usersMessages->method('get')->willReturn($expectedMessage);
        $gmail->users_messages = $usersMessages;

        $message = $google->getMessage('MSG1');

        $this->assertSame($expectedMessage, $message);
    }

    public function testGetMessageRaw()
    {
        [$google, $gmail] = $this->createGoogleWithGmailStub();

        $rawMessage = new Message();
        $rawMessage->setRaw(strtr(base64_encode('raw message body'), '+/=', '-_.'));

        $usersMessages = $this->createStub(Gmail\Resource\UsersMessages::class);
        $usersMessages->method('get')->willReturn($rawMessage);
        $gmail->users_messages = $usersMessages;

        $raw = $google->getMessage('MSG1', true);

        $this->assertEquals('raw message body', $raw);
    }

    public function testGetAttachments()
    {
        [$google, $gmail] = $this->createGoogleWithGmailStub();

        $body = new MessagePartBody();
        $body->setAttachmentId('ATT1');
        $body->setSize(123);

        $part = new MessagePart();
        $part->setFilename('test.pdf');
        $part->setMimeType('application/pdf');
        $part->setBody($body);

        $payload = new MessagePart();
        $payload->setParts([$part]);

        $message = new Message();
        $message->setPayload($payload);

        $usersMessages = $this->createStub(Gmail\Resource\UsersMessages::class);
        $usersMessages->method('get')->willReturn($message);
        $gmail->users_messages = $usersMessages;

        $attachments = $google->getAttachments('MSG1');

        $this->assertArrayHasKey('ATT1', $attachments);
        $this->assertEquals('test.pdf', $attachments['ATT1']['filename']);
    }

    public function testGetAttachmentsNestedParts()
    {
        [$google, $gmail] = $this->createGoogleWithGmailStub();

        $nestedBody = new MessagePartBody();
        $nestedBody->setAttachmentId('ATT2');
        $nestedBody->setSize(456);

        $nestedPart = new MessagePart();
        $nestedPart->setFilename('nested.png');
        $nestedPart->setMimeType('image/png');
        $nestedPart->setBody($nestedBody);

        // Container part has no filename of its own, only nested sub-parts -
        // exercises the `else { foreach ($part->getParts() as $p) {...} }` branch.
        $containerPart = new MessagePart();
        $containerPart->setParts([$nestedPart]);

        $payload = new MessagePart();
        $payload->setParts([$containerPart]);

        $message = new Message();
        $message->setPayload($payload);

        $usersMessages = $this->createStub(Gmail\Resource\UsersMessages::class);
        $usersMessages->method('get')->willReturn($message);
        $gmail->users_messages = $usersMessages;

        $attachments = $google->getAttachments('MSG1');

        $this->assertArrayHasKey('ATT2', $attachments);
        $this->assertEquals('nested.png', $attachments['ATT2']['filename']);
    }

    public function testGetAttachment()
    {
        [$google, $gmail] = $this->createGoogleWithGmailStub();

        $encoded = strtr(base64_encode('attachment bytes'), '+/', '-_');

        $attachmentBody = new MessagePartBody();
        $attachmentBody->setData($encoded);

        $attachmentsResource = $this->createStub(Gmail\Resource\UsersMessagesAttachments::class);
        $attachmentsResource->method('get')->willReturn($attachmentBody);
        $gmail->users_messages_attachments = $attachmentsResource;

        $attachment = $google->getAttachment('MSG1', 'ATT1');

        $this->assertEquals('attachment bytes', $attachment);
    }

    public function testMarkAsRead()
    {
        [$google, $gmail] = $this->createGoogleWithGmailStub();

        $usersMessages = $this->createMock(Gmail\Resource\UsersMessages::class);
        $usersMessages->expects($this->once())->method('modify')
            ->with('test@gmail.com', 'MSG1', $this->callback(function ($request) {
                return $request->getRemoveLabelIds() === ['UNREAD'] && $request->getAddLabelIds() === null;
            }));
        $gmail->users_messages = $usersMessages;

        $result = $google->markAsRead('MSG1');

        $this->assertSame($google, $result);
    }

    public function testMarkAsUnread()
    {
        [$google, $gmail] = $this->createGoogleWithGmailStub();

        $usersMessages = $this->createMock(Gmail\Resource\UsersMessages::class);
        $usersMessages->expects($this->once())->method('modify')
            ->with('test@gmail.com', 'MSG1', $this->callback(function ($request) {
                return $request->getAddLabelIds() === ['UNREAD'] && $request->getRemoveLabelIds() === null;
            }));
        $gmail->users_messages = $usersMessages;

        $google->markAsUnread('MSG1');
    }

    public function testGetMessagesException()
    {
        $this->expectException('Pop\Mail\Client\Exception');
        $google = new Client\Google();
        $google->getMessages();
    }

    public function testGetMessageException()
    {
        $this->expectException('Pop\Mail\Client\Exception');
        $google = new Client\Google();
        $google->getMessage('123456789');
    }

    public function testGetAttachmentsException()
    {
        $this->expectException('Pop\Mail\Client\Exception');
        $google = new Client\Google();
        $google->getAttachments('123456789');
    }

    public function testGetAttachmentException()
    {
        $this->expectException('Pop\Mail\Client\Exception');
        $google = new Client\Google();
        $google->getAttachment('123456789', '123456798');
    }

    public function testMarkAsReadException()
    {
        $this->expectException('Pop\Mail\Client\Exception');
        $google = new Client\Google();
        $google->markAsRead('123456789');
    }

    public function testRequestTokenSuccessPathSetsTokenAndExpiry()
    {
        $google = new Client\Google();

        $googleClientMock = $this->createStub(\Google\Client::class);
        $googleClientMock->method('fetchAccessTokenWithAssertion')
            ->willReturn(['access_token' => 'NEW_ACCESS_TOKEN', 'expires_in' => 3600]);
        $google->setClient($googleClientMock);

        $before = time();
        $google->requestToken();

        $this->assertEquals('NEW_ACCESS_TOKEN', $google->getToken());
        $this->assertGreaterThanOrEqual($before + 3600, $google->getTokenExpires());
    }

}
