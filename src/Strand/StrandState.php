<?php

declare(strict_types=1);


namespace Recoil\Strand;

class StrandState
{

    const int EXITED = 1;
    const int READY = 2;
    const int RUNNING = 3;
    const int SUSPENDED_ACTIVE = 4;
    const int SUSPENDED_INACTIVE = 5;
}