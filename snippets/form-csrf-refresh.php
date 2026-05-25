<?php

$tokenUrl = url(option('baptiste.kirby-form-snippets.csrf.route'));
$formSelector = $formSelector ?? option('baptiste.kirby-form-snippets.csrf.formSelector', 'form');
$fieldName = option('baptiste.kirby-form-snippets.csrf.field', 'csrf_token');
$inputSelector = $formSelector . ' input[name="' . $fieldName . '"]';

?>
<script>
(function () {
    fetch(<?= json_encode($tokenUrl, JSON_UNESCAPED_SLASHES) ?>)
        .then(function (response) {
            if (!response.ok) {
                return null;
            }

            return response.json();
        })
        .then(function (data) {
            if (!data || !data.token) {
                return;
            }

            document.querySelectorAll(<?= json_encode($inputSelector) ?>).forEach(function (field) {
                field.value = data.token;
            });
        });
})();
</script>
