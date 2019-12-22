<?php

namespace EasyDoc\Command;

use EasyDoc\EasyDoc;
use EasyDoc\Util\PharPublish;
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

    const DEFAULT_CONFIG_FILE = '.easy-doc.php';

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
    public $configFile = self::DEFAULT_CONFIG_FILE;

    public function run(SimpleCli $cli): bool
    {
        $this->cli = $cli;

        if (!$this->handleConfigFile()) {
            return false;
        }

        $websiteDirectory = $this->config['websiteDirectory'] ?? 'dist/website';
        $assetsDirectory = $this->config['assetsDirectory'] ?? "$websiteDirectory/assets";
        $sourceDir = $this->config['sourceDirectory'] ?? 'doc';
        $baseHref = $this->config['baseHref'] ?? '';

        $this->cli->setLayout($this->config['layout'] ?? "$sourceDir/layout.php");
        $this->cli->setExtensions($this->config['extensions'] ?? []);
        $this->cli->setVerbose($this->verbose);

        if (isset($this->config['publishPhar'])) {
            $config = is_string($this->config['publishPhar'])
                ? ['repository' => $this->config['publishPhar']]
                : $this->config['publishPhar'];
            $publishPhar = new PharPublish($config['repository'], $config['directory'] ?? $websiteDirectory.'/static/');
            $publishPhar->publishPhar($this->cli);
        }

        $this->cli->build($websiteDirectory, $assetsDirectory, $sourceDir, $baseHref, $this->config['index'] ?? null);

        if ($cname = $this->config['cname'] ?? '') {
            file_put_contents($websiteDirectory.'/CNAME', $cname);
        }

        return true;
    }

    protected function handleConfigFile()
    {
        if ($this->configFile) {
            if (!file_exists($this->configFile)) {
                if ($this->configFile !== self::DEFAULT_CONFIG_FILE) {
                    $this->cli->writeLine('Config file not found', 'light_red');
                    $this->cli->write(strval($this->configFile), 'red');

                    return false;
                }

                $this->cli->info('Config file not found, fallback to default config.');

                return true;
            }

            $this->config = include $this->configFile;
        }

        return true;
    }
}
