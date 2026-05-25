<?php

declare(strict_types=1);

namespace Repliq\Tests;

use repliq\RepliqFilterState;
use repliq\RepliqForm;
use Jevets\Kirby\Validator;
use Uniform\Guards\HoneytimeGuard;
use Uniform\Guards\HoneypotGuard;

final class RepliqFormTest extends TestCase
{
    public function testOptionValueUsesExplicitValue(): void
    {
        $this->assertSame(
            'support',
            RepliqForm::optionValue(['label' => 'Support', 'value' => 'support'])
        );
    }

    public function testOptionValueSlugsLabelWhenValueMissing(): void
    {
        $this->assertSame(
            'support-technique',
            RepliqForm::optionValue(['label' => 'Support technique'])
        );
    }

    public function testResolveHoneypotFieldUsesCustomName(): void
    {
        $fields = [
            'website' => [
                'input' => 'honeypot',
                'name' => 'company_url',
            ],
        ];

        $this->assertSame('company_url', RepliqForm::resolveHoneypotField($fields));
    }

    public function testResolveHoneypotFieldFallsBackToFieldId(): void
    {
        $fields = [
            'website' => [
                'input' => 'honeypot',
            ],
        ];

        $this->assertSame('website', RepliqForm::resolveHoneypotField($fields));
    }

    public function testResolveHoneypotFieldReturnsNullWhenAbsent(): void
    {
        $this->assertNull(RepliqForm::resolveHoneypotField([
            'email' => ['input' => 'input', 'type' => 'email'],
        ]));
    }

    public function testBuildConfigReturnsNullForUnknownForm(): void
    {
        $this->assertNull(RepliqForm::buildConfig('unknown-form'));
    }

    public function testBuildConfigMergesFieldOverrides(): void
    {
        $config = RepliqForm::buildConfig('contact', [
            'fields' => [
                'name' => [
                    'required' => false,
                    'label' => 'Nom complet',
                ],
            ],
        ]);

        $this->assertNotNull($config);
        $this->assertFalse($config['fields']['name']['required']);
        $this->assertSame('Nom complet', $config['fields']['name']['label']);
        $this->assertTrue($config['fields']['email']['required']);
    }

    public function testFromKeyReturnsFilterModeWithoutValidationRules(): void
    {
        $result = RepliqForm::fromKey('filter');

        $this->assertNotNull($result);
        $this->assertSame('filter', $result['mode']);
        $this->assertInstanceOf(RepliqFilterState::class, $result['form']);
        $this->assertSame([], $result['formConfig']->getRules());
    }

    public function testFromKeyBuildsSubmitUrl(): void
    {
        $result = RepliqForm::fromKey('contact');

        $this->assertNotNull($result);
        $this->assertStringContainsString('kirby-form-snippets/submit/contact', $result['formAction']);
    }

    public function testGetRulesForContactForm(): void
    {
        $config = RepliqForm::buildConfig('contact');
        $this->assertNotNull($config);

        $formConfig = new RepliqForm($config['fields']);
        $rules = $formConfig->getRules();

        $this->assertArrayHasKey('name', $rules);
        $this->assertContains('required', $rules['name']['rules']);
        $this->assertContains('email', $rules['email']['rules']);
        $this->assertArrayHasKey('maxLength', $rules['message']['rules']);
        $this->assertArrayNotHasKey('website', $rules);
    }

    public function testGetRulesForSelectCheckboxAndRadioGroups(): void
    {
        $config = RepliqForm::buildConfig('options');
        $this->assertNotNull($config);

        $formConfig = new RepliqForm($config['fields']);
        $rules = $formConfig->getRules();

        $this->assertContains('required', $rules['topic']['rules']);
        $this->assertContains('in:support,sales', $rules['topic']['rules']);
        $this->assertContains('notEmpty', $rules['tags']['rules']);
        $this->assertArrayHasKey('in', $rules['tags']['rules']);
        $this->assertContains('required', $rules['plan']['rules']);
        $this->assertContains('required', $rules['consent']['rules']);
    }

    public function testResolveHoneytimeGuardOptionsDisabledByDefault(): void
    {
        $config = RepliqForm::buildConfig('contact');
        $this->assertNotNull($config);

        $this->assertNull(RepliqForm::resolveHoneytimeGuardOptions($config));
    }

