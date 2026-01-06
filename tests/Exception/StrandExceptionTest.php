<?php

declare(strict_types=1);

namespace Tests\Exception;

use Exception;
use PHPUnit\Framework\TestCase;
use Recoil\Exception\StrandException;
use Recoil\Strand\Strand;

class StrandExceptionTest extends TestCase
{
    public function testCreate(): void
    {
        $strand = $this->createMock(Strand::class);
        $strand->method('id')->willReturn(123);

        $previous = new Exception('Something went wrong', 456);

        $exception = StrandException::create($strand, $previous);

        $this->assertSame('Strand #123 exited with exception: Something went wrong', $exception->getMessage());
        $this->assertSame(456, $exception->getCode());
        $this->assertSame($previous, $exception->getPrevious());
        $this->assertSame($strand, $exception->strand());
    }
}
