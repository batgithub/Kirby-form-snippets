<?php

Kirby::plugin('baptiste/kirby-form-snippets', [
    'snippets' => [
        'form/text' => __DIR__ . '/snippets/fields/text.php',
        'form/textarea' => __DIR__ . '/snippets/fields/text.php',
        'form/honeypot' => __DIR__ . '/snippets/fields/honeypot.php',
        'form/notif' => __DIR__ . '/snippets/fields/notif.php',
    ]
]);
