<?php

declare(strict_types=1);

namespace Recoil\System;

use Recoil\Listener\Listener;
use Recoil\Strand\Strand;

/**
 * A low-level strand interface for use within the kernel.
 */
interface SystemStrand extends Strand, Listener
{
    /**
     * Start the strand.
     */
    public function start(): void;

    /**
     * Attach a listener to this object.
     *
     * @param Listener $listener The object to resume when the work is complete.
     */
    public function await(Listener $listener): void;

    /**
     * Create a uni-directional link to another strand.
     *
     * @param SystemStrand $strand
     */
    public function link(SystemStrand $strand): void;

    /**
     * Break a previously created uni-directional link to another strand.
     *
     * @param SystemStrand $strand
     */
    public function unlink(SystemStrand $strand): void;
}