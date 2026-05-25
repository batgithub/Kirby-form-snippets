<?php

if (empty($formKey)) {
    return;
}

$formData = $formData ?? repliq_form($formKey, $overrides ?? []);

if ($formData === null || ($formData['mode'] ?? 'submit') !== 'filter') {
    return;
}

extract($formData);

$formClass = $formClass ?? ('repliq-form-' . $formKey);
$formSelector = $formSelector ?? ('.' . $formClass);
$submitLabel = $submitLabel ?? 'Filtrer';

if (isset($formActionOverride) && is_string($formActionOverride) && $formActionOverride !== '') {
    $formAction = $formActionOverride;
}
?>

<form class="<?= esc($formClass, 'attr') ?>" action="<?= esc($formAction, 'attr') ?>" method="get">
    <?php snippet('form-fields', [
        'formConfig' => $formConfig,
        'form' => $form,
        'formSelector' => $formSelector,
        'mode' => 'filter',
    ]) ?>

    <button type="submit"><?= html($submitLabel) ?></button>
</form>
