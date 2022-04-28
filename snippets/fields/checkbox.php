<?php 
    $isRequired = isset($required) ? $required : '';
    $error = $form->error($id);
    $value = $form->old($id);
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
            class="cursor-pointer"
            type="checkbox" 
            id="<?= $id ?>" 
            name="<?= $name ?>" 
            value="<?= $value ?>"
            <?php if($required): ?>required <?php endif ?>            
        >
        <label for="<?= $id ?>"> 
            <p class="cursor-pointer ml-2"><?= $label ?></p>
        </label>


    <label>
        <?php $value = $form->old('myfield') ?>
        <input type="checkbox" name="myfield" value="true"<?php e(!$value || $value=='true', ' checked')?>/> Confirm
    </label>
   
    
    <?php if(empty($error) == false)  {
        snippet('form-notif', [
            'notif_text' => implode('<br>', $error),
            'class' => 'error',
        ]);
    }?>
    
</div>