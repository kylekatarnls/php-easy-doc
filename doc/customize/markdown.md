# Markdown

Install a Markdown parser, for example:

```shell
composer require --dev erusev/parsedown
```

And add `.md` parsing in the `'extensions'` config:

```php
<?php

$parser = new Parsedown();

return [
    'index' => '/',
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
```
