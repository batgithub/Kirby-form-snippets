<?php if (!empty($error)): ?>
    <span id="<?= attr($id . '-error') ?>" class="notif error" role="alert">
        <?= html(implode(' ', (array) $error)) ?>
    </span>
<?php endif ?>
