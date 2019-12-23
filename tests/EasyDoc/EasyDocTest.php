<?php

namespace EasyDoc\Tests;

use EasyDoc\EasyDoc;

/**
 * @coversDefaultClass \EasyDoc\EasyDoc
 */
class EasyDocTest extends TestCase
{
    /**
     * @covers ::setVerbose
     * @covers ::isVerbose
     */
    public function testSetVerbose()
    {
        $doc = new EasyDoc();

        $this->assertFalse($doc->isVerbose());

        $doc->setVerbose(true);

        $this->assertTrue($doc->isVerbose());

        $doc->setVerbose(false);

        $this->assertFalse($doc->isVerbose());
    }

    /**
     * @covers ::setLayout
     * @covers ::setExtensions
     * @covers ::build
     * @covers ::info
     * @covers ::removeDirectory
     * @covers ::copyDirectory
     * @covers ::buildWebsite
     * @covers ::evaluatePhpFile
     */
    public function testSetExtensions()
    {
        $source = realpath(__DIR__.'/../doc/simple-example/source');
        $escapedSource = addslashes($source);
        $assets = realpath(__DIR__.'/../doc/simple-example/assets');
        $escapedAssets = addslashes($assets);

        ob_start();
        $doc = new EasyDoc();
        $doc->setVerbose(false);
        $doc->setEscapeCharacter('#');
        $doc->setLayout(__DIR__.'/../doc/simple-example/layout.php');
        $doc->setExtensions([
            'html',
            'php' => 'file_eval',
        ]);
        $doc->build($this->tempDirectory, $assets, $source, '/');
        $output = ob_get_contents();
        ob_end_clean();

        $this->assertSame(
            "#[1;36mBuilding website from '$escapedSource'\n".
            "#[0mBuild finished.\n",
            $output
        );
        $this->assertDirectoryImage([
            'a.html' => "<h1>Title</h1>\n3",
            'a.txt' => 'A',
            'b.html' => "<h1>Title</h1>\n<?php echo 1 + 2; ?>\n",
        ]);

        ob_start();
        $doc->setLayout(null);
        $doc->setVerbose(true);
        $doc->build($this->tempDirectory, $assets, $source, '/');
        $output = ob_get_contents();
        ob_end_clean();

        $layout = file_get_contents(__DIR__.'/../../src/EasyDoc/defaultLayout.php');
        $this->assertDirectoryImage([
            'a.html' => str_replace("<?php echo \$content ?? '' ?>\n", '3', $layout),
            'a.txt' => 'A',
            'b.html' => str_replace("<?php echo \$content ?? '' ?>\n", "<?php echo 1 + 2; ?>\n", $layout),
        ]);
        $temp = $this->tempDirectory;
        $this->assertSame(
            "#[0;36mInitializing $temp\n".
            "#[0m#[0;36mCopying assets from '$escapedAssets'\n".
            "#[0m#[1;36mBuilding website from '$escapedSource'\n".
            "#[0mBuild finished.\n",
            $output
        );

        ob_start();
        $doc->mute();
        $doc->build($this->tempDirectory, $assets, $source, '/');
        $output = ob_get_contents();
        ob_end_clean();

        $layout = file_get_contents(__DIR__.'/../../src/EasyDoc/defaultLayout.php');
        $this->assertDirectoryImage([
            'a.html' => str_replace("<?php echo \$content ?? '' ?>\n", '3', $layout),
            'a.txt' => 'A',
            'b.html' => str_replace("<?php echo \$content ?? '' ?>\n", "<?php echo 1 + 2; ?>\n", $layout),
        ]);
        $this->assertSame('', $output);

        $source = realpath(__DIR__.'/../doc/simple-example/source-empty');
        $assets = realpath(__DIR__.'/../doc/simple-example/assets-empty');

        ob_start();
        $doc->unmute();
        $doc->setVerbose(true);
        $doc->setLayout(__DIR__.'/../doc/simple-example/layout.php');
        $doc->build($this->tempDirectory, $assets, $source, '/');
        $output = ob_get_contents();
        ob_end_clean();

        $this->assertSame(
            "#[0;36mInitializing $temp\n".
            "#[0m#[0;36mCopying assets from ''\n".
            "#[0m#[0;36massets directory skipped as empty\n".
            "#[0m#[1;36mBuilding website from ''\n".
            "#[0m#[0;36msource directory skipped as empty\n".
            "#[0mBuild finished.\n",
            $output
        );
        $this->assertDirectoryImage([]);
    }
}
