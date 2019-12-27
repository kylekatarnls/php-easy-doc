<?php

namespace EasyDoc\Tests;

use EasyDoc\Util\PharPublisher;
use SimpleCli\Writer;

class FakePharPublisher implements PharPublisher
{
    /**
     * @var static
     */
    public static $lastPublisher = null;

    /**
     * @var string
     */
    public $defaultRepository;

    /**
     * @var string
     */
    public $downloadDirectory;

    /**
     * @var Writer
     */
    public $output;

    public function __construct(string $defaultRepository, string $downloadDirectory)
    {
        static::$lastPublisher = $this;
        $this->defaultRepository = $defaultRepository;
        $this->downloadDirectory = $downloadDirectory;
    }

    public function publishPhar(Writer $output = null): void
    {
        $this->output = $output;
    }
}
