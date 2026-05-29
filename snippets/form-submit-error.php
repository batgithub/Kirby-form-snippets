<?php

$messages = $messages ?? [];

if ($messages === []) {
    return;
}

?>

<div class="form-submit-error" role="alert">
    <?php foreach ($messages as $message): ?>
        <p><?= html($message) ?></p>
    <?php endforeach ?>
</div>
