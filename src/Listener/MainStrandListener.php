<?php

declare(strict_types=1);

namespace Recoil\Listener;

use Recoil\Strand\Strand;
use Throwable;

/**
 * MainStrandListener is used to capture the result of the main strand.
 */
final class MainStrandListener implements Listener
{
    private mixed $value = null;
    private ?Throwable $exception = null;

    /**
     * Send the result of a successful operation.
     *
     * @param mixed $value The operation result.
     * @param Strand|null $strand The strand that produced this result upon exit, if any.
     */
    public function send(mixed $value = null, ?Strand $strand = null): void
    {
        $this->value = $value;
    }

    /**
     * Send the result of an unsuccessful operation.
     *
     * @param Throwable $exception The operation result.
     * @param Strand|null $strand The strand that produced this exception upon exit, if any.
     */
    public function throw(Throwable $exception, ?Strand $strand = null): void
    {
        $this->exception = $exception;
    }

    /**
     * Get the result of the main strand.
     *
     * @return mixed
     * @throws Throwable
     */
    public function get(): mixed
    {
        if ($this->exception !== null) {
            throw $this->exception;
        }

        return $this->value;
    }
}