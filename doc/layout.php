<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="Stylesheet" type="text/css" href="<?php echo $baseHref ?? ''; ?>/css/layout.css" media="screen">
    <link rel="icon" href="<?php echo $baseHref ?? ''; ?>/favicon.ico" type="image/x-icon" />

    <title>Easy-Doc</title>
</head>
<body>

<div class="wrapper">

    <div class="page">

        <h1>
            <a href="<?php echo $baseHref ?? ''; ?>/">Easy-Doc</a>
        </h1>

        <div id="content">
            <article>
                <?php

                echo $content ?? '';

                ?>
            </article>

            <aside>
                <ul>
                    <?php

                    echo $menu ?? '';

                    ?>
                </ul>
            </aside>
        </div>

    </div>

    <footer>
        <a href="https://github.com/kylekatarnls/php-easy-doc">GitHub</a>
        |
        <a href="https://github.com/kylekatarnls/php-easy-doc/blob/master/LICENSE">MIT License</a>
    </footer>
</div>

<script src="<?php echo $baseHref ?? ''; ?>/js/layout.js"></script>
<?php echo getenv('FOOTER_HOOK') ?: ''; ?>
</body>
</html>
