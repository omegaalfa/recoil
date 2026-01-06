<?php

declare(strict_types=1);

namespace Recoil\Provider;

use Recoil\Api\Awaitable;

/**
 * AwaitableProvider provides an awaitable when yielded.
 */
interface AwaitableProvider
{
    /**
     * Get the awaitable.
     *
     * @return Awaitable
     */
    public function awaitable(): Awaitable;
}
