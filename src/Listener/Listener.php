<?php

declare(strict_types=1);

namespace Recoil\Listener;

use Recoil\Strand\Strand;
use Throwable;

/**
 * Listener is notified when an asynchronous operation completes.
 */
interface Listener
{
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