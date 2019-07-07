<?php

namespace EasyDoc;

use EasyDoc\Command\Build;
use SimpleCli\SimpleCli;

class EasyDocCli extends SimpleCli
{
    protected $name = 'easy-doc';

    public function getCommands(): array
    {
        return [
            'build' => Build::class,
        ];
    }
}
