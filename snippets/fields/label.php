<label for="<?= esc($id, 'attr') ?>">
    <?= html($label_text) ?>
    <?= ($required === true) ? '<abbr title="requis">*</abbr>' : '' ?>
</label>
