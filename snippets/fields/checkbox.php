<?php 
    $isRequired = isset($required) ? $required : '';
    $id = Str::kebab($value);
    $error = $form->error($id);
    $value = $form->old($id);
?>

<div class="field <?= empty($error) ? '' : 'error' ?>">
    <input 
            class="cursor-pointer"
            type="checkbox" 
            id="<?= $id ?>" 
            name="<?= $name ?>" 
            value="<?= $value ?>"
            <?= $value==$value ? 'checked': '' ?>
            <?php if($required): ?> required <?php endif ?>            
    >
    <label for="<?= $id ?>"> 
        <p class="cursor-pointer"><?= $label ?></p>
    </label>

    
    <?php if(empty($error) == false)  {
        snippet('form-notif', [
            'notif_text' => implode('<br>', $error),
            'class' => 'error',
        ]);
    }?>
    
</div>