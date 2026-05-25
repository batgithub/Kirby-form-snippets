<?php

load([
    'repliq\\RepliqForm' => '/classes/form.php',
], __DIR__);

Kirby::plugin('baptiste/kirby-form-snippets', [
    'options' => [
        'placeholder' => 'Votre réponse',
        'maxLength' => [
            'input' => 1000,
            'textarea' => 3000,
            'honeypot' => 3000,
        ],
        'messages' => [
            'required' => 'Merci d\'entrer une réponse',
            'requiredSelect' => 'Merci de selectionner un élément',
            'requiredCheckbox' => 'Merci de cocher cette case',
            'requiredCheckboxGroup' => 'Merci de selectionner au moins un élément',
            'requiredRadioGroup' => 'Merci de selectionner une option',
            'email' => 'Veuillez entrer un format d\'email valide.',
            'tel' => 'Veuillez entrer un format de téléphone valide.',
            'maxLengthInput' => 'Votre réponse est limitée à 1000 caractères',
            'maxLengthTextarea' => 'Votre réponse est limitée à 3000 caractères',
            'honeypot' => 'Ouups, quelque chose s\'est mal passé. Si le problème persiste contactez moi par mail directement',
            'in' => 'La valeur selectionnée n\'est pas valide',
        ],
    ],
    'templates' => [
        'emails/submition.html' => __DIR__ . '/templates/emails/submition.html.php',
    ],
    'snippets' => [
        'form-fields' => __DIR__ . '/snippets/form-fields.php',
        'form-input' => __DIR__ . '/snippets/fields/input.php',
        'form-textarea' => __DIR__ . '/snippets/fields/textarea.php',
        'form-checkbox' => __DIR__ . '/snippets/fields/checkbox.php',
        'form-radio' => __DIR__ . '/snippets/fields/radio.php',
        'form-checkbox-group' => __DIR__ . '/snippets/fields/checkbox-group.php',
        'form-radio-group' => __DIR__ . '/snippets/fields/radio-group.php',
        'form-honeypot' => __DIR__ . '/snippets/fields/honeypot.php',
        'form-notif' => __DIR__ . '/snippets/fields/notif.php',
        'form-field-errors' => __DIR__ . '/snippets/fields/field-errors.php',
        'form-info' => __DIR__ . '/snippets/fields/info.php',
        'form-label' => __DIR__ . '/snippets/fields/label.php',
        'form-card' => __DIR__ . '/snippets/fields/card.php',
        'form-section-title' => __DIR__ . '/snippets/fields/section-title.php',
        'form-select' => __DIR__ . '/snippets/fields/select.php',
    ],
]);
