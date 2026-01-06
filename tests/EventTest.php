<?php

declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;
use Recoil\Event;

class EventTest extends TestCase
{
    public function testConstruct(): void
    {
        $time = 123.45;
        $fn = fn() => 'test';

        $event = new Event($time, $fn);

        $this->assertSame($time, $event->time);
        $this->assertSame($fn, $event->fn);
    }

    public function testPropertiesAreMutable(): void
    {
        $event = new Event(0.0, fn() => null);

        $event->time = 10.5;
        $this->assertSame(10.5, $event->time);

        $event->fn = null;
        $this->assertNull($event->fn);
    }
}
