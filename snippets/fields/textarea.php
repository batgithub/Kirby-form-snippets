<?php
    $isRequired = isset($required) ? $required : '';
    $error = $form->error($id);
    $placeholderText = isset($placeholder)
        ? $placeholder
        : option('baptiste.kirby-form-snippets.placeholder');
    $maxlengthAttr = $maxlength ?? option('baptiste.kirby-form-snippets.maxLength.textarea');
?>

<div class="field <?= empty($error) ? '' : 'error' ?>">

    <?php snippet('form-label', [
        'label_text' => $label,
        'id' => $id,
        'required' => $isRequired,
    ]); ?>

    <?php if (isset($info)): ?>
        <?php snippet('form-info', ['text' => $info]); ?>
    <?php endif ?>

    <textarea
        id="<?= esc($id, 'attr') ?>"
        name="<?= esc($id, 'attr') ?>"
        <?= isset($rows) ? 'rows="' . esc($rows, 'attr') . '"' : '' ?>
        <?= isset($minlength) ? 'minlength="' . esc($minlength, 'attr') . '"' : '' ?>
        maxlength="<?= esc($maxlengthAttr, 'attr') ?>"
        placeholder="<?= esc($placeholderText, 'attr') ?>"
        aria-invalid="<?= empty($error) ? 'false' : 'true' ?>"
        <?= empty($error) ? '' : 'aria-describedby="' . esc($id . '-error', 'attr') . '"' ?>
        <?= $isRequired ? 'required' : '' ?>
    ><?= html($form->old($id)) ?></textarea>

    <?php snippet('form-field-errors', ['id' => $id, 'error' => $error]); ?>

</div>
