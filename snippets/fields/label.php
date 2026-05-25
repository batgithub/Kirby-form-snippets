<label for="<?= attr($id) ?>">
    <?= html($label_text) ?>
    <?= ($required === true) ? '<abbr title="requis">*</abbr>' : '' ?>
</label>
