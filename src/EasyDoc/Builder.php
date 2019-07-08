<?php

namespace EasyDoc;

use SimpleXMLElement;

class Builder
{
    /**
     * @var string
     */
    protected $layout;

    /**
     * @var array
     */
    protected $extensions = [];

    /**
     * Builder constructor.
     *
     * @param array $extensions
     */
    public function __construct(string $layout, array $extensions = [])
    {
        $this->layout = $layout;

        foreach (array_merge([
            'html',
        ], $extensions) as $extension => $transformation) {
            if (is_int($extension)) {
                $extension = $transformation;
                $transformation = 'file_get_contents';
            }

            if ($transformation === 'file_eval') {
                $transformation = [$this, 'evaluatePhpFile'];
            }

            $this->extensions[strtolower($extension)] = $transformation;
        }
    }

    /**
     * Build the website directory and create HTML files from RST sources.
     *
     * @param string   $websiteDirectory Output directory
     * @param string   $rstDir           Directory containing .rst files
     * @param string   $baseHref         Base of link to be used if website is deployed in a folder URI
     *
     * @return void
     */
    public function build($websiteDirectory, $rstDir, $baseHref)
    {
        $this->removeDirectory($websiteDirectory);
        @mkdir($websiteDirectory, 0777, true);
        $this->copyDirectory(__DIR__.'/../src/site/resources/web', $websiteDirectory);
        $this->buildWebsite($rstDir, $parser, $websiteDirectory, $changelogContent, $rstDir, $baseHref);
        copy($websiteDirectory.'/about.html', $websiteDirectory.'/index.html');
    }

    /**
     * Include a PHP file and returns the output buffered.
     *
     * @param string $file
     *
     * @return string
     */
    protected function evaluatePhpFile(string $file): string
    {
        ob_start();
        include $file;
        $html = ob_get_contents();
        ob_end_clean();

        return $html;
    }

    /**
     * Remove a directory and all sub-directories and files inside.
     *
     * @param string $directory
     *
     * @return void
     */
    protected function removeDirectory($directory)
    {
        if (!($dir = @opendir($directory))) {
            return;
        }

        while (false !== ($file = readdir($dir))) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            if (is_dir($directory.'/'.$file)) {
                $this->removeDirectory($directory.'/'.$file);

                continue;
            }

            unlink($directory.'/'.$file);
        }

        closedir($dir);

