<?php

declare(strict_types=1);

namespace repliq;

use Jevets\Kirby\Flash;
use Jevets\Kirby\Form as BaseForm;
use Kirby\Cms\Page;
use Kirby\Cms\StructureObject;
use Kirby\Http\Response;
use Kirby\Toolkit\Str;
use Uniform\Form;
use Uniform\Guards\HoneytimeGuard;

class RepliqForm
{
    private static bool $htmxScriptLoaded = false;

    /** @var list<string> */
    private const EMAIL_SKIP_INPUTS = [
        'honeypot',
        'line',
        'section-title',
        'card',
        'info',
    ];

    private array $inputs;

    private string $mode;

    public function __construct(array $inputs, string $mode = 'submit')
    {
        $this->mode = $mode;
        $this->inputs = self::resolveFields($inputs);
    }

    /**
     * @param array<string, mixed> $overrides
     * @return array{formConfig: self, form: Form|RepliqFilterState, formKey: string, formAction: string, mode: string, honeytime: array<string, mixed>|null}|null
     */
    public static function fromKey(string $key, array $overrides = []): ?array
    {
        $config = self::buildConfig($key, $overrides, 'render');

        if ($config === null) {
            return null;
        }

        $mode = self::formMode($config);
        $formConfig = new self($config['fields'], $mode);

        if ($mode === 'filter') {
            $form = new RepliqFilterState();
            $formAction = page()?->url() ?? url('/');
        } else {
            $form = new Form($formConfig->getRules());
            $formAction = self::submitUrl($key);
        }

        return [
            'formConfig' => $formConfig,
            'form' => $form,
            'formKey' => $key,
            'formAction' => $formAction,
            'mode' => $mode,
            'honeytime' => self::resolveHoneytimeGuardOptions($config),
        ];
    }

    public static function submitUrl(string $key): string
    {
        $route = (string) option('baptiste.kirby-form-snippets.submit.route');

        return url($route . '/' . $key);
    }

    public static function handleSubmit(string $key): ?Response
    {
        $config = self::buildConfig($key, [], 'submit');

        if ($config === null) {
            go('/');
        }

        if (self::formMode($config) === 'filter') {
            go('/');
        }

        $formConfig = new self($config['fields']);
        $form = new Form($formConfig->getRules());
        $pipeline = self::applySpamGuards($form, $config);

        $email = is_array($config['email'] ?? null) ? $config['email'] : [];
        $emailConfig = self::resolveEmailConfig($email, $key);
        $emailConfig['data'] = array_merge(
            $formConfig->buildEmailData($form, $config, $key),
            is_array($emailConfig['data'] ?? null) ? $emailConfig['data'] : []
        );

        unset($emailConfig['theme'], $emailConfig['themeFrom'], $emailConfig['templateData']);

        if (self::isHtmxRequest()) {
            $pipeline
                ->withoutRedirect()
                ->withoutFlashing()
                ->emailAction($emailConfig)
                ->done();

            return self::respondHtmx($key, $pipeline, $config);
        }

        $pipeline->emailAction($emailConfig)->done();

        return null;
    }

    public static function isHtmxRequest(): bool
    {
        return kirby()->request()->header('HX-Request') === 'true';
    }

    public static function isHtmxScriptLoaded(): bool
    {
        return self::$htmxScriptLoaded;
    }

    public static function markHtmxScriptLoaded(): void
    {
        self::$htmxScriptLoaded = true;
    }

    /**
     * @param bool|array<string, mixed>|null $snippetOverride
     * @return array{
     *     enabled: bool,
     *     swap: string,
     *     target: string,
     *     indicator: string|null,
     *     disabledElt: string|null,
     *     loadScript: bool,
     *     script: string,
     *     scriptIntegrity: string|null,
     *     scriptCrossorigin: string|null
     * }
     */
    public static function resolveHtmxSetting(
        string $formKey,
        bool|array|null $snippetOverride = null
    ): array {
        $global = option('baptiste.kirby-form-snippets.htmx');
        $global = is_array($global) ? $global : [];

        if ($snippetOverride !== null) {
            return self::normalizeHtmxSetting($snippetOverride, $formKey, $global);
        }

        $formConfig = self::getFormConfig($formKey);

        if (isset($formConfig['htmx'])) {
            return self::normalizeHtmxSetting($formConfig['htmx'], $formKey, $global);
        }

        return self::normalizeHtmxSetting($global, $formKey, $global);
    }

    /**
     * @param array{
     *     enabled: bool,
     *     swap: string,
     *     target: string,
     *     indicator: string|null,
     *     disabledElt: string|null
     * } $setting
     * @return array<string, string>
     */
    public static function htmxFormAttributes(array $setting, string $formAction): array
    {
        if (!$setting['enabled']) {
            return [];
        }

        $attrs = [
            'hx-post' => $formAction,
            'hx-target' => $setting['target'],
            'hx-swap' => $setting['swap'],
        ];

        if (is_string($setting['indicator'] ?? null) && $setting['indicator'] !== '') {
            $attrs['hx-indicator'] = $setting['indicator'];
        }

        if (is_string($setting['disabledElt'] ?? null) && $setting['disabledElt'] !== '') {
            $attrs['hx-disabled-elt'] = $setting['disabledElt'];
        }

        return $attrs;
    }

