<div class="field <?= isset($alert[$id]) ? 'error' : '' ?>">

    <label for="<?= $id ?>">
        <?= $label ?> <?php if($required): ?> <abbr title="requis">*</abbr> <?php endif ?>
    </label>
    <div class="label-desc">
        <?= $info ?> 
    </div>
    <textarea 
        class="w-full"
        type="text" 
        id="<?= $id ?>" 
        name="<?= $id ?>" 
        rows="<?= $rows ?>"
        <?php if($minlength !== ''): ?>minlength="<?= $minlength ?>"<?php endif ?>
        <?php if($maxlength !== ''): ?>maxlength="<?= $maxlength ?>"<?php endif ?>
        <?php if($required): ?>required <?php endif ?>
    >
        <?= $data[$id] ?? '' ?>
    </textarea>
    <?php if( isset($alert[$id]) )  {
        snippet('components/input-notif', [
            'notif_text' => $alert[$id],
            'class' => 'error',
        ]);
    }?>
    
</div>