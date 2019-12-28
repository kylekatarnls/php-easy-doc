<?php

namespace EasyDoc\Command;

use EasyDoc\EasyDoc;
use EasyDoc\Util\PharPublish;
use EasyDoc\Util\PharPublisher;
use EasyDoc\Util\SizeLimiter;
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
        $sourceDir = $this->config['sourceDirectory'] ?? 'doc';
        $assetsDirectory = $this->config['assetsDirectory'] ?? "$sourceDir/assets";
        $baseHref = $this->config['baseHref'] ?? '';

        $this->cli->setLayout($this->config['layout'] ?? "$sourceDir/layout.php");
        $this->cli->setExtensions($this->config['extensions'] ?? []);
        $this->cli->setVerbose($this->verbose);
        $this->cli->initializeDirectory($websiteDirectory);

        if (isset($this->config['publishPhar'])) {
            $config = is_string($this->config['publishPhar'])
                ? ['repository' => $this->config['publishPhar']]
                : $this->config['publishPhar'];
            $pharPublisher = $config['publisher'] ?? $this->config['pharPublisher'] ?? PharPublish::class;
            /** @var PharPublisher $publishPhar */
            $publishPhar = new $pharPublisher($config['repository'], $config['directory'] ?? $websiteDirectory.'/static/');

            if (isset($config['sizeLimit']) && $publishPhar instanceof SizeLimiter) {
                $publishPhar->setTotalSizeLimit($config['sizeLimit']);
            }

            $publishPhar->publishPhar($this->cli, $config['fileName'] ?? null);
            unset($publishPhar);
        }

        $this->cli->build($websiteDirectory, $assetsDirectory, $sourceDir, $baseHref, $this->config['index'] ?? null);

        if ($cname = $this->config['cname'] ?? '') {
            file_put_contents($websiteDirectory.'/CNAME', $cname);
        }

        return true;
    }

    protected function handleConfigFile()
    {
        if (!$this->configFile) {
            return false;
        }

        if (!file_exists($this->configFile)) {
            if ($this->configFile !== self::DEFAULT_CONFIG_FILE) {
                $this->cli->writeLine('Config file not found', 'light_red');
                $this->cli->writeLine(strval($this->configFile), 'red');

                return false;
            }

            $this->cli->info('Config file not found, fallback to default config.');

            return true;
        }

        $this->config = include $this->configFile;

        return true;
    }
}
