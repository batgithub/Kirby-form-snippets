<?php

use repliq\RepliqForm;

if (empty($formKey)) {
    return;
}

$formData = $formData ?? repliq_form($formKey, $overrides ?? []);

if ($formData === null) {
    return;
}

extract($formData);

$formClass = $formClass ?? ('repliq-form-' . $formKey);
$formSelector = $formSelector ?? ('.' . $formClass);
$submitLabel = $submitLabel ?? RepliqForm::resolveSubmitLabel((string) $formKey);
$successMessage = $successMessage ?? RepliqForm::resolveSuccessMessage((string) $formKey);
RepliqForm::rememberFormPresentation((string) $formKey, [
    'successMessage' => $successMessage,
    'submitLabel' => $submitLabel,
]);
$errorsSummarySetting = RepliqForm::resolveErrorsSummarySetting(
    (string) $formKey,
    isset($errorsSummary) ? $errorsSummary : null
);
$htmxSetting = RepliqForm::resolveHtmxSetting(
    (string) $formKey,
    isset($htmx) ? $htmx : null
);
$containerId = RepliqForm::htmxContainerId((string) $formKey);
$submitErrors = RepliqForm::buildSubmitErrors(
    $form,
    (string) $formKey,
    $overrides ?? []
);

if ($htmxSetting['enabled'] && $htmxSetting['loadScript'] && !RepliqForm::isHtmxScriptLoaded()) {
    snippet('form-htmx-script', ['htmx' => $htmxSetting]);
}
?>

<div
    id="<?= esc($containerId, 'attr') ?>"
    class="repliq-form-container"
    <?= $htmxSetting['enabled'] ? 'data-repliq-form' : '' ?>
>
    <?php if ($form->success()): ?>
        <p class="form-success"><?= html($successMessage) ?></p>
    <?php else: ?>
        <form
            class="<?= esc($formClass, 'attr') ?>"
            action="<?= esc($formAction, 'attr') ?>"
            method="post"
            <?= attr(RepliqForm::htmxFormAttributes($htmxSetting, $formAction)) ?>
        >
            <?php if ($submitErrors !== []): ?>
                <?php snippet('form-submit-error', ['messages' => $submitErrors]) ?>
            <?php endif ?>

            <?php if ($errorsSummarySetting['enabled']): ?>
                <?php snippet('form-errors-summary', [
                    'form' => $form,
                    'formKey' => $formKey,
                    'overrides' => $overrides ?? [],
                    'title' => $errorsSummarySetting['title'],
                ]) ?>
            <?php endif ?>

            <?php snippet('form-fields', [
                'formConfig' => $formConfig,
                'form' => $form,
                'formSelector' => $formSelector,
                'mode' => $mode ?? 'submit',
                'formKey' => $formKey,
                'overrides' => $overrides ?? [],
                'honeytime' => $honeytime ?? null,
                'htmxEnabled' => $htmxSetting['enabled'],
            ]) ?>

            <button type="submit"><?= html($submitLabel) ?></button>
        </form>
    <?php endif ?>
</div>
