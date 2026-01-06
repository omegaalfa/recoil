<?php

declare(strict_types=1); // @codeCoverageIgnore

namespace Recoil;


use Closure;
use Recoil\Exception\TimeoutException;
use Recoil\Listener\Listener;
use Recoil\Strand\Strand;
use Recoil\System\SystemStrand;
use Throwable;

/**
 * Please note that this code is not part of the public API. It may be
 * changed or removed at any time without notice.
 *
 * @access private
 *
 * An implementation of Api::timeout() based on the reference kernel's event
 * queue.
 */
final class StrandTimeout implements Listener
{

    /**
     * @var EventQueue The event queue used to schedule the timeout event.
     */
    private EventQueue $events;

    /**
     * @var ?Closure The function to call to cancel the timeout event.
     */
    private ?Closure $cancel;

    /**
     * @var float The timeout, in seconds.
     */
    private float $timeout;

    /**
     * @var Listener|Strand|null The object to notify upon completion.
     */
    private $listener;

    /**
     * @var ?SystemStrand The strand to wait for.
     */
    private ?SystemStrand $substrand;

    /**
     * @var EventQueue   The event queue used to schedule the timeout event.
     * @var float $timeout The timeout, in seconds.
     * @var SystemStrand $substrand The strand to wait for.
     */
    public function __construct(
        EventQueue   $events,
        float        $timeout,
        SystemStrand $substrand
    )
    {
        $this->events = $events;
        $this->timeout = $timeout;
        $this->substrand = $substrand;
    }

    /**
     * Attach a listener to this object.
     *
     * @param Listener $listener The object to resume when the work is complete.
     */
    public function await($listener)
    {
        $this->cancel = $this->events->schedule(
            $this->timeout,
            function () {
                if ($this->substrand) {
                    $this->substrand->clearPrimaryListener();
                    $this->substrand->terminate();

                    $this->listener->throw(TimeoutException::create($this->timeout));
                }
            }
        );

        if ($listener instanceof Strand) {
            $listener->setTerminator(function () {
                if ($this->substrand) {
                    ($this->cancel)();
                    $this->substrand->clearPrimaryListener();
                    $this->substrand->terminate();
                }
            });
        }

        $this->listener = $listener;

        $this->substrand->setPrimaryListener($this);
    }

    /**
     * Send the result of an unsuccessful operation.
     *
     * @param Throwable $exception The operation result.
     * @param Strand|null $strand The strand that produced this exception upon exit, if any.
     */
    public function throw(Throwable $exception, ?Strand $strand = null): void
    {
        $this->substrand = null;
        ($this->cancel)();
        $this->listener->throw($exception);
    }

    /**
     * Send the result of a successful operation.
     *
     * @param mixed $value The operation result.
     * @param Strand|null $strand The strand that produced this result upon exit, if any.
     */
    public function send(mixed $value = null, ?Strand $strand = null): void
    {

        $this->substrand = null;
        ($this->cancel)();
        $this->listener->send($value);
    }


}
