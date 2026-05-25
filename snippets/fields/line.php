<?php

$class = isset($class) ? $class : '';
?>

<hr id="<?= esc($id, 'attr') ?>"<?php if ($class !== ''): ?> class="<?= esc($class, 'attr') ?>"<?php endif; ?>>
