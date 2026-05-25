<?php

$formKey = $block->formKey()->isNotEmpty() ? $block->formKey()->value() : 'contact';
$formClass = 'repliq-form block-form-' . $block->id();

snippet('form-page', [
    'formKey' => $formKey,
    'formClass' => $formClass,
    'submitLabel' => $block->submitLabel()->or('Envoyer')->value(),
    'successMessage' => $block->successMessage()->or('Merci, votre message a bien été envoyé.')->value(),
]);
