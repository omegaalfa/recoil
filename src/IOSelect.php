<?php

declare(strict_types=1); // @codeCoverageIgnore

namespace Recoil;

use Closure;

/**
 * Please note that this code is not part of the public API. It may be
 * changed or removed at any time without notice.
 *
 * @access private
 *
 * Holds state for an individual select operation.
 */
final class IOSelect
{
    /**
     * @var int
     */
    public int $id;

    /**
     * @var array
     */
    public array $read;
    /**
     * @var array
     */
    public array $write;
    /**
     * @var Closure
     */
    public Closure $callback;


    /**
     * @param int $id
     * @param array $read
     * @param array $write
     * @param Closure $fn
     */
    public function __construct(int $id, array $read, array $write, Closure $fn)
    {
        $this->id = $id;
        $this->callback = $fn;

        $this->read = [];
        foreach ($read as $stream) {
            assert(is_resource($stream));
            $this->read[(int)$stream] = $stream;
        }

        $this->write = [];
        foreach ($write as $stream) {
            assert(is_resource($stream));
            $this->write[(int)$stream] = $stream;
        }
    }
}
