<?php

namespace EasyDoc\Tests;

use EasyDoc\Util\PharPublisher;
use EasyDoc\Util\SizeChecker;
use SimpleCli\Writer;

class FakePharPublisherWithSizeChecker implements PharPublisher, SizeChecker
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
    public $minimumSize;

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

    public function setPharMinimumSize(int $minimumSize): void
    {
        $this->minimumSize = $minimumSize;
    }
}
