<?php if (!empty($error)): ?>
    <span id="<?= esc($id . '-error', 'attr') ?>" class="notif error" role="alert">
        <?= html(implode(' ', (array) $error)) ?>
    </span>
<?php endif ?>
