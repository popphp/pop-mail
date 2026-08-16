<?php

namespace Pop\Mail\Test\Transport\Smtp;

use Pop\Mail\Message;
use Pop\Mail\Transport\Smtp\AbstractSmtp;
use Pop\Mail\Transport\Smtp\Stream\BufferInterface;
use Pop\Mail\Transport\Smtp\Stream\Byte\InputInterface;
use PHPUnit\Framework\TestCase;

class SpyMessage extends Message
{
    public int $bodyContentCalls = 0;

    public function getBodyContent(): ?string
    {
        $this->bodyContentCalls++;
        return parent::getBodyContent();
    }
}

class FakeSmtpBuffer implements BufferInterface
{
    public array $responses = [];
    public string $written   = '';

    public function initialize(array $params): void {}
    public function startTls(): bool { return true; }
    public function setParam(string $param, mixed $value): void {}
    public function terminate(): void {}
    public function setWriteTranslations(array $replacements) {}

    public function readLine(int|string $sequence): string
    {
        return array_shift($this->responses) ?? "250 OK\r\n";
    }

    public function write(string $bytes): mixed
    {
        $this->written .= $bytes;
        return 1;
    }

    public function commit(): void {}
    public function bind(InputInterface $is): void {}
    public function unbind(InputInterface $is): void {}
    public function flushBuffers(): void {}
    public function read(int|string $length): string|bool { return false; }
    public function setReadPointer(int|string $byteOffset): void {}
}

class TestableSmtp extends AbstractSmtp
{
    protected function getBufferParams(): array
    {
        return [];
    }
}

class AbstractSmtpTest extends TestCase
{

    private function makeBuffer(int $bccCount): FakeSmtpBuffer
    {
        $buffer = new FakeSmtpBuffer();
        // greeting, HELO, then per-Bcc recipient: MAIL FROM, RCPT TO, DATA, end-of-data
        $buffer->responses = array_merge(
            ["220 ready\r\n", "250 HELO\r\n"],
            array_merge(...array_fill(0, max($bccCount, 1), ["250 OK\r\n", "250 OK\r\n", "354 Go ahead\r\n", "250 OK\r\n"]))
        );
        return $buffer;
    }

    public function testSendBccRendersBodyOnceForMultipleRecipients()
    {
        $buffer  = $this->makeBuffer(3);
        $message = new SpyMessage('Test Subject');
        $message->setFrom('sender@localhost');
        $message->setBcc([
            'one@localhost'   => 'One',
            'two@localhost'   => 'Two',
            'three@localhost' => 'Three',
        ]);
        $message->addText('Hello there');

        $transport = new TestableSmtp($buffer);
        $sent      = $transport->send($message);

        $this->assertEquals(3, $sent);
        $this->assertEquals(1, $message->bodyContentCalls);
        $this->assertEquals(3, substr_count($buffer->written, 'Hello there'));
    }

}
