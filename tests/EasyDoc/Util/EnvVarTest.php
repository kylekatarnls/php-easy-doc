<?php

namespace EasyDoc\Tests\Util;

use EasyDoc\Tests\TestCase;
use EasyDoc\Util\EnvVar;

/**
 * @coversDefaultClass \EasyDoc\Util\EnvVar
 */
class EnvVarTest extends TestCase
{
    /**
     * @covers ::reset
     * @covers ::__construct
     * @covers ::getValue
     */
    public function testEnvFileReading()
    {
        EnvVar::reset();
        @mkdir($this->tempDirectory, 0777, true);
        chdir($this->tempDirectory);
        file_put_contents('.env', 'MY_CUSTOM_ENV_VAR=foobar');

        ob_start();
        $env = new EnvVar('MY_CUSTOM_ENV_VAR');
        $output = ob_get_contents();
        ob_end_clean();

        $this->assertSame('foobar', $env->getValue());
        $this->assertSame(".env file loaded:\n - MY_CUSTOM_ENV_VAR\n", $output);

        ob_start();
        $env = new EnvVar('MY_CUSTOM_ENV_VAR');
        $output = ob_get_contents();
        ob_end_clean();

        $this->assertSame('foobar', $env->getValue());
        $this->assertSame('', $output);
    }

    /**
     * @covers ::reset
     * @covers ::__construct
     * @covers ::getValue
     */
    public function testNullVar()
    {
        EnvVar::reset();
        @mkdir($this->tempDirectory, 0777, true);
        chdir($this->tempDirectory);

        ob_start();
        $env = new EnvVar('MY_OTHER_CUSTOM_ENV_VAR');
        $output = ob_get_contents();
        ob_end_clean();

        $this->assertNull($env->getValue());
        $this->assertSame("no .env file.\n", $output);
    }

    /**
     * @covers ::toString
     * @covers ::__toString
     */
    public function testEnvToString()
    {
        EnvVar::reset();
        @mkdir($this->tempDirectory, 0777, true);
        chdir($this->tempDirectory);
        file_put_contents('.env', 'MY_CUSTOM_ENV_VAR=foobar');

        ob_start();
        $env = new EnvVar('MY_CUSTOM_ENV_VAR');
        ob_end_clean();

        $this->assertSame('foobar', "$env");
        $this->assertSame('foobar', EnvVar::toString('MY_CUSTOM_ENV_VAR'));

        $env = new EnvVar('MY_OTHER_CUSTOM_ENV_VAR');

        $this->assertSame('', "$env");
        $this->assertSame('', EnvVar::toString('MY_OTHER_CUSTOM_ENV_VAR'));
    }
}
