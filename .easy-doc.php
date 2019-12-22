<?php

use EasyDoc\Util\EnvVar;

$parser = new Parsedown();

return [
    'baseHref' => EnvVar::toString('BASE_HREF'),
    'index' => 'getting-started.html',
    'websiteDirectory' => __DIR__.'/dist/website',
    'sourceDirectory' => __DIR__.'/doc',
    'assetsDirectory' => __DIR__.'/doc/assets',
    'layout' => __DIR__.'/doc/layout.php',
    'extensions' => [
        'md' => function ($file) use ($parser) {
            return $parser->text(file_get_contents($file));
        },
    ],
];
