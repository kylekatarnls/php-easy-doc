<?php

echo json_encode([
    'uri' => $_SERVER['REQUEST_URI'],
    'input' => file_get_contents('php://input'),
    'headers' => getallheaders(),
]);
