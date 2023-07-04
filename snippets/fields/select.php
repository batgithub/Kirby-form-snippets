<?php 
    $isRequired = isset($required) ? $required : '';
    $multiselect = isset($multiselect) ? true : false;
    $name = $id;
    $error = $form->error($id);
    $value = $form->old($id);
    $options = $options;
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


    <select 
        <?= $multiselect ? 'multiple' : '' ?>
        name="<?= $id ?><?= $multiselect ? '[]' : '' ?>" 
        id="<?= $id ?>"
        <?= ($isRequired) ? 'required' : '' ?>
    >

        <?php foreach(  $options as  $option): ?>
            <option 
                <?php if(isset($option['value'])):  ?>
                    value="<?= $option['value'] ?>"
                    <?= ($option['value']==$form->old($id) ||  isset($option['selected'])) ? 'selected': '' ?>       
                <?php else: ?>
                    value="<?= urlencode($option['label']) ?>"
                    <?= (urlencode($option['label']) == $form->old($id) ||  isset($option['selected'])) ? 'selected': '' ?>       
                <?php endif; ?>

                <?= isset($option['disabled']) ? 'disabled': '' ?>
                
            ><?= $option['label'] ?>
            </option>
        <?php endforeach ?>
    
    </select>

    <?php if(empty($error) == false)  {
        snippet('form-notif', [
            'notif_text' => implode('<br>', $error),
            'class' => 'error',
        ]);
    }?>
</div>