<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/kirby/bootstrap.php';

return new Kirby([
    'roots' => [
        'content' => __DIR__ . '/content',
    ],
    'urls' => [
        'index' => 'http://localhost',
    ],
]);
