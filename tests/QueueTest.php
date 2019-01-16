<?php

namespace Pop\Mail\Test;

use Pop\Mail\Queue;
use Pop\Mail\Message;
use PHPUnit\Framework\TestCase;

class QueueTest extends TestCase
{

    public function testConstructor()
    {
        $queue1 = new Queue([
            'email'   => 'me@domain.com',
            'name'    => 'My Name',
            'company' => 'My Company'
        ], new Message('Hello World'));
        $this->assertInstanceOf('Pop\Mail\Queue', $queue1);
        $this->assertEquals(1, count($queue1->getMessages()));
        $this->assertEquals(1, count($queue1->getRecipients()));

        $queue2 = new Queue([[
            'email'   => 'another@domain.com',
            'name'    => 'Another Name',
            'company' => 'Another Company'
        ]]);
        $this->assertEquals(1, count($queue2->getRecipients()));
        $this->assertEquals(0, count($queue2->getPreparedMessages()));
    }

    public function testAddRecipients()
    {
        $queue = new Queue();
        $queue->addRecipient([
            'email'   => 'me@domain.com',
            'name'    => 'My Name',
            'company' => 'My Company'
        ]);
        $queue->addRecipients([[
            'email'   => 'another@domain.com',
            'name'    => 'Another Name',
            'company' => 'Another Company'
        ]]);
        $this->assertEquals(2, count($queue->getRecipients()));
    }

    public function testAddMessages()
    {
        $queue = new Queue();
        $queue->addMessage(new Message('Hello World'));
        $queue->addMessages([new Message('Hello')]);
        $this->assertEquals(2, count($queue->getMessages()));
    }

    public function testSetRecipients()
    {
        $queue = new Queue();
        $queue->addRecipient([
            'email'   => 'me@domain.com',
            'name'    => 'My Name',
            'company' => 'My Company'
        ]);
        $queue->addRecipients([[
            'email'   => 'another@domain.com',
            'name'    => 'Another Name',
            'company' => 'Another Company'
        ]]);

        $queue->setRecipients([[
            'email'   => 'me@domain.com',
            'name'    => 'My Name',
            'company' => 'My Company'
        ]]);
        $this->assertEquals(1, count($queue->getRecipients()));
    }

    public function testAddRecipientsException()
    {
        $this->expectException('Pop\Mail\Exception');
        $queue = new Queue();
        $queue->addRecipient([
            'name'    => 'My Name',
            'company' => 'My Company'
        ]);
    }

    public function testSetMessages()
    {
        $queue = new Queue();
        $queue->addMessage(new Message('Hello World'));
        $queue->addMessages([new Message('Hello')]);

        $queue->setMessages([new Message('Hello World')]);
        $this->assertEquals(1, count($queue->getMessages()));
    }

    public function testPrepare()
    {
        $queue = new Queue();
        $queue->addRecipient([
            'email'   => 'me@domain.com',
            'name'    => 'My Name',
            'company' => 'My Company'
        ]);
        $queue->addRecipient([
            'email'   => 'another@domain.com',
            'name'    => 'Another Name',
            'company' => 'Another Company'
        ]);

        $message = new Message('Hello [{name}]!');
        $message->setFrom('noreply@domain.com');
        $message->setBody(
            <<<TEXT
            How are you doing? Your [{company}] is great!
TEXT
        );

        $queue->addMessage($message);
        $this->assertEquals(2, count($queue->prepare()));
    }
}