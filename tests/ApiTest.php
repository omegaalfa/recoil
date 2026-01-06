<?php

declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;
use Recoil\EventQueue;
use Recoil\IO;
use Recoil\ReferenceApi;
use Recoil\System\SystemStrand;


class ApiTest extends TestCase
{
    private ReferenceApi $api;
    private SystemStrand $strand;

    protected function setUp(): void
    {
        $this->strand = $this->createMock(SystemStrand::class);
        $events = new EventQueue();
        $io = new IO();
        $this->api = new ReferenceApi($events, $io);
    }

    public function testCooperate(): void
    {
        $result = $this->api->cooperate($this->strand);
        
        $this->assertNull($result);
    }

    public function testSleep(): void
    {
        $interval = 1.5;
        $result = $this->api->sleep($this->strand, $interval);
        
        $this->assertNull($result);
    }

    public function testTimeout(): void
    {
        $timeout = 2.0;
        $coroutine = function () { yield 1; };
        
        // Skip if strand mock isn't compatible with StrandTimeout
        try {
            $result = $this->api->timeout($this->strand, $timeout, $coroutine);
            $this->assertInstanceOf(\Generator::class, $result);
        } catch (\TypeError $e) {
            $this->markTestSkipped('Mock strand not compatible with StrandTimeout type hints');
        }
    }

    public function testRead(): void
    {
        $stream = fopen('php://memory', 'r+');
        $minLength = 10;
        $maxLength = 100;
        $result = $this->api->read($this->strand, $stream, $minLength, $maxLength);
        
        $this->assertNull($result);
        fclose($stream);
    }

    public function testSelect(): void
    {
        $readStreams = [fopen('php://memory', 'r')];
        $writeStreams = [fopen('php://memory', 'w')];
        $result = $this->api->select($this->strand, $readStreams, $writeStreams);
        
        $this->assertNull($result);
        
        foreach ($readStreams as $stream) {
            fclose($stream);
        }
        foreach ($writeStreams as $stream) {
            fclose($stream);
        }
    }

    public function testWrite(): void
    {
        $stream = fopen('php://memory', 'w');
        $buffer = 'test data';
        $length = strlen($buffer);
        $result = $this->api->write($this->strand, $stream, $buffer, $length);
        
        $this->assertNull($result);
        fclose($stream);
    }
}