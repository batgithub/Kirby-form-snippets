<?php

use repliq\RepliqForm;

if (!isset($form, $formKey)) {
    return;
}

$items = $items ?? RepliqForm::buildErrorsSummary($form, (string) $formKey, $overrides ?? []);

if ($items === []) {
    return;
}

$title = $title ?? RepliqForm::resolveErrorsSummarySetting((string) $formKey)['title'];
?>

<div class="form-errors-summary" role="alert" aria-labelledby="form-errors-title">
    <h2 id="form-errors-title"><?= html($title) ?></h2>
    <ul>
        <?php foreach ($items as $item): ?>
            <li>
                <a href="#<?= esc($item['id'], 'attr') ?>"><?= html($item['label']) ?></a> :
                <?= html(implode(' ', $item['messages'])) ?>
            </li>
        <?php endforeach ?>
    </ul>
</div>
