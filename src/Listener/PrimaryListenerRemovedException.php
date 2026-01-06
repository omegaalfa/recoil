<?php

declare(strict_types=1);

namespace Recoil\Listener;

use Exception;
use Recoil\Strand\Strand;

/**
 * Exception thrown when a primary listener is removed.
 */
class PrimaryListenerRemovedException extends Exception
{
    private Listener $listener;
    private Strand $strand;

    /**
     * @param Listener $listener
     * @param Strand $strand
     */
    public function __construct(Listener $listener, Strand $strand)
    {
        parent::__construct(
            'Primary listener was removed from strand #' . $strand->id()
        );
        $this->listener = $listener;
        $this->strand = $strand;
    }

    /**
     * Get the listener that was removed.
     */
    public function listener(): Listener
    {
        return $this->listener;
    }

    /**
     * Get the strand from which the listener was removed.
     */
    public function strand(): Strand
    {
        return $this->strand;
    }
}