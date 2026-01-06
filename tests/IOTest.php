<?php

declare(strict_types=1);

namespace Tests;


use PHPUnit\Framework\TestCase;
use Recoil\IO;


class IOTest extends TestCase
{
    private IO $io;

    protected function setUp(): void
    {
        $this->io = new IO();
    }

    public function testSelectRegistersReadStream(): void
    {
        $stream = fopen('php://memory', 'r+');
        $callbackCalled = false;

        $cancel = $this->io->select(
            [$stream],
            [],
            function ($read, $write) use (&$callbackCalled) {
                $callbackCalled = true;
            }
        );

        $this->assertIsCallable($cancel);
        fclose($stream);
    }

    public function testSelectRegistersWriteStream(): void
    {
        $stream = fopen('php://memory', 'r+');
        $callbackCalled = false;

        $cancel = $this->io->select(
            [],
            [$stream],
            function ($read, $write) use (&$callbackCalled) {
                $callbackCalled = true;
            }
        );

        $this->assertIsCallable($cancel);
        fclose($stream);
    }

    public function testSelectRegistersMultipleStreams(): void
    {
        $stream1 = fopen('php://memory', 'r+');
        $stream2 = fopen('php://memory', 'r+');

        $cancel = $this->io->select(
            [$stream1],
            [$stream2],
            function ($read, $write) {}
        );

        $this->assertIsCallable($cancel);
        fclose($stream1);
        fclose($stream2);
    }

    public function testCancelRemovesSelectCallback(): void
    {
        $stream = fopen('php://memory', 'r+');

        $cancel = $this->io->select(
            [$stream],
            [],
            function ($read, $write) {}
        );

        $cancel();
        $result = $this->io->tick(0);

        $this->assertEquals(IO::INACTIVE, $result);
        fclose($stream);
    }

    public function testTickReturnsInactiveWhenNoStreams(): void
    {
        $result = $this->io->tick(0);
        $this->assertEquals(IO::INACTIVE, $result);
    }

    public function testTickReturnsActiveWhenStreamsReady(): void
    {
        $stream = fopen('php://memory', 'r+');
        fwrite($stream, 'test');
        rewind($stream);

        $this->io->select(
            [$stream],
            [],
            function ($read, $write) {}
        );

        $result = $this->io->tick(0);

        $this->assertEquals(IO::ACTIVE, $result);
        fclose($stream);
    }

    public function testTickInvokesCallbackForReadableStream(): void
    {
        $stream = fopen('php://memory', 'r+');
        fwrite($stream, 'test');
        rewind($stream);

        $callbackCalled = false;
        $readStreams = [];

        $this->io->select(
            [$stream],
            [],
            function ($read, $write) use (&$callbackCalled, &$readStreams) {
                $callbackCalled = true;
                $readStreams = $read;
            }
        );

        $this->io->tick(0);

        $this->assertTrue($callbackCalled);
        $this->assertContains($stream, $readStreams);
        fclose($stream);
    }

    public function testTickInvokesCallbackForWritableStream(): void
    {
        $stream = fopen('php://memory', 'r+');

        $callbackCalled = false;
        $writeStreams = [];

        $this->io->select(
            [],
            [$stream],
            function ($read, $write) use (&$callbackCalled, &$writeStreams) {
                $callbackCalled = true;
                $writeStreams = $write;
            }
        );

        $this->io->tick(0);

        $this->assertTrue($callbackCalled);
        $this->assertContains($stream, $writeStreams);
        fclose($stream);
    }

    public function testTickWithTimeout(): void
    {
        $start = microtime(true);
        $this->io->tick(10000); // 10ms
        $elapsed = (microtime(true) - $start) * 1000000;

        $this->assertGreaterThanOrEqual(10000, $elapsed);
    }

    public function testMultipleSelectsOnSameStream(): void
    {
        $stream = fopen('php://memory', 'r+');
        fwrite($stream, 'test');
        rewind($stream);

        $callback1Called = false;
        $callback2Called = false;

        $this->io->select([$stream], [], function () use (&$callback1Called) {
            $callback1Called = true;
        });

        $this->io->select([$stream], [], function () use (&$callback2Called) {
            $callback2Called = true;
        });

        $this->io->tick(0);

        $this->assertTrue($callback1Called);
        $this->assertTrue($callback2Called);
        fclose($stream);
    }

    public function testCancelRemovesOnlySpecificSelect(): void
    {
        $stream = fopen('php://memory', 'r+');
        fwrite($stream, 'test');
        rewind($stream);

        $callback1Called = false;
        $callback2Called = false;

        $cancel1 = $this->io->select([$stream], [], function () use (&$callback1Called) {
            $callback1Called = true;
        });

        $this->io->select([$stream], [], function () use (&$callback2Called) {
            $callback2Called = true;
        });

        $cancel1();
        $this->io->tick(0);

        $this->assertFalse($callback1Called);
        $this->assertTrue($callback2Called);
        fclose($stream);
    }
}