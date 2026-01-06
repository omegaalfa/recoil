<?php

declare(strict_types=1);

namespace Tests;


use PHPUnit\Framework\TestCase;
use Recoil\EventQueue;
use Recoil\Exceptions\TimeoutException;
use Recoil\Listener\Listener;
use Recoil\Strand\Strand;
use Recoil\StrandTimeout;
use Recoil\System\SystemStrand;

class StrandTimeoutTest extends TestCase
{
    private EventQueue $eventQueue;
    private SystemStrand $substrand;
    private Listener $listener;
    private StrandTimeout $timeout;

    protected function setUp(): void
    {
        $this->eventQueue = $this->createMock(EventQueue::class);
        $this->substrand = $this->createMock(SystemStrand::class);
        $this->listener = $this->createMock(Listener::class);
        
        $this->timeout = new StrandTimeout(
            $this->eventQueue,
            5.0,
            $this->substrand
        );
    }

    public function testConstructorSetsProperties(): void
    {
        $timeout = new StrandTimeout($this->eventQueue, 10.0, $this->substrand);
        $this->assertInstanceOf(StrandTimeout::class, $timeout);
    }

    public function testAwaitSchedulesTimeoutEvent(): void
    {
        $cancelCallback = function () {};
        
        $this->eventQueue->expects($this->once())
            ->method('schedule')
            ->with(5.0, $this->isType('callable'))
            ->willReturn($cancelCallback);

        $this->substrand->expects($this->once())
            ->method('setPrimaryListener')
            ->with($this->timeout);

        $this->timeout->await($this->listener);
    }

    public function testAwaitWithStrandListenerSetsTerminator(): void
    {
        $strand = $this->createMock(Strand::class);
        $cancelCallback = function () {};
        
        $this->eventQueue->method('schedule')
            ->willReturn($cancelCallback);

        $strand->expects($this->once())
            ->method('setTerminator')
            ->with($this->isType('callable'));

        $this->substrand->expects($this->once())
            ->method('setPrimaryListener');

        $this->timeout->await($strand);
    }

    public function testSendCancelsTimeoutAndForwardsValue(): void
    {
        $cancelCalled = false;
        $cancelCallback = function () use (&$cancelCalled) {
            $cancelCalled = true;
        };
        
        $this->eventQueue->method('schedule')
            ->willReturn($cancelCallback);

        $this->listener->expects($this->once())
            ->method('send')
            ->with('test_value');

        $this->timeout->await($this->listener);
        $this->timeout->send('test_value');

        $this->assertTrue($cancelCalled);
    }

    public function testThrowCancelsTimeoutAndForwardsException(): void
    {
        $cancelCalled = false;
        $cancelCallback = function () use (&$cancelCalled) {
            $cancelCalled = true;
        };
        
        $this->eventQueue->method('schedule')
            ->willReturn($cancelCallback);

        $exception = new \Exception('test exception');

        $this->listener->expects($this->once())
            ->method('throw')
            ->with($exception);

        $this->timeout->await($this->listener);
        $this->timeout->throw($exception);

        $this->assertTrue($cancelCalled);
    }

    public function testTimeoutEventThrowsTimeoutException(): void
    {
        $timeoutCallback = null;
        
        $this->eventQueue->method('schedule')
            ->willReturnCallback(function ($timeout, $callback) use (&$timeoutCallback) {
                $timeoutCallback = $callback;
                return function () {};
            });

        $this->listener->expects($this->once())
            ->method('throw')
            ->with($this->isInstanceOf(\Recoil\Exception\TimeoutException::class));

        $this->substrand->expects($this->once())
            ->method('clearPrimaryListener');

        $this->substrand->expects($this->once())
            ->method('terminate');

        $this->timeout->await($this->listener);
        $timeoutCallback();
    }

    public function testStrandTerminatorCancelsAndTerminatesSubstrand(): void
    {
        $strand = $this->createMock(Strand::class);
        $terminatorCallback = null;
        $cancelCalled = false;
        
        $this->eventQueue->method('schedule')
            ->willReturn(function () use (&$cancelCalled) {
                $cancelCalled = true;
            });

        $strand->method('setTerminator')
            ->willReturnCallback(function ($callback) use (&$terminatorCallback) {
                $terminatorCallback = $callback;
            });

        $this->substrand->expects($this->once())
            ->method('clearPrimaryListener');

        $this->substrand->expects($this->once())
            ->method('terminate');

        $this->timeout->await($strand);
        $terminatorCallback();

        $this->assertTrue($cancelCalled);
    }
}