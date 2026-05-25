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
        type="<?= attr($inputType) ?>"
        id="<?= attr($id) ?>"
        name="<?= attr($id) ?>"
        placeholder="<?= attr($placeholderText) ?>"
        <?= isset($pattern) ? 'pattern="' . attr($pattern) . '"' : '' ?>
        <?= isset($minlength) ? 'minlength="' . attr($minlength) . '"' : '' ?>
        <?= isset($maxlength) ? 'maxlength="' . attr($maxlength) . '"' : '' ?>
        value="<?= attr($form->old($id)) ?>"
        aria-invalid="<?= empty($error) ? 'false' : 'true' ?>"
        <?= empty($error) ? '' : 'aria-describedby="' . attr($id . '-error') . '"' ?>
        <?= $isRequired ? 'required' : '' ?>
    >

    <?php snippet('form-field-errors', ['id' => $id, 'error' => $error]); ?>
</div>
