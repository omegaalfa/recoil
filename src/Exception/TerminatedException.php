<?php

declare(strict_types=1);

namespace Recoil\Exception;

use Exception;
use Recoil\Strand\Strand;

/**
 * Exception thrown when a strand is terminated.
 */
class TerminatedException extends Exception
{
    private Strand $strand;

    /**
     * Create a terminated exception.
     *
     * @param Strand $strand
     * @return static
     */
    public static function create(Strand $strand): static
    {
        $e = new static('Strand #' . $strand->id() . ' was terminated.');
        $e->strand = $strand;
        return $e;
    }

    /**
     * Get the strand that was terminated.
     */
    public function strand(): Strand
    {
        return $this->strand;
    }
}