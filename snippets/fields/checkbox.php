<?php
    use repliq\RepliqForm;

    $isRequired = isset($required) ? $required : '';
    $inGroup = isset($inGroup) ? $inGroup : false;
    $name = isset($name) ? $name : $id;
    $error = $inGroup ? [] : $form->error($id);
    $checked = isset($checked) ? $checked : false;
    $fieldValue = isset($value) ? (string) $value : RepliqForm::optionValue(['label' => $label]);
    $oldValue = $form->old($id);
    $isChecked = $checked || ($oldValue !== null && (string) $oldValue === $fieldValue);
?>

<div class="field checkbox <?= empty($error) ? '' : 'error' ?>">
    <div class="wrap-input">
        <input
            class="cursor-pointer"
            type="checkbox"
            id="<?= esc($id, 'attr') ?>"
            name="<?= esc($name, 'attr') ?>"
            value="<?= esc($fieldValue, 'attr') ?>"
            <?= $inGroup ? '' : 'aria-invalid="' . (empty($error) ? 'false' : 'true') . '"' ?>
            <?= (!$inGroup && !empty($error)) ? 'aria-describedby="' . esc($id . '-error', 'attr') . '"' : '' ?>
            <?= $isChecked ? 'checked' : '' ?>
            <?= $isRequired ? 'required' : '' ?>
        >
        <label for="<?= esc($id, 'attr') ?>">
            <p class="cursor-pointer"><?= html($label) ?></p>
        </label>

        <?php if (!$inGroup): ?>
            <?php snippet('form-field-errors', ['id' => $id, 'error' => $error]); ?>
        <?php endif ?>

    </div>

</div>
