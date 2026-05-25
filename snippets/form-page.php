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
$submitLabel = $submitLabel ?? 'Envoyer';
$successMessage = $successMessage ?? 'Merci, votre message a bien été envoyé.';
$errorsSummarySetting = RepliqForm::resolveErrorsSummarySetting(
    (string) $formKey,
    isset($errorsSummary) ? $errorsSummary : null
);
?>

<?php if ($form->success()): ?>
    <p class="form-success"><?= html($successMessage) ?></p>
<?php else: ?>
    <form class="<?= esc($formClass, 'attr') ?>" action="<?= esc($formAction, 'attr') ?>" method="post">
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
        ]) ?>

        <button type="submit"><?= html($submitLabel) ?></button>
    </form>
<?php endif ?>
