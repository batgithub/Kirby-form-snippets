<?php 
    $isRequired = isset($required) ? $required : '';
    $id = $name;
    $error = $form->error($id);
    $value = $form->old($id);
    $options = $options;
?>

<fieldset class="field-group <?= empty($error) ? '' : 'error' ?>">
    <legend><?= $legend ?></legend>
    <?php foreach(  $options as $index => $option): ?>
        <?php snippet('form-checkbox', [
            'name'        => $name.'[]',
            'label'       => $option["label"],
            'value'       => $option["value"],
            'required'    => false
        ]) ?>
    <?php endforeach ?>
    
</fieldset>