    /**
     * @param array<string, mixed> $config
     */
    private static function respondHtmx(string $key, Form $form, array $config): Response
    {
        if (!$form->success()) {
            self::flashFormDataForRender($form);
        }

        $mode = self::formMode($config);
        $formConfig = new self($config['fields'], $mode);

        $html = snippet('form-page', [
            'formKey' => $key,
            'formData' => [
                'formConfig' => $formConfig,
                'form' => $form,
                'formKey' => $key,
                'formAction' => self::submitUrl($key),
                'mode' => $mode,
                'honeytime' => self::resolveHoneytimeGuardOptions($config),
            ],
            'htmx' => [
                'enabled' => true,
                'loadScript' => false,
            ],
        ], true);

        return new Response((string) $html, 'text/html');
    }

    private static function flashFormDataForRender(Form $form): void
    {
        Flash::getInstance()->set(
            BaseForm::FLASH_KEY_DATA,
            $form->data('', '', false)
        );
    }

    /**
     * @param bool|array<string, mixed>|mixed $setting
     * @param array<string, mixed> $global
     * @return array{
     *     enabled: bool,
     *     swap: string,
     *     target: string,
     *     indicator: string|null,
     *     disabledElt: string|null,
     *     loadScript: bool,
     *     script: string,
     *     scriptIntegrity: string|null,
     *     scriptCrossorigin: string|null
     * }
     */
    private static function normalizeHtmxSetting(
        mixed $setting,
        string $formKey,
        array $global
    ): array {
        $defaults = [
            'enabled' => false,
            'swap' => 'outerHTML',
            'target' => null,
            'indicator' => null,
            'disabledElt' => 'find button[type=submit]',
            'loadScript' => true,
            'script' => 'https://cdn.jsdelivr.net/npm/htmx.org@2.0.10/dist/htmx.min.js',
            'scriptIntegrity' => null,
            'scriptCrossorigin' => 'anonymous',
        ];

        $merged = array_replace($defaults, $global);

        if (is_bool($setting)) {
            $merged['enabled'] = $setting;
        } elseif (is_array($setting)) {
            $merged = array_replace($merged, $setting);
            $merged['enabled'] = (bool) ($setting['enabled'] ?? $merged['enabled'] ?? false);
        } else {
            $merged['enabled'] = (bool) ($merged['enabled'] ?? false);
        }

        $containerId = 'repliq-form-' . $formKey;
        $target = $merged['target'] ?? null;

        if (!is_string($target) || $target === '') {
            $target = '#' . $containerId;
        } elseif (!str_starts_with($target, '#') && !str_starts_with($target, '.')) {
            $target = '#' . $target;
        }

        return [
            'enabled' => (bool) $merged['enabled'],
            'swap' => is_string($merged['swap'] ?? null) && $merged['swap'] !== ''
                ? $merged['swap']
                : 'outerHTML',
            'target' => $target,
            'indicator' => is_string($merged['indicator'] ?? null) && $merged['indicator'] !== ''
                ? $merged['indicator']
                : null,
            'disabledElt' => is_string($merged['disabledElt'] ?? null) && $merged['disabledElt'] !== ''
                ? $merged['disabledElt']
                : null,
            'loadScript' => (bool) ($merged['loadScript'] ?? true),
            'script' => is_string($merged['script'] ?? null) && $merged['script'] !== ''
                ? $merged['script']
                : $defaults['script'],
            'scriptIntegrity' => is_string($merged['scriptIntegrity'] ?? null) && $merged['scriptIntegrity'] !== ''
                ? $merged['scriptIntegrity']
                : null,
            'scriptCrossorigin' => is_string($merged['scriptCrossorigin'] ?? null) && $merged['scriptCrossorigin'] !== ''
                ? $merged['scriptCrossorigin']
                : null,
        ];
    }

    public static function htmxContainerId(string $formKey): string
    {
        return 'repliq-form-' . $formKey;
    }

    /**
     * @param array<string, mixed> $config
     * @return array{key: string, seconds: int, field: string}|null
     */
    public static function resolveHoneytimeGuardOptions(array $config): ?array
    {
        $global = option('baptiste.kirby-form-snippets.honeytime');
        $global = is_array($global) ? $global : [];
        $formSetting = $config['honeytime'] ?? null;

        if ($formSetting === false) {
            return null;
        }

        $enabled = false;
        $overrides = [];

        if ($formSetting === true) {
            $enabled = true;
        } elseif (is_array($formSetting)) {
            $enabled = true;
            $overrides = $formSetting;
        } elseif ($formSetting === null) {
            $enabled = (bool) ($global['enabled'] ?? false);
        }

        if (!$enabled) {
            return null;
        }

        $merged = array_replace_recursive($global, $overrides);
        $key = self::resolveHoneytimeKey($merged);

        if ($key === null) {
            return null;
        }

        $seconds = $merged['seconds'] ?? 10;
        $field = $merged['field'] ?? 'uniform-honeytime';

        return [
            'key' => $key,
            'seconds' => is_numeric($seconds) ? (int) $seconds : 10,
            'field' => is_string($field) && $field !== '' ? $field : 'uniform-honeytime',
        ];
    }

