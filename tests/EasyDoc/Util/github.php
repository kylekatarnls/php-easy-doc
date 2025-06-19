<?php

if (preg_match('/\.phar$/', $_SERVER['REQUEST_URI'])) {
    echo 'PHAR SAMPLE: '.$_SERVER['REQUEST_URI'];

    exit;
}

if ($_SERVER['REQUEST_URI'] === '/api/vendor/library/releases') {
    echo json_encode([
        [
            'tag_name' => '1.0.0',
            'draft' => false,
            'prerelease' => false,
        ],
        [
            'tag_name' => '1.3.0',
            'draft' => false,
            'prerelease' => false,
        ],
        [
            'tag_name' => '1.0.19',
            'draft' => false,
            'prerelease' => false,
        ],
        [
            'tag_name' => '1.4.0',
            'draft' => false,
            'prerelease' => true,
        ],
        [
            'tag_name' => '1.4.1',
            'draft' => true,
            'prerelease' => false,
        ],
    ]);

    exit;
}

echo json_encode([
    'uri' => $_SERVER['REQUEST_URI'],
    'input' => file_get_contents('php://input'),
    'headers' => getallheaders(),
]);
