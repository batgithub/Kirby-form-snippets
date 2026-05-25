<?php

declare(strict_types=1);

namespace repliq;

use Kirby\Cms\Page;
use Kirby\Cms\StructureObject;
use Kirby\Toolkit\Str;
use Uniform\Form;

class RepliqForm
{
    private array $inputs;

    private string $mode;

    public function __construct(array $inputs, string $mode = 'submit')
    {
        $this->mode = $mode;
        $this->inputs = self::resolveFields($inputs);
    }

    /**
     * @param array<string, mixed> $overrides
     * @return array{formConfig: self, form: Form|RepliqFilterState, formKey: string, formAction: string, mode: string}|null
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
        ];
    }

    public static function submitUrl(string $key): string
    {
        $route = (string) option('baptiste.kirby-form-snippets.submit.route');

        return url($route . '/' . $key);
    }

    public static function handleSubmit(string $key): void
    {
        $config = self::buildConfig($key, [], 'submit');

        if ($config === null) {
            go('/');
        }

        if (self::formMode($config) === 'filter') {
            go('/');
        }

        $form = new Form((new self($config['fields']))->getRules());
        $honeypotField = self::resolveHoneypotField($config['fields']);

        if ($honeypotField !== null) {
            $form->honeypotGuard(['field' => $honeypotField]);
        }

        $emailConfig = self::resolveEmailConfig($config['email'] ?? [], $key);
        $form->emailAction($emailConfig)->done();
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
     * @param array<string, mixed> $config
     * @return array<string, mixed>
     */
    private static function applyHook(array $config, string $formKey, string $context): array
    {
        $kirby = kirby();

        if ($kirby === null) {
            return $config;
        }

        /** @var array<string, mixed> */
        return $kirby->apply('repliq.form.config', [$config, $formKey, $context], 'array');
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