    public static function generateHoneytimeValue(): ?string
    {
        $global = option('baptiste.kirby-form-snippets.honeytime');
        $global = is_array($global) ? $global : [];
        $key = self::resolveHoneytimeKey($global);

        if ($key === null) {
            return null;
        }

        return HoneytimeGuard::encrypt($key, (string) time());
    }

    /**
     * @param array<string, mixed> $config
     */
    public static function applySpamGuards(Form $form, array $config): Form
    {
        $pipeline = $form;
        $honeypotField = self::resolveHoneypotField($config['fields']);

        if ($honeypotField !== null) {
            $pipeline = $pipeline->honeypotGuard(['field' => $honeypotField]);
        }

        $honeytimeOptions = self::resolveHoneytimeGuardOptions($config);

        if ($honeytimeOptions !== null) {
            $pipeline = $pipeline->honeytimeGuard($honeytimeOptions);
        }

        return $pipeline;
    }

    /**
     * @param array<string, array<string, mixed>> $fields
     */
    public static function resolveHoneypotField(array $fields): ?string
    {
        foreach ($fields as $id => $field) {
            if (($field['input'] ?? null) !== 'honeypot') {
                continue;
            }

            if (isset($field['name']) && is_string($field['name']) && $field['name'] !== '') {
                return $field['name'];
            }

            return $id;
        }

        return null;
    }

    /**
     * @param bool|array<string, mixed>|null $snippetOverride
     * @return array{enabled: bool, title: string}
     */
    public static function resolveErrorsSummarySetting(
        string $formKey,
        bool|array|null $snippetOverride = null
    ): array {
        $title = 'Le formulaire contient des erreurs';
        $global = option('baptiste.kirby-form-snippets.errorsSummary');

        if (is_array($global) && is_string($global['title'] ?? null) && $global['title'] !== '') {
            $title = $global['title'];
        }

        if ($snippetOverride !== null) {
            return self::normalizeErrorsSummarySetting($snippetOverride, $title);
        }

        $formConfig = self::getFormConfig($formKey);

        if (isset($formConfig['errorsSummary'])) {
            return self::normalizeErrorsSummarySetting($formConfig['errorsSummary'], $title);
        }

        return self::normalizeErrorsSummarySetting($global, $title);
    }

    /**
     * @param array<string, mixed> $config
     * @return list<string>
     */
    public static function errorsSummarySkipKeys(array $config): array
    {
        $skip = [
            (string) option('baptiste.kirby-form-snippets.csrf.field', 'csrf_token'),
        ];

        $fields = is_array($config['fields'] ?? null) ? $config['fields'] : [];
        $honeypot = self::resolveHoneypotField($fields);

        if ($honeypot !== null) {
            $skip[] = $honeypot;
        }

        $honeytime = self::resolveHoneytimeGuardOptions($config);

        if ($honeytime !== null) {
            $skip[] = $honeytime['field'];
        }

        return $skip;
    }

    /**
     * @param object $form
     * @param array<string, mixed> $overrides
     * @return list<array{id: string, label: string, messages: list<string>}>
     */
    public static function buildErrorsSummary(
        object $form,
        string $formKey,
        array $overrides = []
    ): array {
        if (!method_exists($form, 'errors') || count($form->errors()) === 0) {
            return [];
        }

        $config = self::buildConfig($formKey, $overrides, 'render');

        if ($config === null) {
            return [];
        }

        $fields = is_array($config['fields'] ?? null) ? $config['fields'] : [];
        $skipKeys = self::errorsSummarySkipKeys($config);
        $items = [];

        foreach ($form->errors() as $fieldKey => $messages) {
            if (!is_string($fieldKey) || !is_array($messages) || $messages === []) {
                continue;
            }

            if (in_array($fieldKey, $skipKeys, true)) {
                continue;
            }

            $field = $fields[$fieldKey] ?? null;

            if (is_array($field) && self::isDecorativeFieldInput($field['input'] ?? null)) {
                continue;
            }

            if (is_array($field) && ($field['input'] ?? null) === 'honeypot') {
                continue;
            }

            $label = is_array($field) && is_string($field['label'] ?? null) && $field['label'] !== ''
                ? $field['label']
                : $fieldKey;

            $items[] = [
                'id' => $fieldKey,
                'label' => $label,
                'messages' => array_map('strval', $messages),
            ];
        }

        return $items;
    }