        @rmdir($directory);
    }

    /**
     * Deep copy a directory with all content to another directory.
     *
     * @param string $source
     * @param string $destination
     *
     * @return void
     */
    protected function copyDirectory($source, $destination)
    {
        $dir = opendir($source);
        @mkdir($destination);

        while (false !== ($file = readdir($dir))) {
            if (substr($file, 0, 1) === '.') {
                continue;
            }

            if (is_dir($source.'/'.$file)) {
                $this->copyDirectory($source.'/'.$file, $destination.'/'.$file);

                continue;
            }

            copy($source.'/'.$file, $destination.'/'.$file);
        }

        closedir($dir);
    }

    /**
     * Create HTML files from RST sources.
     *
     * @param string   $dir
     * @param string   $websiteDirectory Output directory
     * @param string   $rstDir           Directory containing .rst files
     * @param string   $baseHref         Base of link to be used if website is deployed in a folder URI
     * @param string   $base             Base path for recursion
     *
     * @return void
     */
    protected function buildWebsite($dir, $websiteDirectory, $rstDir, $baseHref, $base = '')
    {
        foreach (scandir($dir) as $item) {
            if (substr($item, 0, 1) === '.') {
                continue;
            }

            if (is_dir($dir.'/'.$item)) {
                $this->buildWebsite($dir.'/'.$item, $websiteDirectory, $rstDir, $baseHref, $base.'/'.$item);

                continue;
            }

            $parts = explode('.', $item);
            $transformation = count($parts) < 2 ? null : ($this->extensions[strtolower(end($parts))] ?? null);

            if (!$transformation) {
                continue;
            }

            $directory = $websiteDirectory.$base;

            if (!is_dir($directory)) {
                mkdir($directory, 0777, true);
            }

            $content = $transformation($dir.'/'.$item);
            $uri = $base.'/'.substr($item, 0, -4).'.html';

            $menu = $this->buildMenu($uri, $rstDir, $baseHref);

            file_put_contents($websiteDirectory.$uri, $this->evaluatePhpFile($this->layout));
        }
    }

    /**
     * Check if the node is index (that is skipped in the building of the menu)
     *
     * @param SimpleXMLElement $node menu item node
     *
     * @return bool
     */
    protected function isIndex($node)
    {
        foreach ($node->attributes() as $name => $value) {
            if ($name === 'index' && strval($value[0]) === 'true') {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if the item is hidden (that is skipped in the building of the menu)
     *
     * @param SimpleXMLElement $node menu item node
     *
     * @return bool
     */
    protected function isHidden($node)
    {
        foreach ($node->attributes() as $name => $value) {
            if ($name === 'display' && strval($value[0]) === 'false') {
                return true;
            }
        }

        return false;
    }

    /**
     * Return the menu as HTML from raw PHP array definition.
     *
     * @param string $uri      URI of the current page
     * @param string $rstDir   Directory containing .rst files
     * @param string $baseHref Base of link to be used if website is deployed in a folder URI
     *
     * @return string
     */
    protected function buildPhpMenu(string $uri, string $rstDir, string $baseHref): string
    {
        $output = '';
        $menu = include $rstDir.'/.index.php';

        foreach ($menu as $node) {
            if (!isset($node['path'], $node['name']) || ($node['hidden'] ?? false)) {
                continue;
            }

            $path = $node['path'];
            $name = $node['name'];
            $isDirectory = $node['directory'] ?? false;
            $name = htmlspecialchars(strval($name[0]));
            $path = ltrim(strval($path[0]), '/');
            $href = '/'.$path;
            $root = $isDirectory ? $href : substr($href, 0, -4).'.html';
            $href = $isDirectory ? $href.'index.html' : $root;
            $selected = substr($uri, 0, strlen($root)) === $root;
            $output .= '<li><a href="'.$baseHref.$href.'" title="'.$name.'">';
            $output .= $selected ? '<strong>'.$name.'</strong>' : $name;
            $output .= '</a>';

            if ($selected && $isDirectory && file_exists($file = $rstDir.'/'.$path.'.index.php')) {
                $upperPath = $path;
                $subMenu = include $file;

                $output .= '<ul>';

                foreach ($subMenu as $subNode) {
                    if (($subNode['index'] ?? false) || ($subNode['hidden'] ?? false)) {
                        continue;
                    }

                    $isDirectory = $node['directory'] ?? false;
                    $name = htmlspecialchars(strval($subNode['name'] ?? 'unknown'));
                    $href = '/'.$upperPath.ltrim(strval($subNode['path'] ?? 'unknown'), '/');
                    $root = $isDirectory ? $href : substr($href, 0, -4).'.html';
                    $href = $isDirectory ? $href.'index.html' : $root;
                    $output .= '<li><a href="'.$baseHref.$href.'" title="'.$name.'">';
                    $output .= substr($uri, 0, strlen($root)) === $root ? '<strong>'.$name.'</strong>' : $name;
                    $output .= '</a>';
                }

                $output .= '</ul>';
            }

            $output .= '</li>';
        }

        return $output;
    }

    /**
     * Return the menu as HTML from XML definition.
     *
     * @param string $uri      URI of the current page
     * @param string $rstDir   Directory containing .rst files
     * @param string $baseHref Base of link to be used if website is deployed in a folder URI
     *
     * @return string
     */
    protected function buildXmlMenu(string $uri, string $rstDir, string $baseHref): string
    {
        $output = '';
        $menu = simplexml_load_file($rstDir.'/.index.xml');

        foreach ($menu->children() as $node) {
            $path = $node->xpath('path');
            $name = $node->xpath('name');

            if (!isset($path[0], $name[0]) || $this->isHidden($node)) {
                continue;
            }

            $isDirectory = $node->getName() === 'directory';
            $name = htmlspecialchars(strval($name[0]));
            $path = ltrim(strval($path[0]), '/');
            $href = '/'.$path;
            $root = $isDirectory ? $href : substr($href, 0, -4).'.html';
            $href = $isDirectory ? $href.'index.html' : $root;
            $selected = substr($uri, 0, strlen($root)) === $root;
            $output .= '<li><a href="'.$baseHref.$href.'" title="'.$name.'">';
            $output .= $selected ? '<strong>'.$name.'</strong>' : $name;
            $output .= '</a>';

            if ($selected && $isDirectory && file_exists($file = $rstDir.'/'.$path.'.index.xml')) {
                $upperPath = $path;
                $subMenu = simplexml_load_file($file);

                $output .= '<ul>';

                foreach ($subMenu->children() as $subNode) {
                    if ($this->isIndex($subNode) || $this->isHidden($subNode)) {
                        continue;
                    }

                    $isDirectory = $subNode->getName() === 'directory';
                    $name = htmlspecialchars(strval($subNode->xpath('name')[0] ?? 'unknown'));
                    $href = '/'.$upperPath.ltrim(strval($subNode->xpath('path')[0] ?? 'unknown'), '/');
                    $root = $isDirectory ? $href : substr($href, 0, -4).'.html';
                    $href = $isDirectory ? $href.'index.html' : $root;
                    $output .= '<li><a href="'.$baseHref.$href.'" title="'.$name.'">';
                    $output .= substr($uri, 0, strlen($root)) === $root ? '<strong>'.$name.'</strong>' : $name;
                    $output .= '</a>';
                }

                $output .= '</ul>';
            }

            $output .= '</li>';
        }

        return $output;
    }

    /**
     * Return the menu as HTML.
     *
     * @param string $uri      URI of the current page
     * @param string $rstDir   Directory containing .rst files
     * @param string $baseHref Base of link to be used if website is deployed in a folder URI
     *
     * @return string
     */
    protected function buildMenu(string $uri, string $rstDir, string $baseHref): string
    {
        return file_exists($rstDir.'/.index.php')
            ? $this->buildPhpMenu($uri, $rstDir, $baseHref)
            : $this->buildXmlMenu($uri, $rstDir, $baseHref);
    }
}
