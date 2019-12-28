<?php

namespace EasyDoc\Tests\Util;

use SimpleCli\Writer;

class FakeWriter implements Writer
{
    public $output = [];

    public function write(string $text = '', string $color = null, string $background = null): void
    {
        $this->output[] = [$text, $color, $background];
    }
}
