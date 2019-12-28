<?php

namespace EasyDoc\Util;

interface SizeLimiter
{
    public function setTotalSizeLimit(int $totalSizeLimit): void;
}
