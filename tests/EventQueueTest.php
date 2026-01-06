<?php

declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;
use Recoil\EventQueue;

class EventQueueTest extends TestCase
{
    private EventQueue $queue;

    protected function setUp(): void
    {
        $this->queue = new EventQueue();
    }

    public function testScheduleExecutesCallback(): void
    {
        $called = false;
        $this->queue->schedule(0.0, function () use (&$called) {
            $called = true;
        });

        $this->queue->tick();

        $this->assertTrue($called);
    }

    public function testScheduleWithDelay(): void
    {
        $called = false;
        $this->queue->schedule(0.05, function () use (&$called) {
            $called = true;
        });

        // Should not execute yet
        $wait = $this->queue->tick();
        $this->assertFalse($called);
        $this->assertIsInt($wait);
        $this->assertGreaterThan(0, $wait);
        
        // Wait and execute
        usleep(60000);
        $this->queue->tick();
        $this->assertTrue($called);
    }

    public function testCancel(): void
    {
        $called = false;
        $cancel = $this->queue->schedule(0.0, function () use (&$called) {
            $called = true;
        });

        $cancel();
        $this->queue->tick();

        $this->assertFalse($called);
    }

    public function testExecutionOrder(): void
    {
        $executionOrder = [];
        
        // Schedule later event first
        $this->queue->schedule(0.04, function () use (&$executionOrder) {
            $executionOrder[] = 'second';
        });
        
        // Schedule earlier event second
        $this->queue->schedule(0.02, function () use (&$executionOrder) {
            $executionOrder[] = 'first';
        });

        usleep(50000);
        $this->queue->tick();

        $this->assertSame(['first', 'second'], $executionOrder);
    }

    public function testTickReturnsNullWhenQueueIsEmpty(): void
    {
        $this->assertNull($this->queue->tick());
    }
    
    public function testTickReturnsNullWhenAllEventsCancelled(): void
    {
        $cancel = $this->queue->schedule(0.1, fn() => null);
        $cancel();
        
        $this->assertNull($this->queue->tick());
    }
}
