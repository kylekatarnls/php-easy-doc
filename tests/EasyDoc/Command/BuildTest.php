<?php

namespace EasyDoc\Tests\Command;

use EasyDoc\Command\Build;
use EasyDoc\EasyDoc;
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
}
