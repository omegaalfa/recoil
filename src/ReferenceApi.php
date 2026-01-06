<?php

declare(strict_types=1); // @codeCoverageIgnore

namespace Recoil;

use ErrorException;
use Generator;
use Recoil\Api\Api;
use Recoil\System\SystemStrand;

/**
 * Please note that this code is not part of the public API. It may be
 * changed or removed at any time without notice.
 *
 * @access private
 *
 * The reference kernel's API implementation.
 */
final class ReferenceApi implements Api
{

    /**
     * The maximum number of bytes to read from a stream in a single call to
     * fread().
     */
    const int MAX_READ_LENGTH = 32768;

    /**
     * @var EventQueue The queue used to schedule events.
     */
    private EventQueue $events;

    /**
     * @var IO The object used to perform IO.
     */
    private IO $io;


    /**
     * @param EventQueue $events The queue used to schedule events.
     * @param IO $io The object used to perform IO.
     */
    public function __construct(EventQueue $events, IO $io)
    {
        $this->events = $events;
        $this->io = $io;
    }

    /**
     * Force the current strand to cooperate.
     *
     * @param SystemStrand $strand The strand executing the API call.
     *
     * @return Generator|null
     *
     * @see Recoil::cooperate() for the full specification.
     *
     */
    public function cooperate(SystemStrand $strand): ?Generator
    {
        $strand->setTerminator(
            $this->events->schedule(
                0,
                function () use ($strand) {
                    $strand->send();
                }
            )
        );
        return null;
    }

    /**
     * Suspend the current strand for a fixed interval.
     *
     * @param SystemStrand $strand The strand executing the API call.
     * @param float $interval The interval to wait, in seconds.
     *
     * @return Generator|null
     * @see Recoil::sleep() for the full specification.
     *
     */
    public function sleep(SystemStrand $strand, float $interval): ?Generator
    {
        $strand->setTerminator(
            $this->events->schedule(
                $interval,
                function () use ($strand) {
                    $strand->send();
                }
            )
        );
        return null;
    }

    /**
     * Execute a coroutine with a cap on execution time.
     *
     * @param SystemStrand $strand The strand executing the API call.
     * @param float $timeout The interval to allow for execution, in seconds.
     * @param mixed $coroutine The coroutine to execute.
     *
     * @return Generator|null
     * @see Recoil::timeout() for the full specification.
     *
     */
    public function timeout(SystemStrand $strand, float $timeout, mixed $coroutine): ?Generator
    {
        $substrand = $strand->kernel()->execute($coroutine);

        $awaitable = new StrandTimeout(
            $this->events,
            $timeout,
            $substrand
        );

        $awaitable->await($strand);

        return null;
    }

    /**
     * Read data from a stream.
     *
     * @param SystemStrand $strand The strand executing the API call.
     * @param resource $stream A readable stream resource.
     * @param int $minLength The minimum number of bytes to read.
     * @param int $maxLength The maximum number of bytes to read.
     *
     * @return Generator|null
     * @see Recoil::read() for the full specification.
     *
     */
    public function read(SystemStrand $strand, mixed $stream, int $minLength, int $maxLength): ?Generator
    {
        $buffer = '';
        $done = null;
        $done = $this->io->select(
            [$stream],
            [],
            function () use (
                $stream,
                $strand,
                &$minLength,
                &$maxLength,
                &$done,
                &$buffer
            ) {
                $chunk = @\fread(
                    $stream,
                    min($maxLength, self::MAX_READ_LENGTH)
                );

                if ($chunk === false) {
                    // @codeCoverageIgnoreStart
                    $done();
                    $error = \error_get_last();
                    $strand->throw(
                        new ErrorException(
                            $error['message'],
                            $error['type'],
                            1, // severity
                            $error['file'],
                            $error['line']
                        )
                    );
                    // @codeCoverageIgnoreEnd
                } elseif ($chunk === '') {
                    $done();
                    $strand->send($buffer);
                } else {
                    $buffer .= $chunk;
                    $length = \strlen($chunk);

                    if ($length >= $minLength || $length === $maxLength) {
                        $done();
                        $strand->send($buffer);
                    } else {
                        $minLength -= $length;
                        $maxLength -= $length;
                    }
                }
            }
        );

        $strand->setTerminator($done);
        return null;
    }

    /**
     * Wait for one or more streams to become readable or writable.
     *
     * @param SystemStrand $strand The strand executing the API call.
     * @param array<resource> $read The set of readable streams.
     * @param array<resource> $read The set of writable streams.
     *
     * @return Generator|null
     * @see Recoil::select() for the full specification.
     *
     */
    public function select(SystemStrand $strand, array $read, array $write): ?Generator
    {
        $done = null;
        $done = $this->io->select(
            $read,
            $write,
            function ($read, $write) use ($strand, &$done) {
                $done();
                $strand->send([$read, $write]);
            }
        );

        $strand->setTerminator($done);

        return null;
    }

    /**
     * Write data to a stream.
     *
     * @param SystemStrand $strand The strand executing the API call.
     * @param resource $stream A writable stream resource.
     * @param string $buffer The data to write to the stream.
     * @param int $length The maximum number of bytes to write.
     *
     * @return Generator|null
     * @see Recoil::write() for the full specification.
     *
     */
    public function write(SystemStrand $strand, mixed $stream, string $buffer, int $length): ?Generator
    {
        $bufferLength = \strlen($buffer);

        if ($bufferLength < $length) {
            $length = $bufferLength;
        }

        if ($length === 0) {
            $strand->send();
        }

        $done = null;
        $done = $this->io->select(
            [],
            [$stream],
            function () use (
                $stream,
                $strand,
                &$done,
                &$buffer,
                &$length
            ) {
                $bytes = @\fwrite($stream, $buffer, $length);

                // zero and false both indicate an error
                // http://php.net/manual/en/function.fwrite.php#96951
                if ($bytes === 0 || $bytes === false) {
                    // @codeCoverageIgnoreStart
                    $done();
                    $error = \error_get_last();
                    $strand->throw(
                        new ErrorException(
                            $error['message'],
                            $error['type'],
                            1, // severity
                            $error['file'],
                            $error['line']
                        )
                    );
                    // @codeCoverageIgnoreEnd
                } elseif ($bytes === $length) {
                    $done();
                    $strand->send();
                } else {
                    $length -= $bytes;
                    $buffer = \substr($buffer, $bytes);
                }
            }
        );

        $strand->setTerminator($done);
        return null;

    }

}
