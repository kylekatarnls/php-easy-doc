<?php

namespace EasyDoc\Tests\Util;

use EasyDoc\Tests\TestCase;
use EasyDoc\Util\EnvVar;
use EasyDoc\Util\PharPublish;

/**
 * @coversDefaultClass \EasyDoc\Util\PharPublish
 */
class PharPublishTest extends TestCase
{
    /**
     * @covers ::publishPhar
     * @covers ::write
     */
    public function testPublishPharWithoutToken()
    {
        ob_start();
        EnvVar::reset();
        EnvVar::toString('GITHUB_TOKEN');
        ob_end_clean();
        $publisher = new PharPublish('vendor/library', 'download', 'http://localhost:9245/web/', 'http://localhost:9245/api/');
        ob_start();
        $publisher->publishPhar(null);
        $output = ob_get_contents();
        ob_end_clean();

        $this->assertSame("PHAR publishing skipped as GITHUB_TOKEN is missing.\n", $output);

        $writer = new FakeWriter();
        ob_start();
        $publisher->publishPhar($writer);
        $output = ob_get_contents();
        ob_end_clean();

        $this->assertSame('', $output);
        $this->assertSame([["PHAR publishing skipped as GITHUB_TOKEN is missing.\n", 'yellow', null]], $writer->output);
    }

    /**
     * @covers ::publishPhar
     */
    public function testPublishPhar()
    {
        chdir(__DIR__);
        ob_start();
        $process = $this->startServer('github.php');
        EnvVar::reset();
        EnvVar::toString('GITHUB_TOKEN');
        ob_end_clean();
        $writer = new FakeWriter();
        $publisher = new PharPublish('vendor/library', $this->tempDirectory.'/download/', 'http://localhost:9245/web/', 'http://localhost:9245/api/');
        $publisher->setPharMinimumSize(0);
        $publisher->publishPhar($writer);
        $process->stop();

        $this->assertDirectoryImage([
            'download' => [
                '1.0.0' => [
                    'library.phar' => 'PHAR SAMPLE: /web/vendor/library/releases/download/1.0.0/library.phar',
                ],
                '1.0.19' => [
                    'library.phar' => 'PHAR SAMPLE: /web/vendor/library/releases/download/1.0.19/library.phar',
                ],
                '1.3.0' => [
                    'library.phar' => 'PHAR SAMPLE: /web/vendor/library/releases/download/1.3.0/library.phar',
                ],
                '1.4.0' => [
                    'library.phar' => 'PHAR SAMPLE: /web/vendor/library/releases/download/1.4.0/library.phar',
                ],
                '1.4.1' => [
                    'library.phar' => 'PHAR SAMPLE: /web/vendor/library/releases/download/1.4.1/library.phar',
                ],
                'latest' => [
                    'library.phar' => 'PHAR SAMPLE: /web/vendor/library/releases/download/1.3.0/library.phar',
                ],
            ],
        ]);
    }

    /**
     * @covers ::getTotalSizeLimit
     * @covers ::setTotalSizeLimit
     * @covers ::publishPhar
     */
    public function testSizeLimit()
    {
        chdir(__DIR__);
        ob_start();
        $process = $this->startServer('github.php');
        EnvVar::reset();
        EnvVar::toString('GITHUB_TOKEN');
        ob_end_clean();
        $writer = new FakeWriter();
        $publisher = new PharPublish('vendor/library', $this->tempDirectory.'/download/', 'http://localhost:9245/web/', 'http://localhost:9245/api/');
        $publisher->setTotalSizeLimit(150);
        $publisher->setPharMinimumSize(0);
        $size = $publisher->getTotalSizeLimit();
        $publisher->publishPhar($writer);
        $process->stop();

        $this->assertDirectoryImage([
            'download' => [
                '1.4.0' => [
                    'library.phar' => 'PHAR SAMPLE: /web/vendor/library/releases/download/1.4.0/library.phar',
                ],
                '1.4.1' => [
                    'library.phar' => 'PHAR SAMPLE: /web/vendor/library/releases/download/1.4.1/library.phar',
                ],
                'latest' => [
                    'library.phar' => 'PHAR SAMPLE: /web/vendor/library/releases/download/1.3.0/library.phar',
                ],
            ],
        ]);
        $this->assertSame(150, $size);
    }

