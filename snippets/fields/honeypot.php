<div class="honeypot" aria-hidden="true">
    <label for="<?= attr($name) ?>" aria-hidden="true" style="display: none;">
        <?= html($name) ?> <abbr title="requis">*</abbr>
    </label>
    <input
        type="text"
        id="<?= attr($name) ?>"
        name="<?= attr($name) ?>"
        autocomplete="off"
        tabindex="-1"
    >
</div>
