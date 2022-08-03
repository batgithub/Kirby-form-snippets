<?php 
    $isRequired = isset($required) ? $required : '';
    $id = isset($id) ? $id : $name;
    $error = $form->error($id);
    $checked = isset($checked) ? $checked : false;
?>

<div class="field radio <?= empty($error) ? '' : 'error' ?>">
    <div class="wrap-input">
        <input 
        class="cursor-pointer"
        type="radio" 
        id="<?= $id ?>" 
        name="<?= $name ?>" 
        value="<?= $value ?>"
        <?= ($value==$form->old($id)||  $checked) ? 'checked': '' ?>        
        >
        <label for="<?= $id ?>"> 
            <p class="cursor-pointer"><?= $label ?></p>
        </label>
    </div>
    
</div>