    /**
     * @covers ::publishPhar
     * @covers ::setPharMinimumSize
     * @covers ::getPharMinimumSize
     * @covers ::getHumanSize
     * @covers ::formatNumber
     */
    public function testMinimumSize()
    {
        chdir(__DIR__);
        ob_start();
        $process = $this->startServer('github.php');
        EnvVar::reset();
        EnvVar::toString('GITHUB_TOKEN');
        ob_end_clean();
        $writer = new FakeWriter();
        $publisher = new PharPublish('vendor/library', $this->tempDirectory.'/download/', 'http://localhost:9245/web/', 'http://localhost:9245/api/');
        $publisher->setPharMinimumSize(204800);
        $size = $publisher->getPharMinimumSize();
        $publisher->publishPhar($writer);
        $process->stop();

        $this->assertDirectoryImage([
            'download' => [],
        ]);
        $this->assertSame(204800, $size);

        $this->assertSame(
            [
                [
                    $this->tempDirectory."/download/1.4.1/library.phar skipped because it's only 69.0 B while at least 200 kB is expected.\n",
                    'light_red',
                    null,
                ],
                [
                    $this->tempDirectory."/download/1.4.0/library.phar skipped because it's only 69.0 B while at least 200 kB is expected.\n",
                    'light_red',
                    null,
                ],
                [
                    $this->tempDirectory."/download/1.3.0/library.phar skipped because it's only 69.0 B while at least 200 kB is expected.\n",
                    'light_red',
                    null,
                ],
                [
                    $this->tempDirectory."/download/1.0.19/library.phar skipped because it's only 70.0 B while at least 200 kB is expected.\n",
                    'light_red',
                    null,
                ],
                [
                    $this->tempDirectory."/download/1.0.0/library.phar skipped because it's only 69.0 B while at least 200 kB is expected.\n",
                    'light_red',
                    null,
                ],
            ],
            $writer->output
        );
    }

    /**
     * @covers ::getHumanSize
     */
    public function testVeryBigMinimumSize()
    {
        chdir(__DIR__);
        ob_start();
        $process = $this->startServer('github.php');
        EnvVar::reset();
        EnvVar::toString('GITHUB_TOKEN');
        ob_end_clean();
        $writer = new FakeWriter();
        $publisher = new PharPublish('vendor/library', $this->tempDirectory.'/download/', 'http://localhost:9245/web/', 'http://localhost:9245/api/');
        $publisher->setPharMinimumSize(1024 * 1024 * 1024 * 1024 * 987654);
        $size = $publisher->getPharMinimumSize();
        $publisher->publishPhar($writer);
        $process->stop();

        $this->assertDirectoryImage([
            'download' => [],
        ]);
        $this->assertSame(1024 * 1024 * 1024 * 1024 * 987654, $size);

        $this->assertSame(
            [
                [
                    $this->tempDirectory."/download/1.4.1/library.phar skipped because it's only 69.0 B while at least 987,654 TB is expected.\n",
                    'light_red',
                    null,
                ],
                [
                    $this->tempDirectory."/download/1.4.0/library.phar skipped because it's only 69.0 B while at least 987,654 TB is expected.\n",
                    'light_red',
                    null,
                ],
                [
                    $this->tempDirectory."/download/1.3.0/library.phar skipped because it's only 69.0 B while at least 987,654 TB is expected.\n",
                    'light_red',
                    null,
                ],
                [
                    $this->tempDirectory."/download/1.0.19/library.phar skipped because it's only 70.0 B while at least 987,654 TB is expected.\n",
                    'light_red',
                    null,
                ],
                [
                    $this->tempDirectory."/download/1.0.0/library.phar skipped because it's only 69.0 B while at least 987,654 TB is expected.\n",
                    'light_red',
                    null,
                ],
            ],
            $writer->output
        );
    }
}
