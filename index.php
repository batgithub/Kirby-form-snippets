<?php


Kirby::plugin('baptiste/kirby-form-snippets', [
    'snippets' => [
        'form-input' => __DIR__ . '/snippets/fields/input.php',
        'form-textarea' => __DIR__ . '/snippets/fields/textarea.php',
        'form-checkbox' => __DIR__ . '/snippets/fields/checkbox.php',
        'form-radio' => __DIR__ . '/snippets/fields/radio.php',
        'form-checkbox-group' => __DIR__ . '/snippets/fields/checkbox-group.php',
        'form-radio-group' => __DIR__ . '/snippets/fields/radio-group.php',
        'form-honeypot' => __DIR__ . '/snippets/fields/honeypot.php',
        'form-notif' => __DIR__ . '/snippets/fields/notif.php',
        'form-info' => __DIR__ . '/snippets/fields/info.php',
        'form-label' => __DIR__ . '/snippets/fields/label.php',
        'form-card' => __DIR__ . '/snippets/fields/card.php'
    ]
]);
