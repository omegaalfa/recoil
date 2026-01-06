<?php

declare(strict_types=1);

namespace Recoil\Api;

use Recoil\Listener\Listener;

/**
 * Awaitable represents an operation that can be awaited.
 */
interface Awaitable
{
    /**
     * Attach a listener to this object.
     *
     * @param Listener $listener The object to resume when the work is complete.
     */
    public function await(Listener $listener): void;
}
