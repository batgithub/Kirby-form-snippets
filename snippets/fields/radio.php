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
            id="<?= attr($id) ?>"
            name="<?= attr($name) ?>"
            value="<?= attr($value) ?>"
            <?= $inGroup ? '' : 'aria-invalid="' . (empty($error) ? 'false' : 'true') . '"' ?>
            <?= (!$inGroup && !empty($error)) ? 'aria-describedby="' . attr($id . '-error') . '"' : '' ?>
            <?= $isChecked ? 'checked' : '' ?>
            <?= $isRequired ? 'required' : '' ?>
        >
        <label for="<?= attr($id) ?>">
            <p class="cursor-pointer"><?= html($label) ?></p>
        </label>
    </div>

    <?php if (!$inGroup): ?>
        <?php snippet('form-field-errors', ['id' => $id, 'error' => $error]); ?>
    <?php endif ?>

</div>
