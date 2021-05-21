

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
    ><?= ($datas->old($id))  ? $datas->old($id):'' ?></textarea>
    
    <?php if($datas->error($id))  {
        snippet('form-notif', [
            'notif_text' => implode('<br>', $datas->error('message')),
            'class' => 'error',
        ]);
    }?>
    
</div>