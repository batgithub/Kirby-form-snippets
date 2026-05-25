<?php
    $class = isset($class) ? $class : '';
?>

<div class="form-card <?= esc($class, 'attr') ?>">
    <p>
        <?= html($text) ?>
    </p>
</div>
