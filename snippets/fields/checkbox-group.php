<?php 
    $isRequired = isset($required) ? $required : '';
    $name = $id;
    $error = $form->error($id);
    $value = $form->old($id);
    $options = $options;
?>

<div class="field-group <?= empty($error) ? '' : 'error' ?>">
    <fieldset>
        <legend><?= $legend ?></legend>
        <?php foreach(  $options as $index => $option): ?>
            <?php snippet('form-checkbox', [
                'id'          => $id.'-'.Str::kebab($option["value"]),
                'name'        => $name.'[]',
                'label'       => $option["label"],
                'value'       => $option["value"],
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