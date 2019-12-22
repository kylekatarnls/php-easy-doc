# RST

Install an RST parser, for example:

```shell
composer require --dev gregwar/rst
```

And add `.md` parsing in the `'extensions'` config:

```php
<?php

use Gregwar\RST\Parser;

$parser = new Parser();

return [
    'index' => '/',
    'websiteDirectory' => __DIR__.'/../dist/website',
    'sourceDirectory' => __DIR__,
    'assetsDirectory' => __DIR__.'/assets',
    'layout' => __DIR__.'/layout.php',
    'extensions' => [
        'rst' => function ($file) use ($parser) {
            return $parser->parseFile($file);
        },
    ],
];
```
