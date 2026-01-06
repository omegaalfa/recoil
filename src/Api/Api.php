<?php

declare(strict_types=1);

namespace Recoil\Api;

use Generator;
use Recoil\System\SystemStrand;

/**
 * The kernel API interface.
 */
interface Api
{
    /**
     * Force the current strand to cooperate.
     *
     * @param SystemStrand $strand The strand executing the API call.
     */
    public function cooperate(SystemStrand $strand): ?Generator;

    /**
     * Suspend the current strand for a fixed interval.
     *
     * @param SystemStrand $strand The strand executing the API call.
     * @param float $interval The interval to wait, in seconds.
     */
    public function sleep(SystemStrand $strand, float $interval): ?Generator;

    /**
     * Execute a coroutine with a cap on execution time.
     *
     * @param SystemStrand $strand The strand executing the API call.
     * @param float $timeout The timeout interval, in seconds.
     * @param mixed $coroutine The coroutine to execute.
     */
    public function timeout(SystemStrand $strand, float $timeout, mixed $coroutine): ?Generator;

    /**
     * Read data from a stream.
     *
     * @param SystemStrand $strand The strand executing the API call.
     * @param resource $stream A readable stream resource.
     * @param int $minLength The minimum number of bytes to read.
     * @param int $maxLength The maximum number of bytes to read.
     */
    public function read(SystemStrand $strand, mixed $stream, int $minLength, int $maxLength): ?Generator;

    /**
     * Wait for one or more streams to become readable or writable.
     *
     * @param SystemStrand $strand The strand executing the API call.
     * @param array<resource> $read The set of readable streams.
     * @param array<resource> $write The set of writable streams.
     */
    public function select(SystemStrand $strand, array $read, array $write): ?Generator;

    /**
     * Write data to a stream.
     *
     * @param SystemStrand $strand The strand executing the API call.
     * @param resource $stream A writable stream resource.
     * @param string $buffer The data to write.
     * @param int $length The maximum number of bytes to write.
     */
    public function write(SystemStrand $strand, mixed $stream, string $buffer, int $length): ?Generator;
}