<?php

namespace Pop\Mail\Test;

use Pop\Mail\Message;
use PHPUnit\Framework\TestCase;

class MessageAddressTest extends TestCase
{

    public function testSetToSingleAddress()
    {
        $message = new Message();
        $message->setTo('test@domain.com');
        $this->assertTrue($message->hasTo());
        $this->assertEquals(['test@domain.com' => null], $message->getTo());
    }

    public function testSetToWithDisplayName()
    {
        $message = new Message();
        $message->setTo('John Doe <john@doe.com>');
        $this->assertEquals(['john@doe.com' => 'John Doe'], $message->getTo());
    }

    public function testSetToMultipleAddresses()
    {
        $message = new Message();
        $message->setTo('John Doe <john@doe.com>, Jane Doe <jane@doe.com>');
        $emails = $message->getTo();
        $this->assertEquals(2, count($emails));
        $this->assertEquals('John Doe', $emails['john@doe.com']);
        $this->assertEquals('Jane Doe', $emails['jane@doe.com']);
    }

    public function testSetToWithQuotedDisplayNameContainingComma()
    {
        $message = new Message();
        $message->setTo('"Doe, John" <john@doe.com>');
        $emails = $message->getTo();
        $this->assertEquals(1, count($emails));
        $this->assertEquals('Doe, John', $emails['john@doe.com']);
    }

    public function testSetToAlsoSetsHeader()
    {
        $message = new Message();
        $message->setTo('test@domain.com');
        $this->assertTrue($message->hasHeader('To'));
    }

    public function testSetCc()
    {
        $message = new Message();
        $message->setCc('test@domain.com');
        $this->assertTrue($message->hasCc());
        $this->assertEquals(['test@domain.com' => null], $message->getCc());
    }

    public function testSetBcc()
    {
        $message = new Message();
        $message->setBcc('test@domain.com');
        $this->assertTrue($message->hasBcc());
        $this->assertEquals(['test@domain.com' => null], $message->getBcc());
    }

    public function testSetFrom()
    {
        $message = new Message();
        $message->setFrom('test@domain.com');
        $this->assertTrue($message->hasFrom());
        $this->assertEquals(['test@domain.com' => null], $message->getFrom());
    }

    public function testSetReplyTo()
    {
        $message = new Message();
        $message->setReplyTo('test@domain.com');
        $this->assertTrue($message->hasReplyTo());
        $this->assertEquals(['test@domain.com' => null], $message->getReplyTo());
    }

    public function testSetSender()
    {
        $message = new Message();
        $message->setSender('test@domain.com');
        $this->assertTrue($message->hasSender());
        $this->assertEquals(['test@domain.com' => null], $message->getSender());
    }

    public function testSetReturnPath()
    {
        $message = new Message();
        $message->setReturnPath('test@domain.com');
        $this->assertTrue($message->hasReturnPath());
        $this->assertEquals(['test@domain.com' => null], $message->getReturnPath());
    }

    public function testSetToWithArrayAddress()
    {
        $message = new Message();
        $message->setTo(['test@test.com' => 'Test Person']);
        $this->assertEquals(['test@test.com' => 'Test Person'], $message->getTo());
    }

    public function testSetToWithEmptyArrayClearsAddresses()
    {
        $message = new Message();
        $message->setTo('test@test.com');
        $message->setTo([]);
        $this->assertEquals([], $message->getTo());
    }

    public function testSetBccWithArrayMatchesAbstractSmtpUsage()
    {
        // Mirrors Transport/Smtp/AbstractSmtp.php's actual per-recipient Bcc pattern
        $message = new Message();
        $message->setBcc(['forward@test.com' => 'Forward Name']);
        $this->assertEquals(['forward@test.com' => 'Forward Name'], $message->getBcc());
        $message->setBcc([]);
        $this->assertEquals([], $message->getBcc());
    }

}
