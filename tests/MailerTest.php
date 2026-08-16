<?php

namespace Pop\Mail\Test;

use Pop\Mail\Mailer;
use Pop\Mail\Message;
use Pop\Mail\Queue;
use Pop\Mail\Transport;
use Pop\Mail\Transport\TransportInterface;
use Pop\Mail\Transport\BatchTransportInterface;
use PHPUnit\Framework\TestCase;

class SpyTransport implements TransportInterface
{
    public array $sent = [];

    public function send(Message $message): mixed
    {
        $this->sent[] = $message;
        return true;
    }
}

/**
 * Test double transport that also implements BatchTransportInterface, so
 * tests can assert Mailer prefers sendBatch() over per-message send()
 */
class SpyBatchTransport implements TransportInterface, BatchTransportInterface
{
    /**
     * Messages passed to send() or sendBatch()
     * @var array
     */
    public array $sent      = [];

    /**
     * Number of times send() was called
     * @var int
     */
    public int   $sendCalls = 0;

    /**
     * Number of times sendBatch() was called
     * @var int
     */
    public int   $batchCalls = 0;

    /**
     * Record a single sent message
     *
     * @param  Message $message
     * @return mixed
     */
    public function send(Message $message): mixed
    {
        $this->sendCalls++;
        $this->sent[] = $message;
        return true;
    }

    /**
     * Record a batch of sent messages
     *
     * @param  Message[] $messages
     * @return int
     */
    public function sendBatch(array $messages): int
    {
        $this->batchCalls++;
        $this->sent = array_merge($this->sent, $messages);
        return count($messages);
    }
}

class MailerTest extends TestCase
{

    public function testConstructor()
    {
        $mailer = new Mailer(new Transport\Sendmail());
        $this->assertInstanceOf('Pop\Mail\Mailer', $mailer);
        $this->assertInstanceOf('Pop\Mail\Transport\Sendmail', $mailer->transport());
    }

    public function testDefaultFrom()
    {
        $mailer = new Mailer(new Transport\Sendmail(), 'root@localhost');
        $this->assertTrue($mailer->hasDefaultFrom());
        $this->assertEquals('root@localhost', $mailer->getDefaultFrom());

        $mailer->setDefaultFrom('other@localhost');
        $this->assertEquals('other@localhost', $mailer->getDefaultFrom());
    }

    public function testSendDelegatesToTransport()
    {
        $spy     = new SpyTransport();
        $mailer  = new Mailer($spy);
        $message = new Message('Test Subject!');
        $message->setTo('root@localhost')
            ->setFrom('sender@localhost')
            ->addText('Hey, this is a test!');

        $result = $mailer->send($message);

        $this->assertTrue($result);
        $this->assertCount(1, $spy->sent);
        $this->assertSame($message, $spy->sent[0]);
    }

    public function testSendAppliesDefaultFromWhenMessageHasNone()
    {
        $spy     = new SpyTransport();
        $mailer  = new Mailer($spy, 'default@localhost');
        $message = new Message('Test Subject!');
        $message->setTo('root@localhost')
            ->addText('Hey, this is a test!');

        $this->assertFalse($message->hasFrom());
        $mailer->send($message);

        $this->assertTrue($message->hasFrom());
        $this->assertArrayHasKey('default@localhost', $message->getFrom());
    }

    public function testSendDoesNotOverrideExistingFrom()
    {
        $spy     = new SpyTransport();
        $mailer  = new Mailer($spy, 'default@localhost');
        $message = new Message('Test Subject!');
        $message->setTo('root@localhost')
            ->setFrom('explicit@localhost')
            ->addText('Hey, this is a test!');

        $mailer->send($message);

        $this->assertArrayHasKey('explicit@localhost', $message->getFrom());
        $this->assertArrayNotHasKey('default@localhost', $message->getFrom());
    }

