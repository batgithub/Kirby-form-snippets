<?php

declare(strict_types=1);

use repliq\RepliqForm;

if (!function_exists('repliq_form')) {
    /**
     * @param array<string, mixed> $overrides
     * @return array{formConfig: RepliqForm, form: object, formKey: string, formAction: string, mode: string, honeytime: array<string, mixed>|null}|null
     */
    function repliq_form(string $key, array $overrides = []): ?array
    {
        return RepliqForm::fromKey($key, $overrides);
    }
}
