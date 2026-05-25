<?php
    $isRequired = isset($required) ? $required : '';
    $error = $form->error($id);
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

    <textarea
        id="<?= attr($id) ?>"
        name="<?= attr($id) ?>"
        <?= isset($rows) ? 'rows="' . attr($rows) . '"' : '' ?>
        <?= isset($minlength) ? 'minlength="' . attr($minlength) . '"' : '' ?>
        <?= isset($maxlength) ? 'maxlength="' . attr($maxlength) . '"' : '' ?>
        placeholder="<?= attr($placeholderText) ?>"
        aria-invalid="<?= empty($error) ? 'false' : 'true' ?>"
        <?= empty($error) ? '' : 'aria-describedby="' . attr($id . '-error') . '"' ?>
        <?= $isRequired ? 'required' : '' ?>
    ><?= html($form->old($id)) ?></textarea>

    <?php snippet('form-field-errors', ['id' => $id, 'error' => $error]); ?>

</div>