    public function testSendFromQueueDelegatesEachPreparedMessage()
    {
        $spy    = new SpyTransport();
        $mailer = new Mailer($spy, 'default@localhost');

        $template = new Message('Hello [{name}]!');
        $template->addText('Hi [{name}]!');

        $queue = new Queue();
        $queue->addMessage($template);
        $queue->addRecipient(['email' => 'one@localhost', 'name' => 'One']);
        $queue->addRecipient(['email' => 'two@localhost', 'name' => 'Two']);

        $sent = $mailer->sendFromQueue($queue);

        $this->assertEquals(2, $sent);
        $this->assertCount(2, $spy->sent);
        $this->assertArrayHasKey('default@localhost', $spy->sent[0]->getFrom());
        $this->assertArrayHasKey('default@localhost', $spy->sent[1]->getFrom());
    }

    public function testSendFromQueue()
    {
        $mailer = new Mailer(new Transport\Sendmail());
        $this->assertEquals(0, $mailer->sendFromQueue(new Queue()));
    }

    public function testSendFromQueueUsesBatchTransportWhenAvailable()
    {
        $spy    = new SpyBatchTransport();
        $mailer = new Mailer($spy, 'default@localhost');

        $template = new Message('Hello [{name}]!');
        $template->addText('Hi [{name}]!');

        $queue = new Queue();
        $queue->addMessage($template);
        $queue->addRecipient(['email' => 'one@localhost', 'name' => 'One']);
        $queue->addRecipient(['email' => 'two@localhost', 'name' => 'Two']);

        $sent = $mailer->sendFromQueue($queue);

        $this->assertEquals(2, $sent);
        $this->assertEquals(1, $spy->batchCalls);
        $this->assertEquals(0, $spy->sendCalls);
        $this->assertCount(2, $spy->sent);
    }

    public function testSendFromDirUsesBatchTransportWhenAvailable()
    {
        $dir = sys_get_temp_dir() . '/pop-mail-test-queue-' . uniqid();
        mkdir($dir);

        $message1 = new Message('Test Subject 1!');
        $message1->setTo('root@localhost')->addText('Hey!');
        $message1->save($dir . '/message1.msg');

        $message2 = new Message('Test Subject 2!');
        $message2->setTo('root@localhost')->addText('Hey again!');
        $message2->save($dir . '/message2.msg');

        try {
            $spy    = new SpyBatchTransport();
            $mailer = new Mailer($spy, 'default@localhost');
            $sent   = $mailer->sendFromDir($dir);

            $this->assertEquals(2, $sent);
            $this->assertEquals(1, $spy->batchCalls);
            $this->assertEquals(0, $spy->sendCalls);
            $this->assertCount(2, $spy->sent);
        } finally {
            unlink($dir . '/message1.msg');
            unlink($dir . '/message2.msg');
            rmdir($dir);
        }
    }

    public function testSendFromDirDelegatesEachSavedMessage()
    {
        $dir = sys_get_temp_dir() . '/pop-mail-test-queue-' . uniqid();
        mkdir($dir);

        $message1 = new Message('Test Subject 1!');
        $message1->setTo('root@localhost')->addText('Hey!');
        $message1->save($dir . '/message1.msg');

        $message2 = new Message('Test Subject 2!');
        $message2->setTo('root@localhost')->addText('Hey again!');
        $message2->save($dir . '/message2.msg');

        try {
            $spy    = new SpyTransport();
            $mailer = new Mailer($spy, 'default@localhost');
            $sent   = $mailer->sendFromDir($dir);

            $this->assertEquals(2, $sent);
            $this->assertCount(2, $spy->sent);
        } finally {
            unlink($dir . '/message1.msg');
            unlink($dir . '/message2.msg');
            rmdir($dir);
        }
    }

    public function testSendFromDir()
    {
        $mailer = new Mailer(new Transport\Sendmail());
        $this->assertEquals(0, $mailer->sendFromDir(__DIR__ . '/tmp/queue'));
    }

    public function testSendFromDirException()
    {
        $this->expectException('Pop\Mail\Exception');
        $mailer = new Mailer(new Transport\Sendmail());
        $this->assertEquals(0, $mailer->sendFromDir(__DIR__ . '/tmp/bad'));
    }

}
