<?php

declare(strict_types=1); // @codeCoverageIgnore

namespace Recoil;


use Recoil\Strand\StrandTrait;
use Recoil\System\SystemStrand;

/**
 * Please note that this code is not part of the public API. It may be
 * changed or removed at any time without notice.
 *
 * @access private
 *
 * The reference kernel's strand implementation.
 */
final class ReferenceStrand implements SystemStrand
{
    use StrandTrait;
}
