<?php

declare(strict_types=1); // @codeCoverageIgnore

namespace Recoil\Strand;

use Closure;
use Generator;
use Recoil\Api\Api;
use Recoil\Api\ApiCall;
use Recoil\Api\Awaitable;
use Recoil\Exception\StrandListenerException;
use Recoil\Exception\TerminatedException;
use Recoil\Listener\Listener;
use Recoil\Listener\PrimaryListenerRemovedException;
use Recoil\Provider\AwaitableProvider;
use Recoil\Provider\CoroutineProvider;
use Recoil\System\SystemKernel;
use Recoil\System\SystemStrand;
use SplObjectStorage;
use Throwable;
use UnexpectedValueException;

/**
 * The standard {@see SystemStrand} implementation.
 */
trait StrandTrait
{
    /**
     * @var SystemKernel The kernel.
     */
    private SystemKernel $kernel;
    /**
     * @var Api The kernel API.
     */
    private Api $api;
    /**
     * @var int The strand Id.
     */
    private int $id;
    /**
     * @var array<Generator> The call-stack (except for the top element).
     */
    private array $stack = [];
    /**
     * @var int The call-stack depth (not including the top element).
     */
    private int $depth = 0;
    /**
     * @var Generator|null The current top of the call-stack.
     */
    private ?Generator $current;
    /**
     * @var ?Listener The strand's primary listener.
     */
    private ?Listener $primaryListener;
    /**
     * @var array<Listener> Objects to notify when this strand exits.
     */
    private array $listeners = [];
    /**
     * @var Closure|null A callable invoked when the strand is terminated.
     */
    private ?Closure $terminator = null;
    /**
     * @var SplObjectStorage<Strand>|null Strands to terminate when this strand
     *                                    is terminated.
     */
    private ?SplObjectStorage $linkedStrands = null;

    /**
     * @var int The current state of the strand.
     */
    private int $state = StrandState::READY;
    /**
     * @var string|null The next action to perform on the current coroutine ('send' or 'throw').
     */
    private ?string $action = null;

    /**
     * @var mixed The value or exception to send or throw on the next tick or
     *            the result of the strand's entry point coroutine if the strand
     *            has exited.
     */
    private mixed $value = null;
    /**
     * @var StrandTrace|null The strand trace, if set.
     */
    private ?StrandTrace $trace = null;

    /**
     * @param SystemKernel $kernel The kernel on which the strand is executing.
     * @param Api $api The kernel API used to handle yielded values.
     * @param int $id The strand ID.
     * @param mixed $entryPoint The strand's entry-point coroutine.
     */
    public function __construct(SystemKernel $kernel, Api $api, int $id, mixed $entryPoint)
    {
        $this->kernel = $kernel;
        $this->primaryListener = $kernel;
        $this->api = $api;
        $this->id = $id;

        if ($entryPoint instanceof Generator) {
            $this->current = $entryPoint;
        } elseif ($entryPoint instanceof CoroutineProvider) {
            $this->current = $entryPoint->coroutine();
        } else {
            $this->current = (static function () use ($entryPoint) {
                return yield $entryPoint;
            })();
        }
    }

    /**
     * Get the strand's ID.
     *
     * Strand IDs are unique within the kernel.
     */
    public function id(): int
    {
        return $this->id;
    }

    /**
     * Get the kernel that the strand is running on.
     */
    public function kernel(): SystemKernel
    {
        return $this->kernel;
    }

    /**
     * Terminate execution of the strand.
     *
     * If the strand is suspended waiting on an asynchronous operation, that
     * operation is cancelled.
     *
     * The call-stack is not unwound, it is simply discarded.
     *
     * @return void
     */
    public function terminate(): void
    {
        if ($this->state === StrandState::EXITED) {
            return;
        }

        $this->stack = [];
        $this->action = 'throw';
        $this->value = TerminatedException::create($this);

        if ($this->terminator) {
            ($this->terminator)($this);
        }

        $this->exit();
    }

    /**
     * Finalize the strand by notifying any listeners of the exit and
     * terminating any linked strands.
     *
     * @return void
     */
    private function exit(): void
    {
        $this->state = StrandState::EXITED;
        $this->current = null;

        try {
            $this->primaryListener->{$this->action}($this->value, $this);

            foreach ($this->listeners as $listener) {
                $listener->{$this->action}($this->value, $this);
            }

            // Notify the kernel if any of the listeners fail ...
        } catch (Throwable $e) {
            $this->kernel->throw(
                new StrandListenerException($this, $e),
                $this
            );
        } finally {
            $this->primaryListener = null;
            $this->listeners = [];
        }

        if ($this->linkedStrands !== null) {
            try {
                foreach ($this->linkedStrands as $strand) {
                    if ($strand instanceof SystemStrand) {
                        $strand->unlink($this);
                        $strand->terminate();
                    }
                }
            } finally {
                $this->linkedStrands = null;
            }
        }
    }

