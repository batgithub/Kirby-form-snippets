<?php 


?>

<div class="field <?= isset($alert[$id]) ? 'error' : '' ?>">

    <?php  snippet('form-label', [
        'label_text' => $label,
        'id' => $id,
        'required' => isset($required) ? $required : ''
    ]); ?>


    <?php 
        if(isset($info)): 
            snippet('info', ['text' => $info]);
        endif
    ?>

    <textarea 
        type="text" 
        id="<?= $id ?>" 
        name="<?= $id ?>" 
        <?= isset($rows) ? 'rows='.$rows : '' ?>
        <?= isset($minlength) ? 'minlength='.$minlength : '' ?>
        <?= isset($maxlength) ? 'maxlength='.$maxlength : '' ?>
        <?= isset($required) ? 'required' : '' ?>
    ><?= ($form->old($id))  ? $form->old($id):'' ?></textarea>
    
    <?php if($form->error($id))  {
        snippet('form-notif', [
            'notif_text' => implode('<br>', $form->error('message')),
            'class' => 'error',
        ]);
    }?>
    
</div>