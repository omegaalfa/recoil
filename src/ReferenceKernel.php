<?php

declare(strict_types=1); // @codeCoverageIgnore

namespace Recoil;

use Exception;
use Recoil\Api\Api;
use Recoil\Kernel\KernelState;
use Recoil\Kernel\KernelTrait;
use Recoil\Strand\Strand;
use Recoil\System\SystemKernel;

/**
 * The reference kernel implementation.
 */
final class ReferenceKernel implements SystemKernel
{

    use KernelTrait;

    /**
     * @var EventQueue The queue used to schedule events.
     */
    private EventQueue $events;

    /**
     * @var IO The object used to perform IO.
     */
    private IO $io;

    /**
     * @var Api The kernel API exposed to strands.
     */
    private Api $api;

    /**
     * @var int The next strand ID.
     */
    private int $nextId = 1;

    /**
     * Please note that this code is not part of the public API. It may be
     * changed or removed at any time without notice.
     *
     * @access private
     *
     * This constructor is public so that it may be used by auto-wiring
     * dependency injection containers. If you are explicitly constructing an
     * instance please use one of the static factory methods listed below.
     *
     * @param EventQueue $events The queue used to schedule events.
     * @param IO $io The object used to perform IO.
     * @param Api $api The kernel API exposed to strands.
     * @see ReferenceKernel::create()
     *
     */
    public function __construct(EventQueue $events, IO $io, Api $api)
    {
        $this->events = $events;
        $this->io = $io;
        $this->api = $api;
    }


    /**
     * Create a new kernel.
     * @param mixed|null $arguments
     */
    public static function create(mixed $arguments = null): self
    {
        $events = new EventQueue();
        $io = new IO();
        $api = new ReferenceApi($events, $io);

        return new self($events, $io, $api);
    }

    /**
     * Schedule a coroutine for execution on a new strand.
     *
     * Execution begins when the kernel is run; or, if called from within a
     * strand, when that strand cooperates.
     *
     * @param mixed $coroutine The coroutine to execute.
     */
    public function execute(mixed $coroutine): Strand
    {
        $strand = new ReferenceStrand(
            $this,
            $this->api,
            $this->nextId++,
            $coroutine
        );

        $strand->setTerminator(
            $this->events->schedule(
                0,
                function () use ($strand) {
                    $strand->start();
                }
            )
        );

        return $strand;
    }


    /**
     * The kernel's main event loop. Invoked inside the run() method.
     *
     * Loop must return when $this->state is KernelState::STOPPING.
     *
     * @return void
     * @throws Exception
     */
    protected function loop(): void
    {
        do {
            $timeout = $this->events->tick();

            if ($this->state !== KernelState::RUNNING) {
                return;
            }

            $io = $this->io->tick($timeout);

        } while ($timeout !== null || $io !== IO::INACTIVE);
    }
}
