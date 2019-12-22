<?php

$parser = new Parsedown();

return [
    'baseHref' => '/',
    'index' => 'getting-started.html',
    'websiteDirectory' => __DIR__.'/../dist/website',
    'sourceDirectory' => __DIR__,
    'assetsDirectory' => __DIR__.'/assets',
    'layout' => __DIR__.'/layout.php',
    'extensions' => [
        'md' => function ($file) use ($parser) {
            return $parser->text(file_get_contents($file));
        },
    ],
];
