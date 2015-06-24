<?php

namespace Pop\Mail\Test;

use Pop\Mail\Queue;

class QueueTest extends \PHPUnit_Framework_TestCase
{

    public function testConstructor()
    {
        $queue = new Queue('test@test.com');
        $queue->add('another@test.com', 'Another Person');
        $queue->addRecipients('onemore@test.com');
        $this->assertEquals(3, count($queue));
        $this->assertInstanceOf('Pop\Mail\Queue', $queue);
    }

    public function testAddRecipientsNotValidAddressException1()
    {
        $this->setExpectedException('Pop\Mail\Exception');
        $queue = new Queue();
        $queue->addRecipients('bad');
    }

    public function testAddRecipientsNotValidAddressException2()
    {
        $this->setExpectedException('Pop\Mail\Exception');
        $queue = new Queue();
        $queue->addRecipients(['email' => 'bad']);
    }

    public function testAddRecipientsNotValidAddressException3()
    {
        $this->setExpectedException('Pop\Mail\Exception');
        $queue = new Queue();
        $queue->addRecipients([['email' => 'bad']]);
    }

    public function testAddRecipientsNotValidAddressException4()
    {
        $this->setExpectedException('Pop\Mail\Exception');
        $queue = new Queue();
        $queue->addRecipients(['bad']);
    }

    public function testAddRecipientsNotValidAddressException5()
    {
        $this->setExpectedException('Pop\Mail\Exception');
        $queue = new Queue();
        $queue->addRecipients([['bad']]);
    }

}
