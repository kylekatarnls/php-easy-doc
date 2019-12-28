<?php

namespace EasyDoc\Tests\Command;

use EasyDoc\Command\Build;
use EasyDoc\EasyDoc;
use EasyDoc\Tests\FakePharPublisher;
use EasyDoc\Tests\FakePharPublisherWithSizeLimit;
use EasyDoc\Tests\TestCase;

/**
 * @coversDefaultClass \EasyDoc\Command\Build
 */
class BuildTest extends TestCase
{
    /**
     * @covers ::run
     * @covers ::handleConfigFile
     */
    public function testMissingConfig()
    {
        $doc = new EasyDoc();
        $doc->setEscapeCharacter('#');
        $doc->mute();
        $build = new Build();
        $build->configFile = null;
        $firstRun = $build->run($doc);

        ob_start();
        $doc->unmute();
        $build->configFile = 'i-do-not-exist';
        $secondRun = $build->run($doc);
        $output = ob_get_contents();
        ob_end_clean();

        $this->assertFalse($firstRun);
        $this->assertFalse($secondRun);
        $this->assertSame("#[1;31mConfig file not found\n#[0m#[0;31mi-do-not-exist\n#[0m", $output);
    }

    /**
     * @covers ::run
     * @covers ::handleConfigFile
     */
    public function testCname()
    {
        $temp = $this->tempDirectory.'/temp';
        @mkdir($temp, 0777, true);
        chdir($temp);
        mkdir('doc/assets', 0777, true);
        file_put_contents('doc/assets/robots.txt', 'User-agent: *');
        file_put_contents('doc/assets/a.css', 'body { color: red; }');
        file_put_contents('doc/robots.txt', 'User-agent: A');
        file_put_contents('doc/index.html', '<body>Hello</body>');
        file_put_contents('doc/layout.php', '<html><?php echo $content; ?></html>');
        file_put_contents('.easy-doc.php', '<?php return [
            "cname" => "abc.com",
        ];');
        $doc = new EasyDoc();
        $doc->setEscapeCharacter('#');
        $doc->mute();
        $build = new Build();
        $run = $build->run($doc);
        $this->removeDirectory('doc');
        unlink('.easy-doc.php');

        $this->assertTrue($run);
        $this->assertDirectoryImage([
            'temp' => [
                'dist' => [
                    'website' => [
                        'CNAME' => 'abc.com',
                        'a.css' => 'body { color: red; }',
                        'index.html' => '<html><body>Hello</body></html>',
                        'robots.txt' => 'User-agent: *',
                    ],
                ],
            ],
        ]);
    }

    /**
     * @covers ::run
     * @covers ::handleConfigFile
     */
    public function testFallbackConfig()
    {
        $temp = $this->tempDirectory.'/temp';
        @mkdir($temp, 0777, true);
        chdir($temp);
        mkdir('doc/assets', 0777, true);
        file_put_contents('doc/assets/robots.txt', 'User-agent: *');
        file_put_contents('doc/assets/a.css', 'body { color: red; }');
        file_put_contents('doc/robots.txt', 'User-agent: A');
        file_put_contents('doc/index.html', '<body>Hello</body>');
        file_put_contents('doc/layout.php', '<html><?php echo $content; ?></html>');
        $doc = new EasyDoc();
        $doc->setVerbose(true);
        $doc->setEscapeCharacter('#');
        ob_start();
        $build = new Build();
        $build->verbose = true;
        $run = $build->run($doc);
        $output = ob_get_contents();
        ob_end_clean();
        $this->removeDirectory('doc');

        $this->assertTrue($run);
        $this->assertSame("#[0;36mConfig file not found, fallback to default config.\n".
            "#[0m#[0;36mInitializing dist/website\n".
            "#[0m#[0;36mCopying assets from 'doc/assets'\n".
            "#[0m#[1;36mBuilding website from 'doc'\n".
            "#[0mBuild finished.\n",
            $output
        );
        $this->assertDirectoryImage([
            'temp' => [
                'dist' => [
                    'website' => [
                        'a.css' => 'body { color: red; }',
                        'index.html' => '<html><body>Hello</body></html>',
                        'robots.txt' => 'User-agent: *',
                    ],
                ],
            ],
        ]);
    }

    /**
     * @covers ::run
     */
    public function testPharPublish()
    {
        $temp = $this->tempDirectory.'/temp';
        @mkdir($temp, 0777, true);
        chdir($temp);
        file_put_contents('.easy-doc.php', '<?php return [
            "pharPublisher" => "\\EasyDoc\\Tests\\FakePharPublisher",
            "publishPhar" => "vendor/library",
        ];');
        $doc = new EasyDoc();
        $doc->setEscapeCharacter('#');
        $doc->mute();
        $build = new Build();
        $run = $build->run($doc);
        $this->removeDirectory('doc');
        unlink('.easy-doc.php');

        $this->assertTrue($run);
        $this->assertSame($doc, FakePharPublisher::$lastPublisher->output);
        $this->assertSame('vendor/library', FakePharPublisher::$lastPublisher->defaultRepository);
        $this->assertSame('dist/website/static/', FakePharPublisher::$lastPublisher->downloadDirectory);
    }

    /**
     * @covers ::run
     */
    public function testPharPublishSizeLimit()
    {
        $temp = $this->tempDirectory.'/temp';
        @mkdir($temp, 0777, true);
        chdir($temp);
        file_put_contents('.easy-doc.php', '<?php return [
            "pharPublisher" => "\\EasyDoc\\Tests\\FakePharPublisher",
            "publishPhar" => [
                "repository" => "vendor/library",
                "sizeLimit" => 2000,
            ],
        ];');
        $doc = new EasyDoc();
        $doc->setEscapeCharacter('#');
        $doc->mute();
        $build = new Build();
        $run = $build->run($doc);
        $this->removeDirectory('doc');
        unlink('.easy-doc.php');

        $this->assertTrue($run);
        $this->assertInstanceOf(FakePharPublisher::class, FakePharPublisher::$lastPublisher);
        $this->assertSame($doc, FakePharPublisher::$lastPublisher->output);
        $this->assertSame('vendor/library', FakePharPublisher::$lastPublisher->defaultRepository);
        $this->assertSame('dist/website/static/', FakePharPublisher::$lastPublisher->downloadDirectory);

        $temp = $this->tempDirectory.'/temp';
        @mkdir($temp, 0777, true);
        chdir($temp);
        file_put_contents('.easy-doc.php', '<?php return [
            "pharPublisher" => "\\EasyDoc\\Tests\\FakePharPublisherWithSizeLimit",
            "publishPhar" => [
                "repository" => "vendor/library",
                "sizeLimit" => 2000,
            ],
        ];');
        $doc = new EasyDoc();
        $doc->setEscapeCharacter('#');
        $doc->mute();
        $build = new Build();
        $run = $build->run($doc);
        $this->removeDirectory('doc');
        unlink('.easy-doc.php');

        $this->assertTrue($run);
        $this->assertInstanceOf(FakePharPublisherWithSizeLimit::class, FakePharPublisherWithSizeLimit::$lastPublisher);
        $this->assertSame(2000, FakePharPublisherWithSizeLimit::$lastPublisher->sizeLimit);
        $this->assertSame($doc, FakePharPublisherWithSizeLimit::$lastPublisher->output);
        $this->assertSame('vendor/library', FakePharPublisherWithSizeLimit::$lastPublisher->defaultRepository);
        $this->assertSame('dist/website/static/', FakePharPublisherWithSizeLimit::$lastPublisher->downloadDirectory);
    }
}
