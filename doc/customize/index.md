# Customize

The `build` command can take a config file as argument:

```shell
vendor/bin/easy-doc build doc/config.php
```

If not specified, it will try to load **.easy-doc.php** from the current
directory. If none exist, it will simply fallback to default settings.

The config file must be a PHP file returning custom settings as an array:

```php
<?php

return [
    // Specify a PHP template to use to render pages
    'layout' => __DIR__.'/layout.php',

    // Specify the $baseHref variable the layout.php will receive
    'baseHref' => '/',

    // A page to copy to index.html
    'index' => 'getting-started.html', 

    // Where to output the generated website
    'websiteDirectory' => __DIR__.'/dist/website',

    // Directory containing documentation source files
    'sourceDirectory' => __DIR__.'/doc',

    // Directory containing CSS, JS, images and other assets
    'assetsDirectory' => __DIR__.'/assets',
];
```

**layout.php** would look like:
```php
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="Stylesheet" type="text/css" href="<?php echo $baseHref ?? ''; ?>/css/layout.css" media="screen">
    <title>My library</title>
</head>
<body>

<h1>
    <a href="<?php echo $baseHref ?? ''; ?>/">My library</a>
</h1>

<div id="content">
    <?php

    echo $content ?? '';

    ?>
</div>

<ul>
    <?php

    echo $menu ?? '';

    ?>
</ul>

<footer>
    My license
</footer>

<script src="<?php echo $baseHref ?? ''; ?>/js/layout.js"></script>
</body>
</html>
```

The layout receive 2 variables `$content` and `$menu`. `$menu` contains links
in `<li></li>` items auto-generated from your documentation source directory
structure.

And `$content` is the current page content.

By default you can use HTML to create documentation pages, for instance
a **getting-started.html** page with:

```html
<h1>Getting started</h1>

<pre>composer install my-vendor/my-super-library</pre>
```

And you can specify a transformation/rendering/parsing for each file
extension of your documentation pages in your PHP config file:

```php
<?php

return [
    'index' => '/',
    'websiteDirectory' => __DIR__.'/../dist/website',
    'sourceDirectory' => __DIR__,
    'assetsDirectory' => __DIR__.'/assets',
    'layout' => __DIR__.'/layout.php',
    'extensions' => [
        'html' => function ($file) {
            return strtr(file_get_contents($file), [
                'my-vendor' => 'phpmd',
                'my-super-library' => 'phpmd',
            ]);
        },
    ],
];
```

So you can basically use any renderer and custom transformations.

Here are some examples of parser implementations:
- [Markdown](markdown.html)
- [RST](rst.html)
- [Pug](pug.html)
