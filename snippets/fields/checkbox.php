<?php 
    $isRequired = isset($required) ? $required : '';
    $name = isset($name) ? $name : $id;
    $error = $form->error($id);
    $checked = isset($checked) ? $checked : false;
?>

<div class="field checkbox <?= empty($error) ? '' : 'error' ?>">
    <div class="wrap-input">
        <input 
                class="cursor-pointer"
                type="checkbox" 
                id="<?= $id ?>" 
                name="<?= $name ?>" 
                value="<?= Str::slug($label) ?>"
                <?= ($value==$form->old($id) ||  $checked) ? 'checked': '' ?>        
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
    
</div>