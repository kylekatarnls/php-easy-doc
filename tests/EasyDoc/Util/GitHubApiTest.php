<?php

namespace EasyDoc\Tests\Util;

use EasyDoc\Tests\TestCase;
use EasyDoc\Util\EnvVar;
use EasyDoc\Util\GitHubApi;
use Symfony\Component\Process\Process;

/**
 * @coversDefaultClass \EasyDoc\Util\GitHubApi
 */
class GitHubApiTest extends TestCase
{
    /**
     * @covers ::__construct
     * @covers ::download
     * @covers ::webRequest
     * @covers ::prefixRequest
     */
    public function testDownload()
    {
        @mkdir($this->tempDirectory, 0777, true);
        chdir(__DIR__);
        $process = new Process(['php', '-S=localhost:9245', 'github.php']);
        $process->start();

        usleep(100000);

        EnvVar::reset();
        ob_start();
        $api = new GitHubApi('vendor/library', 'download', 'http://localhost:9245/web/', 'http://localhost:9245/api/');
        $response = $api->download($this->tempDirectory.'/file', 'suffix');
        ob_end_clean();
        $process->stop();

        $this->assertTrue($response);

        $response = json_decode(file_get_contents($this->tempDirectory.'/file'));

        $this->assertSame('/web/vendor/library/suffix', $response->uri);
        $this->assertSame('application/json', $response->headers->{'Content-Type'});
        $this->assertSame('token abc123', $response->headers->Authorization);
        $this->assertSame('', $response->input);
    }

    /**
     * @covers ::__construct
     * @covers ::json
     * @covers ::apiRequest
     * @covers ::prefixRequest
     */
    public function testJson()
    {
        @mkdir($this->tempDirectory, 0777, true);
        chdir(__DIR__);
        $process = new Process(['php', '-S=localhost:9245', 'github.php']);
        $process->start();

        usleep(100000);

        EnvVar::reset();
        ob_start();
        $api = new GitHubApi('vendor/library', 'download', 'http://localhost:9245/web/', 'http://localhost:9245/api/');
        $response = $api->json('suffix');
        ob_end_clean();
        $process->stop();

        $this->assertSame('/api/vendor/library/suffix', $response->uri);
        $this->assertSame('application/json', $response->headers->{'Content-Type'});
        $this->assertSame('token abc123', $response->headers->Authorization);
        $this->assertSame('', $response->input);
    }
}
