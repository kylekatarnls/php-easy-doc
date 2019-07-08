<?php

namespace EasyDoc\Command;

use EasyDoc\Builder;
use EasyDoc\EasyDoc;
use SimpleCli\Command;
use SimpleCli\Options\Help;
use SimpleCli\Options\Quiet;
use SimpleCli\Options\Verbose;
use SimpleCli\SimpleCli;

/**
 * Build the website, its menu and its assets.
 */
class Build implements Command
{
    use Help, Quiet, Verbose;

    /**
     * @var EasyDoc
     */
    protected $cli;

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
        $this->cli = $cli;
        $this->cli->setLayout($this->config['layout'] ?? 'doc/layout.php');
        $this->cli->setExtensions($this->config['extensions'] ?? []);
        $this->config = include $this->configFile;

        $websiteDirectory = $this->config['websiteDirectory'] ?? 'dist/website';
        $sourceDir = $this->config['sourceDir'] ?? 'doc';
        $baseHref = $this->config['baseHref'] ?? '';

        $this->cli->build($websiteDirectory, $sourceDir, $baseHref);

        if ($cname = $this->config['cname'] ?? '') {
            file_put_contents($websiteDirectory.'/CNAME', $cname);
        }

        $cli->write($this->configFile, 'red');

        return true;
    }
}