    /**
     * @param bool|array<string, mixed>|mixed $setting
     * @return array{enabled: bool, title: string}
     */
    private static function normalizeErrorsSummarySetting(mixed $setting, string $defaultTitle): array
    {
        if (is_bool($setting)) {
            return [
                'enabled' => $setting,
                'title' => $defaultTitle,
            ];
        }

        if (!is_array($setting)) {
            return [
                'enabled' => false,
                'title' => $defaultTitle,
            ];
        }

        $title = is_string($setting['title'] ?? null) && $setting['title'] !== ''
            ? $setting['title']
            : $defaultTitle;

        return [
            'enabled' => (bool) ($setting['enabled'] ?? false),
            'title' => $title,
        ];
    }

    private static function isDecorativeFieldInput(mixed $input): bool
    {
        return in_array($input, ['line', 'card', 'section-title', 'info'], true);
    }

    /**
     * @param array<string, mixed> $overrides
     * @return array<string, mixed>|null
     */
    public static function buildConfig(string $key, array $overrides = [], string $context = 'render'): ?array
    {
        $config = self::getFormConfig($key);

        if ($config === null || !isset($config['fields']) || !is_array($config['fields'])) {
            return null;
        }

        $config = self::applyHook($config, $key, $context);

        if ($overrides !== []) {
            $config = self::mergeConfig($config, $overrides);
        }

        return $config;
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function getFormConfig(string $key): ?array
    {
        $config = option('baptiste.kirby-form-snippets.forms.' . $key);

        return is_array($config) ? $config : null;
    }

    /**
     * @param array<string, mixed> $email
     * @return array<string, mixed>
     */
    public static function resolveEmailConfig(array $email, string $formKey): array
    {
        if (isset($email['toFrom'])) {
            $toFrom = $email['toFrom'];
            unset($email['toFrom']);

            if (is_callable($toFrom)) {
                $to = ($toFrom)($formKey);
            } else {
                $to = self::resolveEmailTo($toFrom, $formKey);
            }

            if ($to !== null && $to !== '') {
                $email['to'] = $to;
            } elseif (!isset($email['to'])) {
                $default = option('baptiste.kirby-form-snippets.defaultEmailTo');

                if (is_string($default) && $default !== '') {
                    $email['to'] = $default;
                }
            }
        }

        if (!isset($email['template'])) {
            $template = option('baptiste.kirby-form-snippets.defaultEmailTemplate');

            if (is_string($template) && $template !== '') {
                $email['template'] = $template;
            }
        }

        return $email;
    }

    /**
     * @param array<string, mixed> $config
     * @return array<string, mixed>
     */
    public function buildEmailData(Form $form, array $config, string $formKey): array
    {
        $email = is_array($config['email'] ?? null) ? $config['email'] : [];
        $theme = self::resolveEmailTheme($email, $formKey);
        $templateData = is_array($email['templateData'] ?? null) ? $email['templateData'] : [];

        $site = site();
        $formName = is_string($config['title'] ?? null) && $config['title'] !== ''
            ? $config['title']
            : $formKey;
        $date = self::formatEmailDate();

        $data = [
            'formName' => $formName,
            'formKey' => $formKey,
            'date' => $date,
            'datas' => $this->buildEmailFieldsData($form, $theme),
            'theme' => $theme,
            'preview' => is_string($theme['preview'] ?? null) && $theme['preview'] !== ''
                ? $theme['preview']
                : $formName . ' · ' . $date,
            'siteName' => $site?->title()->value() ?? '',
            'siteUrl' => $site?->url() ?? '',
        ];

        return array_merge($data, $templateData);
    }

    /**
     * @param array<string, mixed> $email
     * @return array<string, mixed>
     */
    public static function resolveEmailTheme(array $email, string $formKey): array
    {
        $defaults = option('baptiste.kirby-form-snippets.defaultEmailTheme');
        $defaults = is_array($defaults) ? $defaults : [];
        $theme = is_array($email['theme'] ?? null) ? $email['theme'] : [];

        if (isset($email['themeFrom'])) {
            $fromPanel = self::resolveThemeFrom($email['themeFrom'], $formKey);
            $theme = array_replace_recursive($fromPanel, $theme);
        }

        return self::mergeEmailTheme($defaults, $theme);
    }

    public function getRules(): array
    {
        if ($this->mode === 'filter') {
            return [];
        }

        $rules = [];

        foreach ($this->inputs as $id => $input) {
            $fieldRules = match ($input['input']) {
                'input' => $this->rulesForInput($input),
                'textarea' => $this->rulesForTextarea($input),
                'select' => $this->rulesForSelect($input),
                'checkbox' => $this->rulesForCheckbox($input),
                'checkbox-group' => $this->rulesForCheckboxGroup($input),
                'radio-group' => $this->rulesForRadioGroup($input),
                default => null,
            };

            if ($fieldRules !== null) {
                $rules[$id] = $fieldRules;
            }
        }

        return $rules;
    }

    public function getInputs(object $form): array
    {
        $snippets = [];

        foreach ($this->inputs as $id => $input) {
            if ($this->mode === 'filter' && $input['input'] === 'honeypot') {
                continue;
            }

            $params = [
                'id' => $id,
                'form' => $form,
            ];

            foreach ($input as $key => $param) {
                if ($key !== 'input') {
                    $params[$key] = $param;
                }
            }

            $snippets[$id] = snippet('form-' . $input['input'], $params);
        }

        return $snippets;
    }

    public static function optionValue(array $option): string
    {
        if (isset($option['value'])) {
            return (string) $option['value'];
        }

        return Str::slug((string) $option['label']);
    }

    /**
     * @param array<string, array<string, mixed>> $fields
     * @return array<string, array<string, mixed>>
     */
    private static function resolveFields(array $fields): array
    {
        foreach ($fields as $id => &$field) {
            if (!isset($field['optionsFrom'])) {
                continue;
            }

            if (is_callable($field['optionsFrom'])) {
                $resolved = ($field['optionsFrom'])();
                $resolved = is_array($resolved) ? $resolved : [];
            } elseif (is_array($field['optionsFrom'])) {
                $resolved = self::resolveOptions($field['optionsFrom']);
            } else {
                continue;
            }

            $static = isset($field['options']) && is_array($field['options']) ? $field['options'] : [];
            $field['options'] = array_merge($static, $resolved);
            unset($field['optionsFrom']);
        }

        return $fields;
    }

    /**
     * @param array<string, mixed> $base
     * @param array<string, mixed> $overrides
     * @return array<string, mixed>
     */
    private static function mergeConfig(array $base, array $overrides): array
    {
        if (isset($overrides['mode'])) {
            $base['mode'] = $overrides['mode'];
        }

        if (isset($overrides['email']) && is_array($overrides['email'])) {
            $base['email'] = array_replace_recursive($base['email'] ?? [], $overrides['email']);
        }

        if (isset($overrides['fields']) && is_array($overrides['fields'])) {
            foreach ($overrides['fields'] as $fieldId => $fieldOverride) {
                if (!is_string($fieldId) || !is_array($fieldOverride)) {
                    continue;
                }

                $existing = isset($base['fields'][$fieldId]) && is_array($base['fields'][$fieldId])
                    ? $base['fields'][$fieldId]
                    : [];

                if (array_key_exists('options', $fieldOverride)) {
                    $options = $fieldOverride['options'];
                    unset($fieldOverride['options']);
                    $merged = array_replace_recursive($existing, $fieldOverride);
                    $merged['options'] = $options;
                    $base['fields'][$fieldId] = $merged;
                } else {
                    $base['fields'][$fieldId] = array_replace_recursive($existing, $fieldOverride);
                }
            }
        }

        return $base;
    }

    /**
     * @param array<string, mixed> $settings
     */
    private static function resolveHoneytimeKey(array $settings): ?string
    {
        $candidates = [
            $settings['key'] ?? null,
            option('baptiste.kirby-form-snippets.honeytime.key'),
            option('uniform.honeytime.key'),
        ];

        foreach ($candidates as $candidate) {
            if (!is_string($candidate) || $candidate === '') {
                continue;
            }

            return self::normalizeHoneytimeKey($candidate);
        }

        return null;
    }

    private static function normalizeHoneytimeKey(string $key): string
    {
        if (str_starts_with($key, 'base64:')) {
            return substr($key, 7);
        }

        return $key;
    }

    /**
     * @param array<string, mixed> $config
     * @return array<string, mixed>
     */
    private static function applyHook(array $config, string $formKey, string $context): array
    {
        $kirby = kirby();

        if ($kirby === null) {
            return $config;
        }

        $result = $kirby->apply('repliq.form.config', [
            'config' => $config,
            'formKey' => $formKey,
            'context' => $context,
        ]);

        return is_array($result) ? $result : $config;
    }

    /**
     * @param array<string, mixed> $source
     * @return array<int, array<string, string>>
     */
    private static function resolveOptions(array $source): array
    {
        $type = $source['type'] ?? null;

        return match ($type) {
            'pages' => self::resolveOptionsFromPages($source),
            'structure' => self::resolveOptionsFromStructure($source),
            default => [],
        };
    }

    /**
     * @param array<string, mixed> $source
     * @return array<int, array<string, string>>
     */
    private static function resolveOptionsFromPages(array $source): array
    {
        $parent = $source['parent'] ?? null;

        if (!is_string($parent) || $parent === '') {
            return [];
        }

        $parentPage = page($parent);

        if ($parentPage === null) {
            return [];
        }

        $labelKey = $source['label'] ?? 'title';
        $valueKey = $source['value'] ?? 'slug';
        $options = [];

        foreach ($parentPage->children()->listed() as $child) {
            $options[] = [
                'label' => self::resolvePageOptionValue($child, $labelKey),
                'value' => self::resolvePageOptionValue($child, $valueKey),
            ];
        }

        return $options;
    }

    /**
     * @param array<string, mixed> $source
     * @return array<int, array<string, string>>
     */
    private static function resolveOptionsFromStructure(array $source): array
    {
        $field = $source['field'] ?? null;

        if (!is_string($field) || $field === '') {
            return [];
        }

        $page = self::resolveContentPage($source['page'] ?? 'site');

        if ($page === null) {
            return [];
        }

        $structure = $page->{$field}()->toStructure();
        $labelKey = $source['label'] ?? 'label';
        $valueKey = $source['value'] ?? 'value';
        $options = [];

        foreach ($structure as $item) {
            $options[] = [
                'label' => self::resolveStructureOptionValue($item, $labelKey),
                'value' => self::resolveStructureOptionValue($item, $valueKey),
            ];
        }

        return $options;
    }

    /**
     * @param string|callable(Page): string $key
     */
    private static function resolvePageOptionValue(Page $page, string|callable $key): string
    {
        if (is_callable($key)) {
            return (string) $key($page);
        }

        return match ($key) {
            'title' => $page->title()->value(),
            'slug' => $page->slug(),
            default => $page->{$key}()->value(),
        };
    }

    /**
     * @param string|callable(StructureObject): string $key
     */
    private static function resolveStructureOptionValue(StructureObject $item, string|callable $key): string
    {
        if (is_callable($key)) {
            return (string) $key($item);
        }

        return $item->{$key}()->value();
    }

    /**
     * @param array<string, mixed>|string $toFrom
     */
    private static function resolveEmailTo(array|string $toFrom, string $formKey): string|array|null
    {
        if (is_string($toFrom)) {
            return self::resolveFieldPath($toFrom);
        }

        $field = $toFrom['field'] ?? null;

        if (!is_string($field) || $field === '') {
            return null;
        }

        $page = self::resolveContentPage($toFrom['page'] ?? 'site');

        if ($page === null) {
            return null;
        }

        $matchKey = $toFrom['match'] ?? 'formKey';
        $valueKey = $toFrom['value'] ?? 'email';

        foreach ($page->{$field}()->toStructure() as $item) {
            if ($item->{$matchKey}()->value() === $formKey) {
                return $item->{$valueKey}()->value();
            }
        }

        return null;
    }

    private static function resolveFieldPath(string $path): ?string
    {
        $parts = explode('.', $path, 2);

        if (count($parts) !== 2) {
            return null;
        }

        [$pageId, $field] = $parts;
        $page = self::resolveContentPage($pageId);

        if ($page === null) {
            return null;
        }

        $value = $page->{$field}()->value();

        return $value !== '' ? $value : null;
    }

    /**
     * @param array<string, mixed> $base
     * @param array<string, mixed> $override
     * @return array<string, mixed>
     */
    private static function mergeEmailTheme(array $base, array $override): array
    {
        $merged = array_replace_recursive($base, $override);
        $site = site();

        if (($merged['logoLink'] ?? null) === null && $site !== null) {
            $merged['logoLink'] = $site->url();
        }

        if (($merged['logoAlt'] ?? null) === null && $site !== null) {
            $merged['logoAlt'] = $site->title()->value();
        }

        return $merged;
    }

    /**
     * @param array<string, mixed>|string $themeFrom
     * @return array<string, mixed>
     */
    private static function resolveThemeFrom(array|string $themeFrom, string $formKey): array
    {
        if (is_string($themeFrom)) {
            return self::resolveThemeFromPath($themeFrom);
        }

        $field = $themeFrom['field'] ?? null;

        if (!is_string($field) || $field === '') {
            return [];
        }

        $page = self::resolveContentPage($themeFrom['page'] ?? 'site');

        if ($page === null) {
            return [];
        }

        $structure = $page->{$field}()->toStructure();

        if ($structure->isEmpty()) {
            return [];
        }

        $matchKey = $themeFrom['match'] ?? null;

        if (is_string($matchKey) && $matchKey !== '') {
            foreach ($structure as $item) {
                if ($item->{$matchKey}()->value() === $formKey) {
                    return self::mapStructureItemToTheme($item);
                }
            }

            return [];
        }

        return self::mapStructureItemToTheme($structure->first());
    }

    /**
     * @return array<string, mixed>
     */
    private static function resolveThemeFromPath(string $path): array
    {
        $parts = explode('.', $path, 2);

        if (count($parts) !== 2) {
            return [];
        }

        [$pageId, $fieldName] = $parts;
        $page = self::resolveContentPage($pageId);

        if ($page === null) {
            return [];
        }

        $field = $page->{$fieldName}();

        if ($field->isEmpty()) {
            return [];
        }

        $structure = $field->toStructure();

        if ($structure->isNotEmpty()) {
            return self::mapStructureItemToTheme($structure->first());
        }

        $file = $field->toFile();

        if ($file !== null) {
            return ['logo' => $file->absoluteUrl()];
        }

        return [];
    }

    /**
     * @return array<string, mixed>
     */
    private static function mapStructureItemToTheme(StructureObject $item): array
    {
        $theme = [];
        $colorKeys = ['pageBg', 'cardBg', 'border', 'label', 'text', 'accent'];
        $scalarKeys = ['logoAlt', 'logoLink', 'intro', 'footer', 'preview', 'fontFamily', 'width'];

        foreach ($colorKeys as $key) {
            $value = $item->{$key}()->value();

            if ($value === '' && $key === 'accent') {
                $value = $item->accentColor()->value();
            }

            if ($value !== '') {
                $theme['colors'][$key] = $value;
            }
        }

        foreach ($scalarKeys as $key) {
            $value = $item->{$key}()->value();

            if ($value !== '') {
                $theme[$key] = $key === 'width' ? (int) $value : $value;
            }
        }

        if ($item->logo()->isNotEmpty()) {
            $logo = $item->logo()->toFile();

            if ($logo !== null) {
                $theme['logo'] = $logo->absoluteUrl();
            }
        }

        if (($theme['logo'] ?? null) === null && $item->emailLogo()->isNotEmpty()) {
            $logo = $item->emailLogo()->toFile();

            if ($logo !== null) {
                $theme['logo'] = $logo->absoluteUrl();
            }
        }

        $hideEmpty = $item->hideEmptyFields()->toBool();

        if ($hideEmpty) {
            $theme['hideEmptyFields'] = true;
        }

        return $theme;
    }

    /**
     * @param array<string, mixed> $theme
     * @return array<string, array<string, mixed>>
     */
    private function buildEmailFieldsData(Form $form, array $theme): array
    {
        $datas = [];

        foreach ($this->inputs as $id => $field) {
            if (self::shouldSkipEmailField($field)) {
                continue;
            }

            $raw = $form->data($id);
            $value = $this->resolveFieldDisplayValue($field, $raw);

            if (($theme['hideEmptyFields'] ?? false) === true && self::isEmptyEmailValue($value)) {
                continue;
            }

            $datas[$id] = [
                'label' => is_string($field['label'] ?? null) ? $field['label'] : $id,
                'value' => $value,
                'input' => is_string($field['input'] ?? null) ? $field['input'] : 'input',
            ];
        }

        return $datas;
    }

    /**
     * @param array<string, mixed> $field
     */
    private function resolveFieldDisplayValue(array $field, mixed $raw): string|array
    {
        $input = $field['input'] ?? 'input';

        return match ($input) {
            'checkbox' => ($raw !== null && $raw !== '') ? 'Oui' : 'Non',
            'checkbox-group' => $this->resolveOptionLabels($field, is_array($raw) ? $raw : []),
            'select', 'radio-group' => $this->resolveSelectDisplayValue($field, $raw),
            default => is_array($raw) ? implode(', ', array_map('strval', $raw)) : (string) ($raw ?? ''),
        };
    }

    /**
     * @param array<string, mixed> $field
     */
    private function resolveSelectDisplayValue(array $field, mixed $raw): string|array
    {
        if (isset($field['multiselect']) && $field['multiselect'] === true) {
            return $this->resolveOptionLabels($field, is_array($raw) ? $raw : []);
        }

        return $this->resolveOptionLabel($field, is_scalar($raw) ? (string) $raw : '');
    }

    /**
     * @param array<string, mixed> $field
     * @param array<int, mixed> $values
     * @return array<int, string>
     */
    private function resolveOptionLabels(array $field, array $values): array
    {
        $labels = [];

        foreach ($values as $value) {
            if (!is_scalar($value) || (string) $value === '') {
                continue;
            }

            $labels[] = $this->resolveOptionLabel($field, (string) $value);
        }

        return $labels;
    }

    /**
     * @param array<string, mixed> $field
     */
    private function resolveOptionLabel(array $field, string $value): string
    {
        if ($value === '') {
            return '';
        }

        if (!isset($field['options']) || !is_array($field['options'])) {
            return $value;
        }

        foreach ($field['options'] as $option) {
            if (!is_array($option)) {
                continue;
            }

            if (self::optionValue($option) === $value) {
                return (string) ($option['label'] ?? $value);
            }
        }

        return $value;
    }

    /**
     * @param array<string, mixed> $field
     */
    private static function shouldSkipEmailField(array $field): bool
    {
        $input = $field['input'] ?? null;

        return is_string($input) && in_array($input, self::EMAIL_SKIP_INPUTS, true);
    }

    private static function isEmptyEmailValue(mixed $value): bool
    {
        if (is_array($value)) {
            return $value === [];
        }

        return $value === null || $value === '';
    }

    private static function formatEmailDate(): string
    {
        $kirby = kirby();
        $locale = $kirby?->language()?->code() ?? 'fr_FR';

        if (class_exists(\IntlDateFormatter::class)) {
            $formatter = new \IntlDateFormatter(
                $locale,
                \IntlDateFormatter::LONG,
                \IntlDateFormatter::SHORT
            );
            $formatted = $formatter->format(time());

            if (is_string($formatted) && $formatted !== '') {
                return $formatted;
            }
        }

        return date('d/m/Y H:i');
    }

    private static function resolveContentPage(string $pageId): Page|\Kirby\Cms\Site|null
    {
        if ($pageId === 'site') {
            return site();
        }

        return page($pageId);
    }

    /**
     * @param array<string, mixed> $config
     */
    private static function formMode(array $config): string
    {
        $mode = $config['mode'] ?? 'submit';

        return $mode === 'filter' ? 'filter' : 'submit';
    }

    private function message(string $key): string
    {
        return (string) option('baptiste.kirby-form-snippets.messages.' . $key);
    }

    private function maxLength(string $field): int
    {
        return (int) option('baptiste.kirby-form-snippets.maxLength.' . $field);
    }

    private function isRequired(array $input): bool
    {
        return isset($input['required']) && $input['required'] === true;
    }

    /**
     * @param array<int|string, mixed> $rules
     * @param array<int, string> $messages
     */
    private function ruleSet(array $rules, array $messages): array
    {
        return [
            'rules' => $rules,
            'message' => $messages,
        ];
    }

    /**
     * @param array<int, array<string, mixed>> $options
     * @return array<int, string>
     */
    private function optionValues(array $options): array
    {
        return array_map(
            fn (array $option) => self::optionValue($option),
            $options
        );
    }

    /**
     * @param array<int|string, mixed> $rules
     * @param array<int, string> $messages
     */
    private function appendInRule(array &$rules, array &$messages, array $input): void
    {
        if (!isset($input['options']) || !is_array($input['options'])) {
            return;
        }

        $values = $this->optionValues($input['options']);

        if ($values === []) {
            return;
        }

        $rules['in'] = $values;
        $messages[] = $this->message('in');
    }

    private function rulesForInput(array $input): array
    {
        $inputRules = [];
        $inputMessages = [];

        if ($this->isRequired($input)) {
            $inputRules[] = 'required';
            $inputMessages[] = $this->message('required');
        }

        $type = $input['type'] ?? 'text';

        if ($type === 'email') {
            $inputRules[] = 'email';
            $inputMessages[] = $this->message('email');
        }

        if ($type === 'phone' || $type === 'tel') {
            $inputRules[] = 'tel';
            $inputMessages[] = $this->message('tel');
        }

        $maxLength = $this->maxLength('input');
        $inputRules['maxLength'] = $maxLength;
        $inputMessages[] = $this->message('maxLengthInput');

        return $this->ruleSet($inputRules, $inputMessages);
    }

    private function rulesForTextarea(array $input): array
    {
        $inputRules = [];
        $inputMessages = [];

        if ($this->isRequired($input)) {
            $inputRules[] = 'required';
            $inputMessages[] = $this->message('required');
        }

        $maxLength = $this->maxLength('textarea');
        $inputRules['maxLength'] = $maxLength;
        $inputMessages[] = $this->message('maxLengthTextarea');

        return $this->ruleSet($inputRules, $inputMessages);
    }

    private function rulesForSelect(array $input): array
    {
        $inputRules = [];
        $inputMessages = [];
        $multiselect = isset($input['multiselect']) && $input['multiselect'] === true;

        if ($this->isRequired($input)) {
            $inputRules[] = $multiselect ? 'notEmpty' : 'required';
            $inputMessages[] = $this->message('requiredSelect');
        }

        if ($multiselect) {
            $this->appendInRule($inputRules, $inputMessages, $input);
        } elseif (isset($input['options']) && is_array($input['options'])) {
            $values = $this->optionValues($input['options']);

            if ($values !== []) {
                $inputRules[] = 'in:' . implode(',', $values);
                $inputMessages[] = $this->message('in');
            }
        }

        return $this->ruleSet($inputRules, $inputMessages);
    }

    private function rulesForCheckbox(array $input): array
    {
        if (!$this->isRequired($input)) {
            return $this->ruleSet([], []);
        }

        return $this->ruleSet(
            ['required'],
            [$this->message('requiredCheckbox')]
        );
    }

    private function rulesForCheckboxGroup(array $input): array
    {
        $inputRules = [];
        $inputMessages = [];

        if ($this->isRequired($input)) {
            $inputRules[] = 'notEmpty';
            $inputMessages[] = $this->message('requiredCheckboxGroup');
        }

        $this->appendInRule($inputRules, $inputMessages, $input);

        return $this->ruleSet($inputRules, $inputMessages);
    }

    private function rulesForRadioGroup(array $input): array
    {
        $inputRules = [];
        $inputMessages = [];

        if ($this->isRequired($input)) {
            $inputRules[] = 'required';
            $inputMessages[] = $this->message('requiredRadioGroup');
        }

        if (isset($input['options']) && is_array($input['options'])) {
            $values = $this->optionValues($input['options']);
            if ($values !== []) {
                $inputRules[] = 'in:' . implode(',', $values);
                $inputMessages[] = $this->message('in');
            }
        }

        return $this->ruleSet($inputRules, $inputMessages);
    }
}