    /**
     * Resume execution of a suspended strand with an error.
     *
     * @param Throwable $exception The operation result.
     * @param Strand|null $strand The strand that produced this exception upon exit, if any.
     */
    public function throw(Throwable $exception, ?Strand $strand = null): void
    {
        // Ignore resumes after exit, not all asynchronous operations will have
        // meaningful cancel operations and some may attempt to resume the
        // strand after it has been terminated.
        if ($this->state === StrandState::EXITED) {
            return;
        }

        $this->action = 'throw';
        $this->value = $exception;

        if ($this->state === StrandState::SUSPENDED_INACTIVE) {
            $this->start();
        } else {
            $this->state = StrandState::READY;
        }
    }

    /**
     * Start the strand.
     *
     * @return void
     */
    public function start(): void
    {
        ////////////////////////////////////////////////////////////////////////////
        // This method intentionally sacrifices readability in order to keep      //
        // the number of function calls to a minimum for the sake of performance. //
        ////////////////////////////////////////////////////////////////////////////

        // The strand has exited already. This can occur if it is terminated
        // immediately after being scheduled for execution ...
        if ($this->state === StrandState::EXITED) {
            return;
        }

        $this->state = StrandState::RUNNING;
        $this->terminator = null;

        // Execute the next "tick" of the current coroutine ...
        while ($this->state === StrandState::RUNNING) {
            try {
                // If action is set, we are resuming the generator. The action and
                // the associated value variable must be set before jumping to the
                // "resume_generator" label, or by calling send() or throw() ...
                if ($this->action) {
                    $this->terminator = null;
                    $this->current->{$this->action}($this->value);
                    $this->action = $this->value = null;
                }

                // If the generator is "valid" it has futher iterations to perform,
                // therefore it has yielded, rather than returned ...
                if ($this->current->valid()) {
                    $produced = $this->current->current();

                    $this->state = StrandState::SUSPENDED_ACTIVE;

                    try {
                        // Another generator was yielded, push it onto the call
                        // stack and execute it ...
                        if ($produced instanceof Generator) {
                            // "fast" functionless stack-push ...
                            $this->stack[$this->depth++] = $this->current;
                            $this->current = $produced;
                            $this->state = StrandState::RUNNING;
                            // Trace the stack push, this is performed inside an
                            // A coroutine provider was yielded. Extract the coroutine
                            // then push it onto the call-stack and execute it ...
                        } elseif ($produced instanceof CoroutineProvider) {
                            // The coroutine is extracted from the provider before the
                            // stack push is begun in case coroutine() throws ...
                            $produced = $produced->coroutine();
                            // An API call was made through the Recoil static facade ...
                        } elseif ($produced instanceof ApiCall) {
                            $produced = $this->api->{$produced->__name}(
                                $this,
                                ...$produced->__arguments
                            );

                            // A generic awaitable object was yielded ...
                        } elseif ($produced instanceof Awaitable) {
                            $produced->await($this);

                            // An awaitable provider was yielded ...
                        } elseif ($produced instanceof AwaitableProvider) {
                            $produced->awaitable()->await($this);

                            // A raw callable was yielded ...
                        } elseif (
                            $produced instanceof Closure || // perf
                            \is_callable($produced)
                        ) {
                            $produced = $produced();
                            throw new UnexpectedValueException(
                                'The yielded callable returned ' .
                                $produced .
                                ', expected a generator.'
                            );

                            // If null was yielded, cooperate immediately
                        } elseif ($produced === null) {
                            // Schedule immediate resumption
                            $this->action = 'send';
                            $this->value = null;
                            $this->state = StrandState::READY;

                            // Some unidentified value was yielded, allow the API to
                            // dispatch the operation as it sees fit ...
                        }

                        // An exception occurred as a result of the yielded value. This
                        // exception is not propagated up the call-stack, but rather
                        // sent back to the current coroutine (i.e., the one that yielded
                        // the value) ...
                    } catch (Throwable $e) {
                        $this->action = 'throw';
                        $this->value = $e;
                        $this->state = StrandState::RUNNING;
                    }

                    // The strand has already been set back to the READY state. This
                    // means that send() or throw() was called while handling the
                    // yielded value. Resume the current coroutine immediately ...
                    if ($this->state === StrandState::READY) {
                        $this->state = StrandState::RUNNING;
                        // Continue the loop to execute immediately

                        // If state is still RUNNING, a generator was pushed onto the stack
                        // Continue the loop to execute it
                    } elseif ($this->state === StrandState::RUNNING) {
                        // Continue the loop

                        // Otherwise, if the strand was not terminated while handling
                        // the yielded value, it is now fully suspended. No further
                        // action will be performed until send() or throw() is called ...
                    } elseif ($this->state !== StrandState::EXITED) {
                        $this->state = StrandState::SUSPENDED_INACTIVE;
                    }

                    // Only break if we're not running anymore
                    if ($this->state !== StrandState::RUNNING) {
                        break; // Exit the while loop
                    }

                    // Otherwise continue the loop
                    continue;
                }

                // The generator is not "valid", and has therefore returned a value
                // (which may be null) ...
                $this->action = 'send';
                $this->value = $this->current->getReturn();

                // An exception was thrown during the execution of the generator ...
            } catch (Throwable $e) {
                $this->action = 'throw';
                $this->value = $e;
            }

            // Trace the stack pop, this is performed inside an assertion so
            // that it can be optimised away completely in production ...

            // The current coroutine has ended, either by returning or throwing. If
            // there is a coroutine above it on the call-stack, we pop the current
            // coroutine from the stack and resume the parent ...
            if ($this->depth) {
                // "fast" functionless stack-pop ...
                $current = &$this->stack[--$this->depth];
                $this->current = $current;
                $current = null;

                $this->state = StrandState::RUNNING;
                // Continue the loop to execute the parent generator
            } else {
                // Otherwise the call-stack is empty, the strand has exited ...
                $this->exit();
                break; // Exit the while loop
            }
        } // End of while loop;
    }

