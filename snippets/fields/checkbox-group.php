<?php 
    $isRequired = isset($required) ? $required : '';
    $name = $id;
    $error = $form->error($id);
    $value = $form->old($id);
    $options = $options;
?>

<div class="field-group <?= empty($error) ? '' : 'error' ?>">
    <fieldset>
        <legend><?= $label ?></legend>
        <?php foreach(  $options as  $option): ?>
            <?php snippet('form-checkbox', [
                'id'          => $id.'-'.Str::slug($option['label']),
                'name'        => $name.'[]',
                'label'       => $option['label'],
                'value'       => Str::slug($option['label']),
                'checked'     => isset($option["checked"]),
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