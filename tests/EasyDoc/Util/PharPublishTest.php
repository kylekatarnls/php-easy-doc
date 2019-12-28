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
        $size = $publisher->getTotalSizeLimit();
        $publisher->publishPhar($writer);
        $process->stop();

        $this->assertDirectoryImage([
            'download' => [
                '1.0.19' => [
                    'library.phar' => 'PHAR SAMPLE: /web/vendor/library/releases/download/1.0.19/library.phar',
                ],
                '1.3.0' => [
                    'library.phar' => 'PHAR SAMPLE: /web/vendor/library/releases/download/1.3.0/library.phar',
                ],
                'latest' => [
                    'library.phar' => 'PHAR SAMPLE: /web/vendor/library/releases/download/1.3.0/library.phar',
                ],
            ],
        ]);
        $this->assertSame(150, $size);
    }
}
