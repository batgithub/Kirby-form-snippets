<?php
    $isRequired = isset($required) ? $required : '';
    $inGroup = isset($inGroup) ? $inGroup : false;
    $id = isset($id) ? $id : $name;
    $error = $inGroup ? [] : $form->error($id);
    $checked = isset($checked) ? $checked : false;
    $isChecked = $checked || ($form->old($name) !== null && (string) $form->old($name) === (string) $value);
?>

<div class="field radio <?= empty($error) ? '' : 'error' ?>">
    <div class="wrap-input">
        <input
            class="cursor-pointer"
            type="radio"
            id="<?= esc($id, 'attr') ?>"
            name="<?= esc($name, 'attr') ?>"
            value="<?= esc($value, 'attr') ?>"
            <?= $inGroup ? '' : 'aria-invalid="' . (empty($error) ? 'false' : 'true') . '"' ?>
            <?= (!$inGroup && !empty($error)) ? 'aria-describedby="' . esc($id . '-error', 'attr') . '"' : '' ?>
            <?= $isChecked ? 'checked' : '' ?>
            <?= $isRequired ? 'required' : '' ?>
        >
        <label for="<?= esc($id, 'attr') ?>">
            <p class="cursor-pointer"><?= html($label) ?></p>
        </label>
    </div>

    <?php if (!$inGroup): ?>
        <?php snippet('form-field-errors', ['id' => $id, 'error' => $error]); ?>
    <?php endif ?>

</div>
