<?php 
    $isRequired = isset($required) ? $required : '';
    $id = $name;
    $error = $form->error($id);
    $value = $form->old($id);
    $options = $options;
?>

<div class="field-group <?= empty($error) ? '' : 'error' ?>">
    <fieldset>
        <legend><?= $legend ?></legend>
        <?php foreach(  $options as $index => $option): ?>
            <?php snippet('form-radio', [
                'name'        => $name,
                'label'       => $option["label"],
                'value'       => $option["value"],
                'required'    => false
            ]) ?>
        <?php endforeach ?>
        
    </fieldset>
    <?php if(empty($error) == false)  {
            snippet('form-notif', [
                'notif_text' => implode('<br>', $error),
                'class' => 'error',
            ]);}
    ?>
</div>
