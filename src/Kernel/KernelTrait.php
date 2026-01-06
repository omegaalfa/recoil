<?php

declare(strict_types=1);

namespace Recoil\Kernel;

use Closure;
use Exception;
use Recoil\Api\Api;
use Recoil\Exception\KernelException;
use Recoil\Exception\StrandException;
use Recoil\Listener\MainStrandListener;
use Recoil\Strand\Strand;
use Recoil\System\SystemKernel;
use SplQueue;
use Throwable;

/**
 * The standard {@see SystemKernel} implementation.
 */
trait KernelTrait
{

    /**
     * @var Api The kernel API.
     */
    private Api $api;

    /**
     * @var int True if the event loop is currently running.
     */
    private int $state = KernelState::STOPPED;

    /**
     * @var Closure|null The exception handler.
     */
    private ?Closure $exceptionHandler = null;

    /**
     * @var ?SplQueue<Exception> A queue of exceptions that caused the kernel to panic.
     */
    private ?SplQueue $panicExceptions = null;

    /**
     * Execute a coroutine on a new kernel.
     *
     * This function blocks until the coroutine has finished executing, at which
     * point the kernel is stopped.
     *
     * @return mixed                  The result of the coroutine.
     * @throws Throwable              The exception thrown by the coroutine, if any.
     * @throws Exception The kernel was stopped before the coroutine completed.
     */
    public static function start($coroutine, mixed ...$arguments): mixed
    {
        $kernel = self::create(...$arguments);

        $listener = new MainStrandListener();
        $strand = $kernel->execute($coroutine);
        $strand->setPrimaryListener($listener);

        $kernel->run();

        return $listener->get();
    }

    /**
     * Create a new kernel.
     *
     * @param mixed $arguments,... Implementation-specific parameters.
     *
     * @return self
     */
    abstract public static function create(mixed $arguments = null): self;

    /**
     * Schedule a coroutine for execution on a new strand.
     *
     * Execution begins when the kernel is run; or, if called from within a
     * strand, when that strand cooperates.
     *
     * @param mixed $coroutine The coroutine to execute.
     */
    abstract public function execute(mixed $coroutine): Strand;

    /**
     * Run the kernel until all strands exit, the kernel is stopped or a kernel
     * panic occurs.
     *
     * A kernel panic occurs when an exception occurs that is not handled by the
     * kernel's exception handler.
     *
     * This method returns immediately if the kernel is already running.
     *
     * @throws Exception An unhandled exception has stopped the kernel.
     * @see ReferenceKernel::setExceptionHandler()
     *
     */
    public function run(): void
    {
        if ($this->state !== KernelState::STOPPED) {
            return;
        }

        if ($this->panicExceptions !== null &&
            !$this->panicExceptions->isEmpty()) {
            throw $this->panicExceptions->dequeue();
        }

        try {
            $this->state = KernelState::RUNNING;
            $this->loop();
        } catch (Throwable $e) {
            $this->throw($e);
        } finally {
            $this->state = KernelState::STOPPED;
        }

        if (
            $this->panicExceptions !== null &&
            !$this->panicExceptions->isEmpty()
        ) {
            throw $this->panicExceptions->dequeue();
        }
    }

    /**
     * The kernel's main event loop. Invoked inside the run() method.
     *
     * Loop must return when $this->state is KernelState::STOPPING.
     *
     * @return void
     */
    abstract protected function loop(): void;

    /**
     * Send the result of an unsuccessful operation.
     *
     * @param Throwable $exception The operation result.
     * @param Strand|null $strand The strand that produced this exception upon exit, if any.
     */
    public function throw(Throwable $exception, ?Strand $strand = null): void
    {
        // Termination is not an error ...
        if ($exception instanceof \Recoil\Exception\TerminatedException && $strand === $exception->strand()) {
            return;
        }

        if ($strand === null) {
            $exception = \Recoil\Exception\KernelException::create($exception);
        } else {
            $exception = \Recoil\Exception\StrandException::create($strand, $exception);
        }

        if ($this->exceptionHandler) {
            try {
                ($this->exceptionHandler)($exception);

                return;
            } catch (Exception $e) {
                $exception = $e;
            } catch (Throwable $e) {
                $exception = KernelException::create($e);
            }
        }

        if ($this->panicExceptions === null) {
            $this->panicExceptions = new SplQueue();
        }

        $this->panicExceptions->enqueue($exception);
        $this->stop();
    }

    /**
     * Stop the kernel.
     *
     * @return void
     */
    public function stop(): void
    {
        if ($this->state === KernelState::RUNNING) {
            $this->state = KernelState::STOPPING;
        }
    }

    /**
     * Set a user-defined exception handler function.
     *
     * The exception handler is invoked when a strand exits with an exception or
     * an internal error occurs in the kernel.
     *
     * The handler function signature is:
     *
     *     function (PanicException $exception)
     *
     * For exceptions caused by a strand, $exception is an instance of
     * {@see StrandException}; otherwise, it is a {@see KernelException}.
     *
     * {@see PanicException::getPrevious()} returns the exception that
     * triggered the call to the exception handler.
     *
     * If the exception handler is unable to handle the exception it can simply
     * re-throw it (or any other exception). This causes the kernel to panic and
     * stop running. This is also the behaviour when no exception handler is set.
     *
     * @param callable|null $fn The exception handler (null = remove).
     */
    public function setExceptionHandler(?callable $fn = null): void
    {
        $this->exceptionHandler = $fn;
    }

    /**
     * Send the result of a successful operation.
     *
     * @param mixed $value The operation result.
     * @param Strand|null $strand The strand that produced this result upon exit, if any.
     */
    public function send(mixed $value = null, ?Strand $strand = null): void
    {

    }


}
