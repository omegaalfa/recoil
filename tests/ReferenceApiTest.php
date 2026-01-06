<?php

declare(strict_types=1);

namespace Tests;


use PHPUnit\Framework\TestCase;
use Recoil\EventQueue;
use Recoil\IO;
use Recoil\ReferenceApi;
use Recoil\System\SystemKernel;
use Recoil\System\SystemStrand;

class ReferenceApiTest extends TestCase
{
    private EventQueue $events;
    private IO $io;
    private ReferenceApi $api;
    private SystemStrand $strand;
    private SystemKernel $kernel;

    protected function setUp(): void
    {
        $this->events = $this->createMock(EventQueue::class);
        $this->io = $this->createMock(IO::class);
        $this->api = new ReferenceApi($this->events, $this->io);
        $this->kernel = $this->createMock(SystemKernel::class);
        $this->strand = $this->createMock(SystemStrand::class);
    }

    public function testCooperate(): void
    {
        $terminator = function () {};
        
        $this->events->expects($this->once())
            ->method('schedule')
            ->with(0, $this->isType('callable'))
            ->willReturn($terminator);

        $this->strand->expects($this->once())
            ->method('setTerminator')
            ->with($terminator);

        $result = $this->api->cooperate($this->strand);
        $this->assertNull($result);
    }

    public function testSleep(): void
    {
        $interval = 1.5;
        $terminator = function () {};
        
        $this->events->expects($this->once())
            ->method('schedule')
            ->with($interval, $this->isType('callable'))
            ->willReturn($terminator);

        $this->strand->expects($this->once())
            ->method('setTerminator')
            ->with($terminator);

        $result = $this->api->sleep($this->strand, $interval);
        $this->assertNull($result);
    }

    public function testTimeout(): void
    {
        $timeout = 2.0;
        $coroutine = function () { yield; };
        $substrand = $this->createMock(SystemStrand::class);

        $this->strand->expects($this->once())
            ->method('kernel')
            ->willReturn($this->kernel);

        $this->kernel->expects($this->once())
            ->method('execute')
            ->with($coroutine)
            ->willReturn($substrand);

        $result = $this->api->timeout($this->strand, $timeout, $coroutine);
        $this->assertNull($result);
    }

    public function testRead(): void
    {
        $stream = fopen('php://memory', 'r+');
        fwrite($stream, 'test data');
        rewind($stream);
        
        $done = function () {};
        
        $this->io->expects($this->once())
            ->method('select')
            ->with([$stream], [], $this->isType('callable'))
            ->willReturn($done);

        $this->strand->expects($this->once())
            ->method('setTerminator')
            ->with($done);

        $result = $this->api->read($this->strand, $stream, 1, 100);
        $this->assertNull($result);

        fclose($stream);
    }

    public function testSelect(): void
    {
        $readStream = fopen('php://memory', 'r');
        $writeStream = fopen('php://memory', 'w');
        $done = function () {};

        $this->io->expects($this->once())
            ->method('select')
            ->with([$readStream], [$writeStream], $this->isType('callable'))
            ->willReturn($done);

        $this->strand->expects($this->once())
            ->method('setTerminator')
            ->with($done);

        $result = $this->api->select($this->strand, [$readStream], [$writeStream]);
        $this->assertNull($result);

        fclose($readStream);
        fclose($writeStream);
    }

    public function testWrite(): void
    {
        $stream = fopen('php://memory', 'w');
        $buffer = 'test data';
        $length = strlen($buffer);
        $done = function () {};

        $this->io->expects($this->once())
            ->method('select')
            ->with([], [$stream], $this->isType('callable'))
            ->willReturn($done);

        $this->strand->expects($this->once())
            ->method('setTerminator')
            ->with($done);

        $result = $this->api->write($this->strand, $stream, $buffer, $length);
        $this->assertNull($result);

        fclose($stream);
    }

    public function testWriteWithEmptyBuffer(): void
    {
        $stream = fopen('php://memory', 'w');
        
        $this->strand->expects($this->once())
            ->method('send');

        $result = $this->api->write($this->strand, $stream, '', 0);
        $this->assertNull($result);

        fclose($stream);
    }

    public function testWriteWithLengthGreaterThanBuffer(): void
    {
        $stream = fopen('php://memory', 'w');
        $buffer = 'test';
        $done = function () {};

        $this->io->expects($this->once())
            ->method('select')
            ->willReturn($done);

        $this->strand->expects($this->once())
            ->method('setTerminator');

        $result = $this->api->write($this->strand, $stream, $buffer, 100);
        $this->assertNull($result);

        fclose($stream);
    }
}