    public function testResolveHoneytimeGuardOptionsWhenEnabledOnForm(): void
    {
        $config = RepliqForm::buildConfig('honeytime');
        $this->assertNotNull($config);

        $options = RepliqForm::resolveHoneytimeGuardOptions($config);

        $this->assertNotNull($options);
        $this->assertSame(10, $options['seconds']);
        $this->assertSame('uniform-honeytime', $options['field']);
        $this->assertNotEmpty($options['key']);
    }

    public function testGenerateHoneytimeValueReturnsEncryptedTimestamp(): void
    {
        $value = RepliqForm::generateHoneytimeValue();

        $this->assertNotNull($value);
        $this->assertNotSame('', $value);
    }

    public function testResolveEmailConfigUsesDefaultTemplateAndRecipient(): void
    {
        $email = RepliqForm::resolveEmailConfig([
            'toFrom' => fn () => null,
        ], 'contact');

        $this->assertSame('test@example.com', $email['to']);
        $this->assertSame('submition', $email['template']);
    }

    public function testResolveEmailThemeMergesDefaultsWithSiteContext(): void
    {
        $theme = RepliqForm::resolveEmailTheme([
            'theme' => [
                'colors' => ['accent' => '#FF0000'],
            ],
        ], 'contact');

        $this->assertSame('#FF0000', $theme['colors']['accent']);
        $this->assertSame('http://localhost', $theme['logoLink']);
        $this->assertSame('Test Site', $theme['logoAlt']);
    }

    public function testRepliqFormHelperMatchesFromKey(): void
    {
        $fromKey = RepliqForm::fromKey('contact');
        $fromHelper = repliq_form('contact');

        $this->assertNotNull($fromKey);
        $this->assertNotNull($fromHelper);
        $this->assertSame($fromKey['formKey'], $fromHelper['formKey']);
        $this->assertSame($fromKey['mode'], $fromHelper['mode']);
        $this->assertSame($fromKey['formAction'], $fromHelper['formAction']);
    }

    public function testValidationRulesRejectMissingRequiredFields(): void
    {
        $config = RepliqForm::buildConfig('contact');
        $this->assertNotNull($config);

        $formConfig = new RepliqForm($config['fields']);
        $rules = $formConfig->getRules();
        $data = [
            'name' => '',
            'email' => 'invalid',
            'message' => '',
        ];

        $messages = [];
        $ruleSets = [];

        foreach ($rules as $field => $fieldRules) {
            $ruleSets[$field] = $fieldRules['rules'];
            $messages[$field] = $fieldRules['message'];
        }

        $validator = new Validator($data, $ruleSets, $messages);

        $this->assertNotEmpty($validator->validate());
    }

    public function testValidationRulesAcceptValidData(): void
    {
        $config = RepliqForm::buildConfig('contact');
        $this->assertNotNull($config);

        $formConfig = new RepliqForm($config['fields']);
        $rules = $formConfig->getRules();
        $data = [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'message' => 'Bonjour',
        ];

        $messages = [];
        $ruleSets = [];

        foreach ($rules as $field => $fieldRules) {
            $ruleSets[$field] = $fieldRules['rules'];
            $messages[$field] = $fieldRules['message'];
        }

        $validator = new Validator($data, $ruleSets, $messages);

        $this->assertSame([], $validator->validate());
    }

    public function testHoneypotGuardRejectsFilledField(): void
    {
        $config = RepliqForm::buildConfig('contact');
        $this->assertNotNull($config);

        $field = RepliqForm::resolveHoneypotField($config['fields']);
        $this->assertSame('website', $field);

        $this->simulateRequest('POST', [$field => 'https://spam.example']);

        $guard = new HoneypotGuard(new \Uniform\Form([]), ['field' => $field]);

        $this->expectException(\Uniform\Exceptions\PerformerException::class);
        $guard->perform();
    }

    public function testHoneytimeGuardAcceptsExpiredTimestamp(): void
    {
        $config = RepliqForm::buildConfig('honeytime');
        $this->assertNotNull($config);

        $options = RepliqForm::resolveHoneytimeGuardOptions($config);
        $this->assertNotNull($options);

        $this->simulateRequest('POST', [
            $options['field'] => HoneytimeGuard::encrypt(
                $options['key'],
                (string) (time() - 20)
            ),
        ]);

        $guard = new HoneytimeGuard(new \Uniform\Form([]), $options);

        $guard->perform();
        $this->assertTrue(true);
    }

    public function testBuildErrorsSummaryReturnsEmptyWhenNoErrors(): void
    {
        $form = $this->runSubmitPipeline('contact', $this->postWithCsrf([
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'message' => 'Bonjour',
            'website' => '',
        ]));

        $this->assertSame([], RepliqForm::buildErrorsSummary($form, 'contact'));
    }

