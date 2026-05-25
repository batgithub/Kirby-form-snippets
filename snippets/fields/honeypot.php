<?php $name = $name ?? $id; ?>
<div class="honeypot" aria-hidden="true">
    <label for="<?= esc($name, 'attr') ?>" aria-hidden="true" style="display: none;">
        <?= html($name) ?> <abbr title="requis">*</abbr>
    </label>
    <input
        type="text"
        id="<?= esc($name, 'attr') ?>"
        name="<?= esc($name, 'attr') ?>"
        autocomplete="off"
        tabindex="-1"
    >
</div>
