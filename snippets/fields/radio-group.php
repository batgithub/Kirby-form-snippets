<?php
    use repliq\RepliqForm;

    $isRequired = isset($required) ? $required : '';
    $name = $id;
    $error = $form->error($id);
    $oldValue = $form->old($id);
?>

<div class="field-group <?= empty($error) ? '' : 'error' ?>">
    <fieldset
        aria-invalid="<?= empty($error) ? 'false' : 'true' ?>"
        <?= empty($error) ? '' : 'aria-describedby="' . esc($id . '-error', 'attr') . '"' ?>
    >
        <legend>
            <?= html($label) ?>
            <?= ($required === true) ? '<abbr title="requis">*</abbr>' : '' ?>
        </legend>
        <?php foreach ($options as $option): ?>
            <?php $optionValue = RepliqForm::optionValue($option); ?>
            <?php snippet('form-radio', [
                'id' => $id . '-' . $optionValue,
                'name' => $name,
                'label' => $option['label'],
                'value' => $optionValue,
                'form' => $form,
                'checked' => isset($option['checked']) || ($oldValue !== null && (string) $oldValue === $optionValue),
                'required' => false,
                'inGroup' => true,
            ]) ?>
        <?php endforeach ?>

    </fieldset>
    <?php snippet('form-field-errors', ['id' => $id, 'error' => $error]); ?>
</div>
