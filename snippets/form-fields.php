<?php

use repliq\RepliqForm;

if (!isset($form)) {
    return;
}

if (isset($formConfig) && $formConfig instanceof RepliqForm) {
    $config = $formConfig;
} elseif (isset($fields) && is_array($fields)) {
    $config = new RepliqForm($fields);
} else {
    return;
}

foreach ($config->getInputs($form) as $field): ?>
    <?= $field ?>
<?php endforeach;
