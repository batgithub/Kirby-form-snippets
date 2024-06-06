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

        <textarea 
            type="text" 
            id="<?= $id ?>" 
            name="<?= $id ?>" 
            <?= isset($rows) ? 'rows='.$rows : '' ?>
            <?= isset($placeholder) ? 'placeholder='.$placeholder : 'placeholder= "Votre réponse"' ?>
           
        ><?= ($form->old($id))  ? $form->old($id):'' ?></textarea>
    </div>
        <?php if(empty($error) == false)  {
            snippet('form-notif', [
                'notif_text' => implode('<br>', $error),
                'class' => 'error',
            ]);
        }?>
    
</div>