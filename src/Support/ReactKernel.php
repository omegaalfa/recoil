<?php

declare(strict_types=1);

namespace Recoil\Support;

use Exception;
use React\EventLoop\Loop;
use React\EventLoop\LoopInterface;
use Recoil\Api\Api;
use Recoil\EventQueue;
use Recoil\IO;
use Recoil\Kernel\KernelState;
use Recoil\Kernel\KernelTrait;
use Recoil\ReferenceApi;
use Recoil\ReferenceStrand;
use Recoil\Strand\Strand;
use Recoil\System\SystemKernel;

/**
 * ReactKernel integrates Recoil with React PHP event loop.
 */
final class ReactKernel implements SystemKernel
{
    use KernelTrait;

    /**
     * @var LoopInterface The React event loop.
     */
    private LoopInterface $loop;

    /**
     * @var EventQueue The queue used to schedule events.
     */
    private EventQueue $events;

    /**
     * @var IO The object used to perform IO.
     */
    private IO $io;
    /**
     * @var Api
     */
    private Api $api;


    /**
     * @var int The next strand ID.
     */
    private int $nextId = 1;

    /**
     * @var bool Whether a tick is scheduled.
     */
    private bool $tickScheduled = false;

    /**
     * @param LoopInterface $loop The React event loop.
     * @param EventQueue $events The queue used to schedule events.
     * @param IO $io The object used to perform IO.
     * @param Api $api The kernel API exposed to strands.
     */
    public function __construct(LoopInterface $loop, EventQueue $events, IO $io, Api $api)
    {
        $this->loop = $loop;
        $this->events = $events;
        $this->io = $io;
        $this->api = $api;
    }

    /**
     * Create a new kernel.
     *
     * @param mixed|null $loop Optional React event loop instance.
     * @return self
     */
    public static function create(mixed $loop = null): self
    {
        if ($loop === null) {
            $loop = Loop::get();
        }

        $events = new EventQueue();
        $io = new IO();
        $api = new ReferenceApi($events, $io);

        return new self($loop, $events, $io, $api);
    }

    /**
     * Schedule a coroutine for execution on a new strand.
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

        $this->scheduleTick();

        return $strand;
    }

    /**
     * Schedule a tick on the event loop.
     */
    private function scheduleTick(): void
    {
        if (!$this->tickScheduled) {
            $this->tickScheduled = true;
            $this->loop->futureTick(function () {
                $this->tickScheduled = false;
                $this->tick();
            });
        }
    }

    /**
     * Process pending events.
     */
    private function tick(): void
    {
        if ($this->state !== KernelState::RUNNING) {
            return;
        }

        $timeout = $this->events->tick();

        if ($timeout !== null && $this->state === KernelState::RUNNING) {
            $this->scheduleTick();
        } elseif ($timeout === null && $this->state === KernelState::RUNNING) {
            // No more events, stop the loop
            $this->loop->stop();
        }
    }

    /**
     * Stop the kernel.
     */
    public function stop(): void
    {
        if ($this->state === KernelState::RUNNING) {
            $this->state = KernelState::STOPPING;
        }
        $this->loop->stop();
    }

    /**
     * The kernel's main event loop.
     *
     * @return void
     * @throws Exception
     */
    protected function loop(): void
    {
        $this->scheduleTick();
        $this->loop->run();
    }
}
