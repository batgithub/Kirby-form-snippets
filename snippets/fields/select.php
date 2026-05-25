<?php
    use repliq\RepliqForm;

    $isRequired = isset($required) ? $required : '';
    $multiselect = isset($multiselect) ? true : false;
    $name = $id;
    $error = $form->error($id);
    $oldValue = $form->old($id);
    $oldValues = (array) $oldValue;
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

    <select
        <?= $multiselect ? 'multiple' : '' ?>
        name="<?= esc($id . ($multiselect ? '[]' : ''), 'attr') ?>"
        id="<?= esc($id, 'attr') ?>"
        aria-invalid="<?= empty($error) ? 'false' : 'true' ?>"
        <?= empty($error) ? '' : 'aria-describedby="' . esc($id . '-error', 'attr') . '"' ?>
        <?= $isRequired ? 'required' : '' ?>
    >

        <?php foreach ($options as $option): ?>
            <?php $optionValue = RepliqForm::optionValue($option); ?>
            <option
                value="<?= esc($optionValue, 'attr') ?>"
                <?php if ($multiselect): ?>
                    <?= in_array($optionValue, $oldValues, true) || isset($option['selected']) ? 'selected' : '' ?>
                <?php else: ?>
                    <?= ($optionValue === $oldValue || isset($option['selected'])) ? 'selected' : '' ?>
                <?php endif; ?>
            ><?= html($option['label']) ?></option>
        <?php endforeach ?>

    </select>

    <?php snippet('form-field-errors', ['id' => $id, 'error' => $error]); ?>
</div>
