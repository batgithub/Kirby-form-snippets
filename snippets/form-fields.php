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
}

foreach ($config->getInputs($form) as $field): ?>
    <?= $field ?>
<?php endforeach;
