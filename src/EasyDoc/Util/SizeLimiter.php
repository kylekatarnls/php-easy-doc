<?php

namespace EasyDoc\Util;

interface SizeLimiter
{
    public const DEFAULT_MAXIMUM_SIZE = 94371840;

    public function setTotalSizeLimit(int $totalSizeLimit): void;
}
