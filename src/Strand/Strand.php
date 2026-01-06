<?php

declare(strict_types=1);

namespace Recoil\Strand;

use Recoil\Listener\Listener;
use Recoil\System\SystemKernel;
use Throwable;

/**
 * Strand represents a single execution context.
 */
interface Strand
{
    /**
     * Get the strand's ID.
     */
    public function id(): int;

    /**
     * Get the kernel that the strand is running on.
     */
    public function kernel(): SystemKernel;

    /**
     * Terminate execution of the strand.
     */
    public function terminate(): void;

    /**
     * Resume execution of a suspended strand.
     *
     * @param mixed $value The value to send to the coroutine.
     * @param Strand|null $strand The strand that produced this result upon exit, if any.
     */
    public function send(mixed $value = null, ?Strand $strand = null): void;

    /**
     * Resume execution of a suspended strand with an error.
     *
     * @param Throwable $exception The operation result.
     * @param Strand|null $strand The strand that produced this exception upon exit, if any.
     */
    public function throw(Throwable $exception, ?Strand $strand = null): void;

    /**
     * Check if the strand has exited.
     */
    public function hasExited(): bool;

    /**
     * Set the primary listener.
     *
     * @param Listener $listener
     */
    public function setPrimaryListener(Listener $listener): void;

    /**
     * Set the primary listener to the kernel.
     */
    public function clearPrimaryListener(): void;

    /**
     * Set the strand terminator callback.
     *
     * @param callable|null $fn
     */
    public function setTerminator(?callable $fn = null): void;
}