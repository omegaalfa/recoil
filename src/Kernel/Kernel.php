<?php

declare(strict_types=1);

namespace Recoil\Kernel;

use Recoil\Strand\Strand;
use Throwable;

/**
 * The main kernel interface.
 */
interface Kernel
{
    /**
     * Run the kernel until all strands exit or the kernel is stopped.
     */
    public function run(): void;

    /**
     * Stop the kernel.
     */
    public function stop(): void;

    /**
     * Schedule a coroutine for execution on a new strand.
     *
     * @param mixed $coroutine The coroutine to execute.
     */
    public function execute(mixed $coroutine): Strand;

    /**
     * Set a user-defined exception handler function.
     *
     * @param callable|null $fn The exception handler (null = remove).
     */
    public function setExceptionHandler(?callable $fn = null): void;

    /**
     * Send the result of a successful operation.
     *
     * @param mixed $value The operation result.
     * @param Strand|null $strand The strand that produced this result upon exit, if any.
     */
    public function send(mixed $value = null, ?Strand $strand = null): void;

    /**
     * Send the result of an unsuccessful operation.
     *
     * @param Throwable $exception The operation result.
     * @param Strand|null $strand The strand that produced this exception upon exit, if any.
     */
    public function throw(Throwable $exception, ?Strand $strand = null): void;
}