    public function testBuildErrorsSummaryListsFieldErrorsWithLabels(): void
    {
        $form = $this->runSubmitPipeline('contact', $this->postWithCsrf([
            'name' => 'Jane Doe',
            'email' => 'not-an-email',
            'message' => 'Bonjour',
            'website' => '',
        ]));

        $summary = RepliqForm::buildErrorsSummary($form, 'contact');

        $this->assertCount(1, $summary);
        $this->assertSame('email', $summary[0]['id']);
        $this->assertSame('Email', $summary[0]['label']);
        $this->assertNotEmpty($summary[0]['messages']);
    }

    public function testBuildErrorsSummarySkipsHoneypotFieldKey(): void
    {
        $config = RepliqForm::buildConfig('contact');
        $this->assertNotNull($config);

        $form = new \Uniform\Form([]);
        $honeypotField = RepliqForm::resolveHoneypotField($config['fields']);
        $this->assertNotNull($honeypotField);

        $reflection = new \ReflectionClass($form);
        $prop = $reflection->getProperty('errors');
        $prop->setAccessible(true);
        $prop->setValue($form, [
            'email' => ['Email invalide'],
            $honeypotField => ['Spam détecté'],
        ]);

        $summary = RepliqForm::buildErrorsSummary($form, 'contact');

        $this->assertCount(1, $summary);
        $this->assertSame('email', $summary[0]['id']);
    }

    public function testResolveErrorsSummarySettingUsesGlobalByDefault(): void
    {
        $setting = RepliqForm::resolveErrorsSummarySetting('contact');

        $this->assertFalse($setting['enabled']);
        $this->assertSame('Le formulaire contient des erreurs', $setting['title']);
    }

    public function testResolveErrorsSummarySettingHonorsSnippetOverride(): void
    {
        $setting = RepliqForm::resolveErrorsSummarySetting('contact', true);

        $this->assertTrue($setting['enabled']);
    }

    public function testErrorsSummarySnippetRendersAccessibleMarkup(): void
    {
        $form = $this->runSubmitPipeline('contact', $this->postWithCsrf([
            'name' => '',
            'email' => 'not-an-email',
            'message' => '',
            'website' => '',
        ]));

        ob_start();
        snippet('form-errors-summary', [
            'form' => $form,
            'formKey' => 'contact',
            'title' => 'Corrigez les champs suivants',
        ]);
        $html = (string) ob_get_clean();

        $this->assertStringContainsString('form-errors-summary', $html);
        $this->assertStringContainsString('role="alert"', $html);
        $this->assertStringContainsString('Corrigez les champs suivants', $html);
        $this->assertStringContainsString('href="#email"', $html);
        $this->assertStringContainsString('Email', $html);
    }

    public function testResolveHtmxSettingIsDisabledByDefault(): void
    {
        $setting = RepliqForm::resolveHtmxSetting('contact');

        $this->assertFalse($setting['enabled']);
        $this->assertSame('#repliq-form-contact', $setting['target']);
        $this->assertSame('outerHTML', $setting['swap']);
    }

    public function testResolveHtmxSettingHonorsSnippetOverride(): void
    {
        $setting = RepliqForm::resolveHtmxSetting('contact', true);

        $this->assertTrue($setting['enabled']);
    }

    public function testHtmxFormAttributesReturnsEmptyWhenDisabled(): void
    {
        $attrs = RepliqForm::htmxFormAttributes(
            RepliqForm::resolveHtmxSetting('contact'),
            'http://localhost/kirby-form-snippets/submit/contact'
        );

        $this->assertSame([], $attrs);
    }

    public function testHtmxFormAttributesReturnsHxAttributesWhenEnabled(): void
    {
        $setting = RepliqForm::resolveHtmxSetting('contact', true);
        $action = 'http://localhost/kirby-form-snippets/submit/contact';
        $attrs = RepliqForm::htmxFormAttributes($setting, $action);

        $this->assertSame($action, $attrs['hx-post']);
        $this->assertSame('#repliq-form-contact', $attrs['hx-target']);
        $this->assertSame('outerHTML', $attrs['hx-swap']);
    }

    public function testIsHtmxRequestDetectsHxRequestHeader(): void
    {
        $this->simulateRequest('GET', [], '/', ['HX-Request' => 'true']);

        $this->assertTrue(RepliqForm::isHtmxRequest());
    }
}
