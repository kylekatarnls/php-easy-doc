<?php

namespace EasyDoc\Tests\Util;

use EasyDoc\Exception\HttpException;
use EasyDoc\Tests\TestCase;
use EasyDoc\Util\Http;

/**
 * @coversDefaultClass \EasyDoc\Util\Http
 */
class HttpTest extends TestCase
{
    /**
     * @covers ::request
     */
    public function testRequest()
    {
        $http = new Http();
        $file = str_replace('\\', '/', realpath(__DIR__.'/sample.txt'));
        $response = $http->request('file://'.(substr($file, 0, 1) === '/' ? '' : '/').$file);

        $this->assertSame('Hello from sample.txt', $response);
    }

    /**
     * @covers \EasyDoc\Exception\HttpException::<public>
     * @covers ::request
     */
    public function testRequestError()
    {
        $this->expectException(HttpException::class);
        $this->expectExceptionMessage(
            "HTTP error: file:///i-m/pretty/sure/i-do-not-exist.txt failed:\n".
            "Couldn't open file /i-m/pretty/sure/i-do-not-exist.txt"
        );

        $http = new Http();
        $file = '/i-m/pretty/sure/i-do-not-exist.txt';
        $http->request('file://'.(substr($file, 0, 1) === '/' ? '' : '/').$file);
    }

    /**
     * @covers ::request
     */
    public function testWriteInFile()
    {
        @mkdir($this->tempDirectory, 0777, true);
        $http = new Http();
        $sampleFile = __DIR__.'/sample.txt';
        $file = str_replace('\\', '/', realpath($sampleFile));
        $response = $http->request('file://'.(substr($file, 0, 1) === '/' ? '' : '/').$file, null, false, $this->tempDirectory.'/dump.txt');

        $this->assertFileEquals($sampleFile, $this->tempDirectory.'/dump.txt');
        $this->assertTrue($response);
    }

    /**
     * @covers \EasyDoc\Exception\HttpException::<public>
     * @covers ::request
     */
    public function testRequestTokenError()
    {
        $this->expectException(HttpException::class);
        $this->expectExceptionMessage('HTTP error: No Github token provided.');

        $http = new Http();
        $file = str_replace('\\', '/', realpath(__DIR__.'/sample.txt'));
        $http->request('file://'.(substr($file, 0, 1) === '/' ? '' : '/').$file, null, true);
    }
}
