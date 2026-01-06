<?php

declare(strict_types=1);

namespace Recoil\Api;

/**
 * ApiCall represents a call to the kernel API.
 */
final readonly class ApiCall
{
    /**
     * @param string $__name The name of the API method to call.
     * @param array $__arguments The arguments to pass to the method.
     */
    public function __construct(
        public string $__name,
        public array  $__arguments = []
    )
    {
    }
}
