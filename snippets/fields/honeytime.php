<?php

$field = $field ?? 'uniform-honeytime';

if (!is_string($field) || $field === '') {
    $field = 'uniform-honeytime';
}

?>
<input type="hidden" name="<?= esc($field, 'attr') ?>" value="">
