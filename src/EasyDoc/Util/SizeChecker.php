<?php

namespace EasyDoc\Util;

interface SizeChecker
{
    public const DEFAULT_MINIMUM_PHAR_SIZE = 1024;

    public function setPharMinimumSize(int $pharMinimumSize): void;
}
