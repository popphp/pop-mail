<?php

namespace Pop\Mail\Test\Transport\Smtp;

use Pop\Mail\Message;
use Pop\Mail\Transport\Smtp\AbstractSmtp;
use Pop\Mail\Transport\Smtp\Stream\BufferInterface;
use Pop\Mail\Transport\Smtp\Stream\Byte\InputInterface;
use PHPUnit\Framework\TestCase;

/**
 * Test double that counts getBodyContent() invocations, so tests can assert
 * AbstractSmtp::sendBcc() renders the body once and reuses it across recipients
 */
class SpyMessage extends Message
{
    /**
     * Number of times getBodyContent() was called
     * @var int
     */
    public int $bodyContentCalls = 0;

    /**
     * Count the call, then delegate to the real implementation
     *
     * @return ?string
     */
    public function getBodyContent(): ?string
    {
        $this->bodyContentCalls++;
        return parent::getBodyContent();
    }
}

/**
 * In-memory BufferInterface implementation that returns a canned queue of
 * SMTP responses and records everything written to it
 */
class FakeSmtpBuffer implements BufferInterface
{
    /**
     * Queue of canned SMTP responses, consumed in order by readLine()
     * @var array
     */
    public array $responses = [];

    /**
     * Everything written to this buffer via write()
     * @var string
     */
    public string $written   = '';

    /**
     * No-op: nothing to initialize for the in-memory buffer
     *
     * @param  array $params
     * @return void
     */
    public function initialize(array $params): void {}

    /**
     * No-op: pretend TLS negotiation always succeeds
     *
     * @return bool
     */
    public function startTls(): bool { return true; }

    /**
     * No-op: params are not tracked by this fake
     *
     * @param  string $param
     * @param  mixed  $value
     * @return void
     */
    public function setParam(string $param, mixed $value): void {}

    /**
     * No-op: nothing to tear down for the in-memory buffer
     *
     * @return void
     */
    public function terminate(): void {}

    /**
     * No-op: write translations are not applied by this fake
     *
     * @param  array $replacements
     * @return mixed
     */
    public function setWriteTranslations(array $replacements) {}

    /**
     * Return the next canned response, defaulting to a generic 250 OK
     *
     * @param  int|string $sequence
     * @return string
     */
    public function readLine(int|string $sequence): string
    {
        return array_shift($this->responses) ?? "250 OK\r\n";
    }

    /**
     * Append the given bytes to the recorded written output
     *
     * @param  string $bytes
     * @return mixed
     */
    public function write(string $bytes): mixed
    {
        $this->written .= $bytes;
        return 1;
    }

    /**
     * No-op: nothing to commit for the in-memory buffer
     *
     * @return void
     */
    public function commit(): void {}

    /**
     * No-op: this fake does not support binding an input stream
     *
     * @param  InputInterface $is
     * @return void
     */
    public function bind(InputInterface $is): void {}

    /**
     * No-op: this fake does not support unbinding an input stream
     *
     * @param  InputInterface $is
     * @return void
     */
    public function unbind(InputInterface $is): void {}

    /**
     * No-op: there are no internal buffers to flush in this fake
     *
     * @return void
     */
    public function flushBuffers(): void {}

    /**
     * No-op: this fake does not support reading raw bytes back
     *
     * @param  int|string $length
     * @return string|bool
     */
    public function read(int|string $length): string|bool { return false; }

    /**
     * No-op: this fake does not track a read pointer
     *
     * @param  int|string $byteOffset
     * @return void
     */
    public function setReadPointer(int|string $byteOffset): void {}
}

/**
 * Minimal concrete AbstractSmtp subclass for testing, since AbstractSmtp
 * is abstract and only requires a buffer-params implementation
 */
class TestableSmtp extends AbstractSmtp
{
    /**
     * Return an empty buffer params array; the FakeSmtpBuffer ignores it
     *
     * @return array
     */
    protected function getBufferParams(): array
    {
        return [];
    }
}

class AbstractSmtpTest extends TestCase
{

    /**
     * Build a FakeSmtpBuffer pre-loaded with the response sequence for a
     * greeting + HELO followed by one MAIL FROM/RCPT TO/DATA/end-of-data
     * cycle per Bcc recipient
     *
     * @param  int $bccCount
     * @return FakeSmtpBuffer
     */
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
