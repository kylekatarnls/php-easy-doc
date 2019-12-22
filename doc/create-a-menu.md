# Create a menu

The directory you specify as `'sourceDirectory'` in your config file will
contain the pages of your documentation and may contain a **.index.php**
file to configure the menu.

For instance:
```php
<?php

return [
    [
        'index' => true,
        'name' => 'Getting started',
        'path' => 'getting-started.md',
    ],
    [
        'name' => 'Customize',
        'path' => 'customize/',
        'directory' => true,
    ],
    [
        'name' => 'Create a menu',
        'path' => 'create-a-menu.md',
    ],
];
```

The **.index.php** should return an array of the menu items.

Each item should be an array containing at least a **name** and a **path**.

One of the item should contain `'index' => true` to mark it as the index
of the menu.

And items containing sub-items should be marked as `'directory' => true` and its
path should a sub-directory of `'sourceDirectory'` this sub-directory can
contain its own **.index.php** file containing sub-items definitions with
relative paths.
