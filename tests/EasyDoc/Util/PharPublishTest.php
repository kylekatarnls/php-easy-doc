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
}
