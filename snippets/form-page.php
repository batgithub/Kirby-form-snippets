<?php

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
?>

<?php if ($form->success()): ?>
    <p class="form-success"><?= html($successMessage) ?></p>
<?php else: ?>
    <form class="<?= esc($formClass, 'attr') ?>" action="<?= esc($formAction, 'attr') ?>" method="post">
        <?php snippet('form-fields', [
            'formConfig' => $formConfig,
            'form' => $form,
            'formSelector' => $formSelector,
            'mode' => $mode ?? 'submit',
        ]) ?>

        <button type="submit"><?= html($submitLabel) ?></button>
    </form>
<?php endif ?>
