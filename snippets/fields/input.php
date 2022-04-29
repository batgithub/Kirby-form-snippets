<?php 
    $isRequired = isset($required) ? $required : '';
    $error = $form->error($id);
?>

<div class="field <?= empty($error) ? '' : 'error' ?>">
    <?php  snippet('form-label', [
            'label_text' => $label,
            'id' => $id,
            'required' => $isRequired
    ]); ?>

    <?php 
        if(isset($info)): 
            snippet('form-info', ['text' => $info]);
        endif
    ?>

    <input 
        type="<?= $type ?>" 
        id="<?= $id ?>" 
        name="<?= $id ?>" 
        <?= isset($placeholder) ? 'placeholder='.$placeholder : 'placeholder= "Votre réponse"' ?>
        <?= isset($pattern) ? 'pattern='.$pattern : '' ?>
        <?= isset($minlength) ? 'minlength='.$minlength : '' ?>
        <?= isset($maxlength) ? 'maxlength='.$maxlength : '' ?>
        <?= ($form->old($id))  ? 'value='.$form->old($id) : '' ?>
        <?= ($isRequired) ? 'required' : '' ?>
    >

    <?php if(empty($error) == false)  {
        snippet('form-notif', [
            'notif_text' => implode('<br>', $error),
            'class' => 'error',
        ]);
    }?>
</div>