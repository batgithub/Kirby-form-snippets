<?php 
    $isRequired = isset($required) ? $required : ''
?>

<div class="field <?= isset($alert[$id]) ? 'error' : '' ?>">
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
        <?= isset($placeholder) ? 'placeholder='.$placeholder : '' ?>
        <?= isset($pattern) ? 'pattern='.$pattern : '' ?>
        <?= isset($minlength) ? 'minlength='.$minlength : '' ?>
        <?= isset($maxlength) ? 'maxlength='.$maxlength : '' ?>
        <?= ($datas->old($id))  ? 'value='.$datas->old($id) : '' ?>
        <?= ($isRequired) ? 'required' : '' ?>
    >

    <?php if($datas->error($id))  {
        snippet('form-notif', [
            'notif_text' => implode('<br>', $datas->error('message')),
            'class' => 'error',
        ]);
    }?>
</div>