<?php

namespace EasyDoc\Command;

use SimpleCli\Command;
use SimpleCli\Options\Help;
use SimpleCli\SimpleCli;

/**
 * Build the website, its menu and its assets.
 */
class Build implements Command
{
    use Help;

    /**
     * @argument
     *
     * Config file to be used.
     *
     * @var string
     */
    public $config = '.easy-doc.php';

    public function run(SimpleCli $cli): bool
    {
        $cli->write($this->config, 'red');

        return true;
    }
}
