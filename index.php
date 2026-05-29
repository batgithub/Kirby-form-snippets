<?php

load([
    'repliq\\RepliqFilterState' => '/classes/filter-state.php',
    'repliq\\RepliqForm' => '/classes/form.php',
    'repliq\\RepliqPreviewState' => '/classes/preview-state.php',
], __DIR__);

require_once __DIR__ . '/helpers.php';

Kirby::plugin('baptiste/kirby-form-snippets', [
    'options' => [
        'placeholder' => 'Votre réponse',
        'forms' => [],
        'maxLength' => [
            'input' => 1000,
            'textarea' => 3000,
            'honeypot' => 3000,
        ],
        'honeytime' => [
            'enabled' => false,
            'key' => null,
            'seconds' => 10,
            'field' => 'uniform-honeytime',
            'route' => 'kirby-form-snippets/honeytime-token',
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
            'spam' => 'Oups, l\'envoi n\'a pas abouti. Si le problème persiste, contactez-nous par e-mail.',
            'honeypot' => 'Oups, l\'envoi n\'a pas abouti. Si le problème persiste, contactez-nous par e-mail.',
            'honeytime' => 'Oups, l\'envoi n\'a pas abouti. Si le problème persiste, contactez-nous par e-mail.',
            'honeytimeInvalid' => 'Oups, l\'envoi n\'a pas abouti. Si le problème persiste, contactez-nous par e-mail.',
            'in' => 'La valeur selectionnée n\'est pas valide',
            'submit' => 'L\'envoi du formulaire a échoué. Veuillez réessayer ou nous contacter directement.',
        ],
        'errorsSummary' => [
            'enabled' => false,
            'title' => 'Le formulaire contient des erreurs',
        ],
        'htmx' => [
            'enabled' => false,
            'swap' => 'outerHTML',
            'target' => null,
            'indicator' => null,
            'disabledElt' => 'find button[type=submit]',
            'loadScript' => true,
            'script' => 'https://cdn.jsdelivr.net/npm/htmx.org@2.0.10/dist/htmx.min.js',
            'scriptIntegrity' => 'sha384-H5SrcfygHmAuTDZphMHqBJLc3FhssKjG7w/CeCpFReSfwBWDTKpkzPP8c+cLsK+V',
            'scriptCrossorigin' => 'anonymous',
        ],
        'csrf' => [
            'route' => 'kirby-form-snippets/csrf-token',
            'field' => 'csrf_token',
            'formSelector' => 'form',
        ],
        'submit' => [
            'route' => 'kirby-form-snippets/submit',
        ],
        'defaultEmailTo' => null,
        'defaultEmailTemplate' => 'submition',
        'defaultEmailTheme' => [
            'width' => 600,
            'fontFamily' => "'Helvetica Neue', Helvetica, Arial, sans-serif",
            'colors' => [
                'pageBg' => '#F5F5F5',
                'cardBg' => '#FFFFFF',
                'border' => '#E5E5E5',
                'label' => '#737373',
                'text' => '#171717',
                'accent' => '#2563EB',
            ],
            'logo' => null,
            'logoAlt' => null,
            'logoLink' => null,
            'intro' => null,
            'footer' => null,
            'preview' => null,
            'hideEmptyFields' => false,
            'emptyPlaceholder' => '—',
        ],
    ],
    'routes' => function () {
        return [
            [
                'pattern' => option('baptiste.kirby-form-snippets.csrf.route'),
                'method' => 'GET',
                'action' => function () {
                    return Response::json(['token' => csrf()]);
                },
            ],
            [
                'pattern' => option('baptiste.kirby-form-snippets.honeytime.route'),
                'method' => 'GET',
                'action' => function () {
                    $value = repliq\RepliqForm::generateHoneytimeValue();

                    if ($value === null) {
                        return Response::json(['error' => 'Honeytime not configured'], 503);
                    }

                    return Response::json(['value' => $value]);
                },
            ],
            [
                'pattern' => option('baptiste.kirby-form-snippets.submit.route') . '/(:any)',
                'method' => 'POST',
                'action' => function (string $key) {
                    return repliq\RepliqForm::handleSubmit($key);
                },
            ],
        ];
    },
    'blueprints' => [
        'blocks/contact-form' => __DIR__ . '/blueprints/blocks/contact-form.yml',
    ],
    'templates' => [
        'emails/submition.html' => __DIR__ . '/templates/emails/submition.html.php',
    ],
    'snippets' => [
        'form-page' => __DIR__ . '/snippets/form-page.php',
        'form-filter' => __DIR__ . '/snippets/form-filter.php',
        'form-fields' => __DIR__ . '/snippets/form-fields.php',
        'form-styleguide' => __DIR__ . '/snippets/form-styleguide.php',
        'form-errors-summary' => __DIR__ . '/snippets/form-errors-summary.php',
        'form-submit-error' => __DIR__ . '/snippets/form-submit-error.php',
        'form-input' => __DIR__ . '/snippets/fields/input.php',
        'form-textarea' => __DIR__ . '/snippets/fields/textarea.php',
        'form-checkbox' => __DIR__ . '/snippets/fields/checkbox.php',
        'form-radio' => __DIR__ . '/snippets/fields/radio.php',
        'form-checkbox-group' => __DIR__ . '/snippets/fields/checkbox-group.php',
        'form-radio-group' => __DIR__ . '/snippets/fields/radio-group.php',
        'form-honeypot' => __DIR__ . '/snippets/fields/honeypot.php',
        'form-honeytime' => __DIR__ . '/snippets/fields/honeytime.php',
        'form-csrf' => __DIR__ . '/snippets/fields/csrf.php',
        'form-csrf-refresh' => __DIR__ . '/snippets/form-csrf-refresh.php',
        'form-honeytime-refresh' => __DIR__ . '/snippets/form-honeytime-refresh.php',
        'form-htmx-script' => __DIR__ . '/snippets/form-htmx-script.php',
        'form-field-errors' => __DIR__ . '/snippets/fields/field-errors.php',
        'form-line' => __DIR__ . '/snippets/fields/line.php',
        'form-info' => __DIR__ . '/snippets/fields/info.php',
        'form-label' => __DIR__ . '/snippets/fields/label.php',
        'form-card' => __DIR__ . '/snippets/fields/card.php',
        'form-section-title' => __DIR__ . '/snippets/fields/section-title.php',
        'form-select' => __DIR__ . '/snippets/fields/select.php',
        'blocks/contact-form' => __DIR__ . '/snippets/blocks/contact-form.php',
    ],
    'translations' => [
        'fr' => [
            'uniform-email-error' => 'L\'envoi du message a échoué',
            'uniform-filled-potty' => 'Oups, l\'envoi n\'a pas abouti. Si le problème persiste, contactez-nous par e-mail.',
            'uniform-honeytime-reject' => 'Oups, l\'envoi n\'a pas abouti. Si le problème persiste, contactez-nous par e-mail.',
            'uniform-honeytime-invalid' => 'Oups, l\'envoi n\'a pas abouti. Si le problème persiste, contactez-nous par e-mail.',
        ],
    ],
]);
