<?php
    use repliq\RepliqForm;

    $isRequired = isset($required) ? $required : '';
    $name = $id;
    $error = $form->error($id);
    $oldValues = (array) $form->old($id);
?>

<div class="field-group <?= empty($error) ? '' : 'error' ?>">
    <fieldset
        aria-invalid="<?= empty($error) ? 'false' : 'true' ?>"
        <?= empty($error) ? '' : 'aria-describedby="' . attr($id . '-error') . '"' ?>
    >
        <legend><?= html($label) ?></legend>
        <?php foreach ($options as $option): ?>
            <?php $optionValue = RepliqForm::optionValue($option); ?>
            <?php snippet('form-checkbox', [
                'id' => $id . '-' . $optionValue,
                'name' => $name . '[]',
                'label' => $option['label'],
                'value' => $optionValue,
                'form' => $form,
                'checked' => isset($option['checked']) || in_array($optionValue, $oldValues, true),
                'required' => false,
                'inGroup' => true,
            ]) ?>
        <?php endforeach ?>

    </fieldset>
    <?php snippet('form-field-errors', ['id' => $id, 'error' => $error]); ?>
</div>