    /**
     * Attach a listener to this object.
     *
     * @param Listener $listener The object to resume when the work is complete.
     *
     * @return void
     */
    public function await(Listener $listener): void
    {
        if ($this->state === StrandState::EXITED) {
            $listener->{$this->action}($this->value, $this);
            return;
        }

        $this->listeners[] = $listener;
    }

    /**
     * The Strand interface extends AwaitableProvider, but this particular
     * implementation can provide await functionality directly.
     *
     * Implementations must favour await() over awaitable() when both are
     * available to avoid a pointless performance hit.
     *
     * @return Awaitable
     */
    public function awaitable(): Awaitable
    {
        return $this;
    }

    /**
     * Break a previously created uni-directional link to another strand.
     *
     * @param SystemStrand $strand
     *
     * @return void
     */
    public function unlink(SystemStrand $strand): void
    {
        $this->linkedStrands?->detach($strand);
    }

    /**
     * Resume execution of a suspended strand.
     *
     * @param mixed $value The value to send to the coroutine on the the top of the call-stack.
     * @param Strand|null $strand The strand that produced this result upon exit, if any.
     *
     * @return void
     */
    public function send(mixed $value = null, ?Strand $strand = null): void
    {
        // Ignore resumes after exit, not all asynchronous operations will have
        // meaningful cancel operations and some may attempt to resume the
        // strand after it has been terminated.
        if ($this->state === StrandState::EXITED) {
            return;
        }

        $this->action = 'send';
        $this->value = $value;

        if ($this->state === StrandState::SUSPENDED_INACTIVE) {
            $this->start();

            return;
        }

        $this->state = StrandState::READY;
    }

    /**
     * Check if the strand has exited.
     */
    public function hasExited(): bool
    {
        return $this->state === StrandState::EXITED;
    }

    /**
     * Set the primary listener.
     *
     * If the current primary listener is not the kernel, it is notified with
     * a {@see PrimaryListenerRemovedException}.
     *
     * @param Listener $listener
     * @return void
     */
    public function setPrimaryListener(Listener $listener): void
    {
        if ($this->state === StrandState::EXITED) {
            $listener->{$this->action}($this->value, $this);
        } else {
            $previous = $this->primaryListener;
            $this->primaryListener = $listener;

            if ($previous !== $this->kernel) {
                $previous->throw(
                    new PrimaryListenerRemovedException($previous, $this),
                    $this
                );
            }
        }
    }

    /**
     * Set the primary listener to the kernel.
     *
     * The current primary listener is not notified.
     */
    public function clearPrimaryListener(): void
    {
        $this->primaryListener = $this->kernel;
    }

    /**
     * Set the strand 'terminator'.
     *
     * The terminator is a function invoked when the strand is terminated. It is
     * used by the kernel API to clean up any pending asynchronous operations.
     *
     * The terminator function is removed without being invoked when the strand
     * is resumed.
     */
    public function setTerminator(?callable $fn = null): void
    {
        $this->terminator = $fn;
    }

    /**
     * Create a uni-directional link to another strand.
     *
     * If this strand exits, any linked strands are terminated.
     *
     * @param SystemStrand $strand
     * @return void
     */
    public function link(SystemStrand $strand): void
    {
        if ($this === $strand) {
            return;
        }

        if ($this->linkedStrands === null) {
            $this->linkedStrands = new SplObjectStorage();
        }

        $this->linkedStrands->attach($strand);
    }

    /**
     * Get the current trace for this strand.
     *
     * @return StrandTrace|null
     */
    public function trace(): ?StrandTrace
    {
        return $this->trace;
    }

    /**
     * Set the current trace for this strand.
     *
     * This method has no effect when assertions are disabled.
     *
     * @param StrandTrace|null $trace
     * @return void
     */
    public function setTrace(?StrandTrace $trace = null): void
    {

    }
}
