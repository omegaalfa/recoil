<?php

declare(strict_types=1);

namespace Recoil\Strand;

/**
 * StrandTrace provides debugging information about a strand's call stack.
 */
final class StrandTrace
{
    /**
     * @param array $frames The stack frames.
     */
    public function __construct(
        private array $frames = []
    )
    {
    }

    /**
     * Get the stack frames.
     *
     * @return array
     */
    public function frames(): array
    {
        return $this->frames;
    }

    /**
     * Push a frame onto the trace.
     *
     * @param mixed $frame
     */
    public function push(mixed $frame): void
    {
        $this->frames[] = $frame;
    }

    /**
     * Pop a frame from the trace.
     */
    public function pop(): void
    {
        array_pop($this->frames);
    }
}