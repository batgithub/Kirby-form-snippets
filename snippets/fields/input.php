<?php
    $isRequired = isset($required) ? $required : '';
    $error = $form->error($id);
    $inputType = isset($type) ? $type : 'text';

    if ($inputType === 'phone') {
        $inputType = 'tel';
    }

    $placeholderText = isset($placeholder)
        ? $placeholder
        : option('baptiste.kirby-form-snippets.placeholder');
    $maxlengthAttr = $maxlength ?? option('baptiste.kirby-form-snippets.maxLength.input');
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

    <input
        type="<?= esc($inputType, 'attr') ?>"
        id="<?= esc($id, 'attr') ?>"
        name="<?= esc($id, 'attr') ?>"
        placeholder="<?= esc($placeholderText, 'attr') ?>"
        <?= isset($pattern) ? 'pattern="' . esc($pattern, 'attr') . '"' : '' ?>
        <?= isset($minlength) ? 'minlength="' . esc($minlength, 'attr') . '"' : '' ?>
        maxlength="<?= esc($maxlengthAttr, 'attr') ?>"
        value="<?= esc($form->old($id), 'attr') ?>"
        aria-invalid="<?= empty($error) ? 'false' : 'true' ?>"
        <?= empty($error) ? '' : 'aria-describedby="' . esc($id . '-error', 'attr') . '"' ?>
        <?= $isRequired ? 'required' : '' ?>
    >

    <?php snippet('form-field-errors', ['id' => $id, 'error' => $error]); ?>
</div>
