<?php

declare(strict_types=1);

namespace Recoil\Provider;

use Generator;

/**
 * CoroutineProvider provides a coroutine when yielded.
 */
interface CoroutineProvider
{
    /**
     * Get the coroutine.
     *
     * @return Generator
     */
    public function coroutine(): Generator;
}