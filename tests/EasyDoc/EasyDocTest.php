<?php

namespace EasyDoc\Tests;

use EasyDoc\Command\Build;
use EasyDoc\EasyDoc;
use Parsedown;
use ReflectionMethod;

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
     * @covers ::getCommands
     */
    public function testGetCommands()
    {
        $this->assertSame([
            'build' => Build::class,
        ], (new EasyDoc())->getCommands());
    }

    /**
     * @covers ::trimExtension
     */
    public function testTrimExtension()
    {
        $trimExtension = new ReflectionMethod(EasyDoc::class, 'trimExtension');
        $trimExtension->setAccessible(true);
        $doc = new EasyDoc();

        $this->assertSame('file', $trimExtension->invoke($doc, 'file.md'));
        $this->assertSame('test.inc', $trimExtension->invoke($doc, 'test.inc.php'));
        $this->assertSame('no-extensions', $trimExtension->invoke($doc, 'no-extensions'));
    }

    /**
     * @covers ::setLayout
     * @covers ::setExtensions
     * @covers ::initializeDirectory
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
        $temp = $this->tempDirectory;

        ob_start();
        $doc = new EasyDoc();
        $doc->setVerbose(false);
        $doc->setEscapeCharacter('#');
        $doc->setLayout(__DIR__.'/../doc/simple-example/layout.php');
        $doc->setExtensions([
            'html',
            'php' => 'file_eval',
        ]);
        $doc->initializeDirectory($temp);
        $doc->build($temp, $assets, $source, '/');
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
            'css' => [
                'style.css' => '/* Foo */',
            ],
        ]);

        ob_start();
        $doc->setLayout(null);
        $doc->setVerbose(true);
        $doc->initializeDirectory($temp);
        $doc->build($temp, $assets, $source, '/');
        $output = ob_get_contents();
        ob_end_clean();

        $layout = $this->getFileContents(__DIR__.'/../../src/EasyDoc/defaultLayout.php');
        $this->assertDirectoryImage([
            'a.html' => str_replace("<?php echo \$content ?? '' ?>\n", '3', $layout),
            'a.txt' => 'A',
            'b.html' => str_replace("<?php echo \$content ?? '' ?>\n", "<?php echo 1 + 2; ?>\n", $layout),
            'css' => [
                'style.css' => '/* Foo */',
            ],
        ]);
        $this->assertSame(
            "#[0;36mInitializing $temp\n".
            "#[0m#[0;36mCopying assets from '$escapedAssets'\n".
            "#[0m#[1;36mBuilding website from '$escapedSource'\n".
            "#[0mBuild finished.\n",
            $output
        );

        ob_start();
        $doc->mute();
        $doc->initializeDirectory($temp);
        $doc->build($temp, $assets, $source, '/');
        $output = ob_get_contents();
        ob_end_clean();

        $layout = $this->getFileContents(__DIR__.'/../../src/EasyDoc/defaultLayout.php');
        $this->assertDirectoryImage([
            'a.html' => str_replace("<?php echo \$content ?? '' ?>\n", '3', $layout),
            'a.txt' => 'A',
            'b.html' => str_replace("<?php echo \$content ?? '' ?>\n", "<?php echo 1 + 2; ?>\n", $layout),
            'css' => [
                'style.css' => '/* Foo */',
            ],
        ]);
        $this->assertSame('', $output);

        $source = realpath(__DIR__.'/../doc/simple-example/source-empty');
        $assets = realpath(__DIR__.'/../doc/simple-example/assets-empty');

        ob_start();
        $doc->unmute();
        $doc->setVerbose(true);
        $doc->setLayout(__DIR__.'/../doc/simple-example/layout.php');
        $doc->initializeDirectory($temp);
        $doc->build($temp, $assets, $source, '/');
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

    /**
     * @covers ::setLayout
     * @covers ::setExtensions
     * @covers ::build
     * @covers ::removeDirectory
     * @covers ::copyDirectory
     * @covers ::buildWebsite
     * @covers ::buildMenu
     * @covers ::buildPhpMenu
     */
    public function testBuildMenu()
    {
        $temp = $this->tempDirectory;
        $source = realpath(__DIR__.'/../doc/menu-example/source');
        $escapedSource = addslashes($source);

        ob_start();
        $doc = new EasyDoc();
        $doc->setVerbose(true);
        $doc->setEscapeCharacter('#');
        $doc->setLayout(__DIR__.'/../doc/menu-example/layout.php');
        $doc->setExtensions([
            'md' => function ($file) {
                return (new Parsedown())->parse(file_get_contents($file));
            },
        ]);
        $doc->initializeDirectory($temp);
        $doc->build($temp, null, $source, '', 'install.html');
        $output = ob_get_contents();
        ob_end_clean();

        $this->assertSame(
            "#[0;36mInitializing $temp\n".
            "#[0m#[0;36mCopying assets from NULL\n".
            "#[0m#[0;36massets directory skipped as empty\n".
            "#[0m#[1;36mBuilding website from '$escapedSource'\n".
            "#[0m#[0;36mCopying $temp/install.html to $temp/index.html\n".
            "#[0mBuild finished.\n",
            $output
        );
        $install = '<section>
    <h1>Install</h1>
<p>Install with composer</p></section>
<aside>
    <li><a href="/install.html" title="Install"><strong>Install</strong></a></li><li><a href="/plugins/index.html" title="Plugins">Plugins</a></li></aside>
';
        $this->assertDirectoryImage([
            'index.html' => $install,
            'install.html' => $install,
            'plugins' =>
                [
                    'a.html' => '<section>
    <h1>A</h1></section>
<aside>
    <li><a href="/install.html" title="Install">Install</a></li><li><a href="/plugins/index.html" title="Plugins"><strong>Plugins</strong></a><ul><li><a href="/plugins/a.html" title="A"><strong>A</strong></a><li><a href="/plugins/b.html" title="B">B</a><li><a href="/plugins/more/index.html" title="More">More</a></ul></li></aside>
',
                    'b.html' => '<section>
    <h1>B</h1></section>
<aside>
    <li><a href="/install.html" title="Install">Install</a></li><li><a href="/plugins/index.html" title="Plugins"><strong>Plugins</strong></a><ul><li><a href="/plugins/a.html" title="A">A</a><li><a href="/plugins/b.html" title="B"><strong>B</strong></a><li><a href="/plugins/more/index.html" title="More">More</a></ul></li></aside>
',
                    'index.html' => '<section>
    <h1>Plugins</h1>
<ul>
<li><a href="c.md">a</a></li>
<li><a href="d.md">b</a></li>
<li><a href="more/">more</a></li>
</ul></section>
<aside>
    <li><a href="/install.html" title="Install">Install</a></li><li><a href="/plugins/index.html" title="Plugins"><strong>Plugins</strong></a><ul><li><a href="/plugins/a.html" title="A">A</a><li><a href="/plugins/b.html" title="B">B</a><li><a href="/plugins/more/index.html" title="More">More</a></ul></li></aside>
',
                    'more' =>
                        [
                            'c.html' => '<section>
    <h1>C</h1></section>
<aside>
    <li><a href="/install.html" title="Install">Install</a></li><li><a href="/plugins/index.html" title="Plugins"><strong>Plugins</strong></a><ul><li><a href="/plugins/a.html" title="A">A</a><li><a href="/plugins/b.html" title="B">B</a><li><a href="/plugins/more/index.html" title="More"><strong>More</strong></a></ul></li></aside>
',
                            'd.html' => '<section>
    <h1>D</h1></section>
<aside>
    <li><a href="/install.html" title="Install">Install</a></li><li><a href="/plugins/index.html" title="Plugins"><strong>Plugins</strong></a><ul><li><a href="/plugins/a.html" title="A">A</a><li><a href="/plugins/b.html" title="B">B</a><li><a href="/plugins/more/index.html" title="More"><strong>More</strong></a></ul></li></aside>
',
                            'index.html' => '<section>
    <h1>More plugins</h1>
<ul>
<li><a href="c.md">c</a></li>
<li><a href="d.md">d</a></li>
</ul></section>
<aside>
    <li><a href="/install.html" title="Install">Install</a></li><li><a href="/plugins/index.html" title="Plugins"><strong>Plugins</strong></a><ul><li><a href="/plugins/a.html" title="A">A</a><li><a href="/plugins/b.html" title="B">B</a><li><a href="/plugins/more/index.html" title="More"><strong>More</strong></a></ul></li></aside>
',
                        ],
                ],
        ]);

        $doc = new EasyDoc();
        $doc->mute();
        $doc->setEscapeCharacter('#');
        $doc->setLayout(__DIR__.'/../doc/simple-example/layout.php');
        $doc->setExtensions([
            'html',
            'php' => 'file_eval',
        ]);
        $doc->initializeDirectory($temp);
        $doc->build($temp, __DIR__.'/../doc/simple-example/assets', __DIR__.'/../doc/simple-example/source', '/');
        $this->assertDirectoryImage([
            'a.html' => "<h1>Title</h1>\n3",
            'a.txt' => 'A',
            'b.html' => "<h1>Title</h1>\n<?php echo 1 + 2; ?>\n",
            'css' => [
                'style.css' => '/* Foo */',
            ],
        ]);
    }

    /**
     * @covers ::buildMenu
     * @covers ::buildXmlMenu
     * @covers ::isHidden
     * @covers ::isIndex
     */
    public function testBuildXmlMenu()
    {
        $temp = $this->tempDirectory;
        $source = realpath(__DIR__.'/../doc/menu-xml-example/source');
        $escapedSource = addslashes($source);

        ob_start();
        $doc = new EasyDoc();
        $doc->setVerbose(true);
        $doc->setEscapeCharacter('#');
        $doc->setLayout(__DIR__.'/../doc/menu-xml-example/layout.php');
        $doc->setExtensions([
            'md' => function ($file) {
                return (new Parsedown())->parse(file_get_contents($file));
            },
        ]);
        $doc->initializeDirectory($temp);
        $doc->build($temp, null, $source, '', 'install.html');
        $output = ob_get_contents();
        ob_end_clean();

        $this->assertSame(
            "#[0;36mInitializing $temp\n".
            "#[0m#[0;36mCopying assets from NULL\n".
            "#[0m#[0;36massets directory skipped as empty\n".
            "#[0m#[1;36mBuilding website from '$escapedSource'\n".
            "#[0m#[0;36mCopying $temp/install.html to $temp/index.html\n".
            "#[0mBuild finished.\n",
            $output
        );
        $install = '<section>
    <h1>Install</h1>
<p>Install with composer</p></section>
<aside>
    <li><a href="/install.html" title="Install"><strong>Install</strong></a></li><li><a href="/plugins/index.html" title="Plugins">Plugins</a></li></aside>
';
        $this->assertDirectoryImage([
            'index.html' => $install,
            'install.html' => $install,
            'plugins' =>
                [
                    'a.html' => '<section>
    <h1>A</h1></section>
<aside>
    <li><a href="/install.html" title="Install">Install</a></li><li><a href="/plugins/index.html" title="Plugins"><strong>Plugins</strong></a><ul><li><a href="/plugins/a.html" title="A"><strong>A</strong></a><li><a href="/plugins/b.html" title="B">B</a><li><a href="/plugins/more/index.html" title="More">More</a></ul></li></aside>
',
                    'b.html' => '<section>
    <h1>B</h1></section>
<aside>
    <li><a href="/install.html" title="Install">Install</a></li><li><a href="/plugins/index.html" title="Plugins"><strong>Plugins</strong></a><ul><li><a href="/plugins/a.html" title="A">A</a><li><a href="/plugins/b.html" title="B"><strong>B</strong></a><li><a href="/plugins/more/index.html" title="More">More</a></ul></li></aside>
',
                    'index.html' => '<section>
    <h1>Plugins</h1>
<ul>
<li><a href="c.md">a</a></li>
<li><a href="d.md">b</a></li>
<li><a href="more/">more</a></li>
</ul></section>
<aside>
    <li><a href="/install.html" title="Install">Install</a></li><li><a href="/plugins/index.html" title="Plugins"><strong>Plugins</strong></a><ul><li><a href="/plugins/a.html" title="A">A</a><li><a href="/plugins/b.html" title="B">B</a><li><a href="/plugins/more/index.html" title="More">More</a></ul></li></aside>
',
                    'more' =>
                        [
                            'c.html' => '<section>
    <h1>C</h1></section>
<aside>
    <li><a href="/install.html" title="Install">Install</a></li><li><a href="/plugins/index.html" title="Plugins"><strong>Plugins</strong></a><ul><li><a href="/plugins/a.html" title="A">A</a><li><a href="/plugins/b.html" title="B">B</a><li><a href="/plugins/more/index.html" title="More"><strong>More</strong></a></ul></li></aside>
',
                            'd.html' => '<section>
    <h1>D</h1></section>
<aside>
    <li><a href="/install.html" title="Install">Install</a></li><li><a href="/plugins/index.html" title="Plugins"><strong>Plugins</strong></a><ul><li><a href="/plugins/a.html" title="A">A</a><li><a href="/plugins/b.html" title="B">B</a><li><a href="/plugins/more/index.html" title="More"><strong>More</strong></a></ul></li></aside>
',
                            'index.html' => '<section>
    <h1>More plugins</h1>
<ul>
<li><a href="c.md">c</a></li>
<li><a href="d.md">d</a></li>
</ul></section>
<aside>
    <li><a href="/install.html" title="Install">Install</a></li><li><a href="/plugins/index.html" title="Plugins"><strong>Plugins</strong></a><ul><li><a href="/plugins/a.html" title="A">A</a><li><a href="/plugins/b.html" title="B">B</a><li><a href="/plugins/more/index.html" title="More"><strong>More</strong></a></ul></li></aside>
',
                        ],
                ],
        ]);

        $doc = new EasyDoc();
        $doc->mute();
        $doc->setEscapeCharacter('#');
        $doc->setLayout(__DIR__.'/../doc/simple-example/layout.php');
        $doc->setExtensions([
            'html',
            'php' => 'file_eval',
        ]);
        $doc->initializeDirectory($temp);
        $doc->build($temp, __DIR__.'/../doc/simple-example/assets', __DIR__.'/../doc/simple-example/source', '/');
        $this->assertDirectoryImage([
            'a.html' => "<h1>Title</h1>\n3",
            'a.txt' => 'A',
            'b.html' => "<h1>Title</h1>\n<?php echo 1 + 2; ?>\n",
            'css' => [
                'style.css' => '/* Foo */',
            ],
        ]);
    }
}
