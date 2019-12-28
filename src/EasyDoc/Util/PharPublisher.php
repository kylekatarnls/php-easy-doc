<?php

namespace EasyDoc\Util;

use SimpleCli\Writer;

interface PharPublisher
{
    public function __construct(string $defaultRepository, string $downloadDirectory);

    public function publishPhar(Writer $output = null, string $fileName = null): void;
}
