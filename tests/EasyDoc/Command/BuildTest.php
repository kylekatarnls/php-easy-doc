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
}
