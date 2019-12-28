<?php

namespace EasyDoc\Tests;

use EasyDoc\Util\PharPublisher;
use EasyDoc\Util\SizeLimiter;
use SimpleCli\Writer;

class FakePharPublisherWithSizeLimit implements PharPublisher, SizeLimiter
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

    /**
     * @var int
     */
    public $sizeLimit;

    public function __construct(string $defaultRepository, string $downloadDirectory)
    {
        static::$lastPublisher = $this;
        $this->defaultRepository = $defaultRepository;
        $this->downloadDirectory = $downloadDirectory;
    }

    public function publishPhar(Writer $output = null, string $fileName = null): void
    {
        $this->output = $output;
    }

    public function setTotalSizeLimit(int $totalSizeLimit): void
    {
        $this->sizeLimit = $totalSizeLimit;
    }
}
