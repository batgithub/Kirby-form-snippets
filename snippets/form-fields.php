<?php

use repliq\RepliqForm;

if (!isset($form, $formConfig) || !$formConfig instanceof RepliqForm) {
    return;
}

$config = $formConfig;

if (($mode ?? 'submit') !== 'filter') {
    snippet('form-csrf');
    snippet('form-csrf-refresh', [
        'formSelector' => $formSelector ?? null,
    ]);

    $honeytimeOptions = is_array($honeytime ?? null) ? $honeytime : null;

    if ($honeytimeOptions === null && !empty($formKey)) {
        $resolvedConfig = RepliqForm::buildConfig(
            (string) $formKey,
            is_array($overrides ?? null) ? $overrides : [],
            'render'
        );

        if (is_array($resolvedConfig)) {
            $honeytimeOptions = RepliqForm::resolveHoneytimeGuardOptions($resolvedConfig);
        }
    }

    if (is_array($honeytimeOptions)) {
        snippet('form-honeytime', $honeytimeOptions);
        snippet('form-honeytime-refresh', [
            'formSelector' => $formSelector ?? null,
            'field' => $honeytimeOptions['field'] ?? null,
        ]);
    }
}

foreach ($config->getInputs($form) as $field): ?>
    <?= $field ?>
<?php endforeach;
