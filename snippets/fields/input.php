<?php 
    $isRequired = isset($required) ? $required : '';
    $error = $form->error($id);
?>

<div class="field <?= empty($error) ? '' : 'error' ?>">
    <div class="field-wrapper">
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
            type="<?= isset($type) ? $type:'text' ?>" 
            id="<?= $id ?>" 
            name="<?= $id ?>" 
            <?= isset($placeholder) ? 'placeholder="'.$placeholder.'"' : 'placeholder= "Votre réponse"' ?>
            <?= isset($pattern) ? 'pattern='.$pattern : '' ?>
            <?= ($form->old($id))  ? 'value='.$form->old($id) : '' ?>
           
        >
    </div>

    <?php if(empty($error) == false)  {
        snippet('form-notif', [
            'notif_text' => implode('<br>', $error),
            'class' => 'error',
        ]);
    }?>
</div>