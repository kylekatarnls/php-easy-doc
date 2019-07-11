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
        $this->config = file_exists($this->configFile) ? include $this->configFile : [];

        $websiteDirectory = $this->config['websiteDirectory'] ?? 'dist/website';
        $assetsDirectory = $this->config['assetsDirectory'] ?? "$websiteDirectory/assets";
        $sourceDir = $this->config['sourceDirectory'] ?? 'doc';
        $baseHref = $this->config['baseHref'] ?? '';

        $this->cli = $cli;
        $this->cli->setLayout($this->config['layout'] ?? "$sourceDir/layout.php");
        $this->cli->setExtensions($this->config['extensions'] ?? []);
        $this->cli->setVerbose($this->verbose);

        $this->cli->build($websiteDirectory, $assetsDirectory, $sourceDir, $baseHref, $this->config['index'] ?? null);

        if ($cname = $this->config['cname'] ?? '') {
            file_put_contents($websiteDirectory.'/CNAME', $cname);
        }

        return true;
    }
}
