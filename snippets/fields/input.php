<div class="field <?= isset($alert[$id]) ? 'error' : '' ?>">
    <label for="<?= $id ?>">
        <?= $label ?> <?php if($required): ?> <abbr title="requis">*</abbr> <?php endif ?>
    </label>
    <div class="label-desc">
        <?= $info ?> 
    </div>
    <input 
        type="<?= $type ?>" 
        id="<?= $id ?>" 
        name="<?= $id ?>" 
        <?php if($placeholder !== ''): ?>placeholder="<?= $placeholder ?>"<?php endif ?>
        <?php if($pattern !== ''): ?>pattern="<?= $pattern ?>"<?php endif ?>
        <?php if($minlength !== ''): ?>minlength="<?= $minlength ?>"<?php endif ?>
        <?php if($maxlength !== ''): ?>maxlength="<?= $maxlength ?>"<?php endif ?>
        value="<?= $data[$id] ?? '' ?>"
        <?php if($required): ?>required <?php endif ?>
    >
    <?php if(isset($alert[$id]))  {
        snippet('components/input-notif', [
            'notif_text' => $alert[$id],
            'class' => 'error',
        ]);
    }?>
</div>