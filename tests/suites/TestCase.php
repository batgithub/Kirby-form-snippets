<?php

declare(strict_types=1);

namespace Repliq\Tests;

use Jevets\Kirby\Flash;
use Jevets\Kirby\Form as BaseForm;
use Kirby\Cms\App as Kirby;
use Kirby\Email\Email;
use Kirby\Http\Request;
use PHPUnit\Framework\TestCase as PHPUnitTestCase;
use repliq\RepliqForm;
use Uniform\Form;
use Uniform\Guards\HoneytimeGuard;

abstract class TestCase extends PHPUnitTestCase
{
    protected static ?Kirby $kirby = null;

    public static function setUpBeforeClass(): void
    {
        if (self::$kirby === null) {
            /** @var Kirby $kirby */
            $kirby = require dirname(__DIR__) . '/bootstrap.php';
            self::$kirby = $kirby;
        }
    }

    protected function setUp(): void
    {
        parent::setUp();

        $_POST = [];
        $_GET = [];
        $_SERVER['REQUEST_METHOD'] = 'GET';

        Email::$debug = true;
        Email::$emails = [];

        $flash = Flash::getInstance();
        $flash->set(BaseForm::FLASH_KEY_DATA, null);
        $flash->set(BaseForm::FLASH_KEY_ERRORS, null);
        $flash->set(Form::FLASH_KEY_SUCCESS, null);

        $this->simulateRequest('GET');
    }

    /**
     * @param array<string, mixed> $body
     */
    protected function simulateRequest(
        string $method = 'GET',
        array $body = [],
        string $path = '/'
    ): void {
        $_SERVER['REQUEST_METHOD'] = $method;

        $kirby = kirby();
        $reflection = new \ReflectionClass($kirby);
        $prop = $reflection->getProperty('request');
        $prop->setAccessible(true);
        $prop->setValue($kirby, new Request([
            'method' => $method,
            'body' => $body,
            'url' => 'http://localhost' . $path,
            'cli' => true,
        ]));
    }

    /**
     * @param array<string, mixed> $fields
     * @return array<string, mixed>
     */
    protected function postWithCsrf(array $fields = []): array
    {
        return array_merge($fields, [
            'csrf_token' => csrf(),
        ]);
    }

    /**
     * Exécute le même pipeline que RepliqForm::handleSubmit(), sans redirection HTTP.
     *
     * @param array<string, mixed> $fields
     */
    protected function runSubmitPipeline(string $formKey, array $fields = []): Form
    {
        $route = (string) option('baptiste.kirby-form-snippets.submit.route');
        $this->simulateRequest('POST', $fields, '/' . $route . '/' . $formKey);

        $config = RepliqForm::buildConfig($formKey, [], 'submit');

        if ($config === null) {
            throw new \RuntimeException('Form config not found: ' . $formKey);
        }

        $formConfig = new RepliqForm($config['fields']);
        $form = new Form($formConfig->getRules());
        $pipeline = RepliqForm::applySpamGuards($form, $config);

        $email = is_array($config['email'] ?? null) ? $config['email'] : [];
        $emailConfig = RepliqForm::resolveEmailConfig($email, $formKey);
        $emailConfig['data'] = array_merge(
            $formConfig->buildEmailData($form, $config, $formKey),
            is_array($emailConfig['data'] ?? null) ? $emailConfig['data'] : []
        );

        unset($emailConfig['theme'], $emailConfig['themeFrom'], $emailConfig['templateData']);

        $pipeline
            ->withoutRedirect()
            ->withoutFlashing()
            ->emailAction($emailConfig)
            ->done();

        return $pipeline;
    }

    /**
     * @param array<string, mixed> $config
     * @return array<string, mixed>
     */
    protected function honeytimeFieldPayload(array $config): array
    {
        $options = RepliqForm::resolveHoneytimeGuardOptions($config);

        if ($options === null) {
            return [];
        }

        return [
            $options['field'] => HoneytimeGuard::encrypt(
                $options['key'],
                (string) (time() - 20)
            ),
        ];
    }
}
