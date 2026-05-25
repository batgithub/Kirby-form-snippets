<?php

declare(strict_types=1);

namespace repliq;

use Kirby\Toolkit\Str;

class RepliqForm
{
    private array $inputs;

    public function __construct(array $inputs)
    {
        $this->inputs = $inputs;
    }

    public function getRules(): array
    {
        $rules = [];

        foreach ($this->inputs as $id => $input) {
            $fieldRules = match ($input['input']) {
                'honeypot' => $this->rulesForHoneypot(),
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

    private function rulesForHoneypot(): array
    {
        $maxLength = $this->maxLength('honeypot');

        return $this->ruleSet(
            ['maxLength' => $maxLength],
            [$this->message('honeypot')]
        );
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

        if ($this->isRequired($input)) {
            $inputRules[] = 'required';
            $inputMessages[] = $this->message('requiredSelect');
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
        if (!$this->isRequired($input)) {
            return $this->ruleSet([], []);
        }

        return $this->ruleSet(
            ['required'],
            [$this->message('requiredCheckboxGroup')]
        );
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
