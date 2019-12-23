# Pug

Install a Pug parser, for example:

```shell
composer require pug-php/pug --dev
```

*Use `--dev` only if **easy-doc** itself is installed in the `"require-dev"` of your composer.json*

And add `.md` parsing in the `'extensions'` config:

```php
<?php

use Pug\Facade;

return [
    'index' => '/',
    'websiteDirectory' => __DIR__.'/../dist/website',
    'sourceDirectory' => __DIR__,
    'assetsDirectory' => __DIR__.'/assets',
    'layout' => __DIR__.'/layout.php',
    'extensions' => [
        'pug' => function ($file) use ($parser) {
            return Facade::renderFile($file);
        },
    ],
];
```
