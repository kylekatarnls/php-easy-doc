<?php

namespace EasyDoc\Command;

use EasyDoc\Builder;
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
     * @var array
     */
    protected $config = [];

    /**
     * @argument
     *
     * Config file to be used.
     *
     * @var string
     */
    public $configFile = '.easy-doc.php';

    public function run(SimpleCli $cli): bool
    {
        $this->config = include $this->configFile;

        $websiteDirectory = $this->config['websiteDirectory'] ?? 'dist/website';
        $sourceDir = $this->config['sourceDir'] ?? 'doc';
        $baseHref = $this->config['baseHref'] ?? '';

        (new Builder())->build($websiteDirectory, $sourceDir, $baseHref);

        if ($cname = $this->config['cname'] ?? '') {
            file_put_contents($websiteDirectory.'/CNAME', $cname);
        }

        $cli->write($this->configFile, 'red');

        return true;
    }
}
