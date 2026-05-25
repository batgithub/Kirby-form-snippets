<?php
    $allowedTags = ['h2', 'h3', 'h4', 'h5', 'h6'];
    $headingTag = isset($tag) && in_array($tag, $allowedTags, true) ? $tag : 'h2';
?>

<a class="form-section-title <?= esc($headingTag, 'attr') ?> field" id="<?= esc($id, 'attr') ?>" href="#<?= esc($id, 'attr') ?>">
    <<?= $headingTag ?>>
        <?= html($title) ?>
    </<?= $headingTag ?>>
</a>
