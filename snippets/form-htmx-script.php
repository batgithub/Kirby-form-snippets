<?php

use repliq\RepliqForm;

if (!is_array($htmx ?? null)) {
    return;
}

if (RepliqForm::isHtmxScriptLoaded()) {
    return;
}

RepliqForm::markHtmxScriptLoaded();

$scriptAttrs = [
    'src' => $htmx['script'],
];

if (is_string($htmx['scriptIntegrity'] ?? null) && $htmx['scriptIntegrity'] !== '') {
    $scriptAttrs['integrity'] = $htmx['scriptIntegrity'];
}

if (is_string($htmx['scriptCrossorigin'] ?? null) && $htmx['scriptCrossorigin'] !== '') {
    $scriptAttrs['crossorigin'] = $htmx['scriptCrossorigin'];
}

$csrfField = (string) option('baptiste.kirby-form-snippets.csrf.field', 'csrf_token');
$honeytimeField = (string) option('baptiste.kirby-form-snippets.honeytime.field', 'uniform-honeytime');
$tokenUrl = url(option('baptiste.kirby-form-snippets.csrf.route'));
$honeytimeUrl = url(option('baptiste.kirby-form-snippets.honeytime.route'));

?>
<script <?= attr($scriptAttrs) ?>></script>
<script>
(function () {
    var config = {
        csrfField: <?= json_encode($csrfField) ?>,
        honeytimeField: <?= json_encode($honeytimeField) ?>,
        tokenUrl: <?= json_encode($tokenUrl, JSON_UNESCAPED_SLASHES) ?>,
        honeytimeUrl: <?= json_encode($honeytimeUrl, JSON_UNESCAPED_SLASHES) ?>
    };

    function refreshContainer(container) {
        if (!container || !container.hasAttribute('data-repliq-form')) {
            return;
        }

        fetch(config.tokenUrl)
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

                container.querySelectorAll('input[name="' + config.csrfField + '"]').forEach(function (field) {
                    field.value = data.token;
                });
            });

        if (!container.querySelector('input[name="' + config.honeytimeField + '"]')) {
            return;
        }

        fetch(config.honeytimeUrl)
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

                container.querySelectorAll('input[name="' + config.honeytimeField + '"]').forEach(function (field) {
                    field.value = data.value;
                });
            });
    }

    function refreshAll() {
        document.querySelectorAll('[data-repliq-form]').forEach(refreshContainer);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', refreshAll);
    } else {
        refreshAll();
    }

    document.body.addEventListener('htmx:afterSwap', function (event) {
        var target = event.detail && event.detail.target;

        if (!target) {
            return;
        }

        if (target.hasAttribute('data-repliq-form')) {
            refreshContainer(target);
            return;
        }

        target.querySelectorAll('[data-repliq-form]').forEach(refreshContainer);
    });
})();
</script>
