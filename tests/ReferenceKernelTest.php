<?php

declare(strict_types=1);

namespace Tests;


use PHPUnit\Framework\TestCase;
use Recoil\Api\Api;
use Recoil\EventQueue;
use Recoil\IO;
use Recoil\ReferenceKernel;
use Recoil\Strand\Strand;

class ReferenceKernelTest extends TestCase
{
    public function testConstructor(): void
    {
        $events = $this->createMock(EventQueue::class);
        $io = $this->createMock(IO::class);
        $api = $this->createMock(Api::class);

        $kernel = new ReferenceKernel($events, $io, $api);

        $this->assertInstanceOf(ReferenceKernel::class, $kernel);
    }

    public function testCreate(): void
    {
        $kernel = ReferenceKernel::create();

        $this->assertInstanceOf(ReferenceKernel::class, $kernel);
    }

    public function testCreateWithArguments(): void
    {
        $kernel = ReferenceKernel::create(['some' => 'arguments']);

        $this->assertInstanceOf(ReferenceKernel::class, $kernel);
    }

    public function testExecute(): void
    {
        $kernel = ReferenceKernel::create();
        $coroutine = function () {
            yield 'test';
        };

        $strand = $kernel->execute($coroutine);

        $this->assertInstanceOf(Strand::class, $strand);
    }

    public function testExecuteMultipleStrands(): void
    {
        $kernel = ReferenceKernel::create();
        
        $strand1 = $kernel->execute(function () { yield 1; });
        $strand2 = $kernel->execute(function () { yield 2; });

        $this->assertInstanceOf(Strand::class, $strand1);
        $this->assertInstanceOf(Strand::class, $strand2);
        $this->assertNotSame($strand1, $strand2);
    }

    public function testExecuteIncrementsStrandId(): void
    {
        $events = $this->createMock(EventQueue::class);
        $io = $this->createMock(IO::class);
        $api = $this->createMock(Api::class);

        $events->expects($this->exactly(2))
            ->method('schedule')
            ->willReturn(function() {});

        $kernel = new ReferenceKernel($events, $io, $api);

        $strand1 = $kernel->execute(function () { yield; });
        $strand2 = $kernel->execute(function () { yield; });

        $this->assertNotSame($strand1, $strand2);
    }

    public function testExecuteSchedulesStrandStart(): void
    {
        $events = $this->createMock(EventQueue::class);
        $io = $this->createMock(IO::class);
        $api = $this->createMock(Api::class);

        $events->expects($this->once())
            ->method('schedule')
            ->with(0, $this->isType('callable'))
            ->willReturn(function() {});

        $kernel = new ReferenceKernel($events, $io, $api);
        $kernel->execute(function () { yield; });
    }
}