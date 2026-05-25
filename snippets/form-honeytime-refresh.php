<?php

$valueUrl = url(option('baptiste.kirby-form-snippets.honeytime.route'));
$formSelector = $formSelector ?? option('baptiste.kirby-form-snippets.csrf.formSelector', 'form');
$fieldName = $field ?? option('baptiste.kirby-form-snippets.honeytime.field', 'uniform-honeytime');
$inputSelector = $formSelector . ' input[name="' . $fieldName . '"]';

?>
<script>
(function () {
    fetch(<?= json_encode($valueUrl, JSON_UNESCAPED_SLASHES) ?>)
        .then(function (response) {
            if (!response.ok) {
                return null;
            }

            return response.json();
        })
        .then(function (data) {
            if (!data || !data.value) {
                return;
            }

            document.querySelectorAll(<?= json_encode($inputSelector) ?>).forEach(function (field) {
                field.value = data.value;
            });
        });
})();
</script>
