<?php

declare(strict_types=1);

namespace Repliq\Tests;

use Kirby\Http\Response;
use repliq\RepliqFilterState;
use repliq\RepliqForm;

final class PluginIntegrationTest extends TestCase
{
    public function testPluginIsRegistered(): void
    {
        $plugin = kirby()->plugin('baptiste/kirby-form-snippets');

        $this->assertNotNull($plugin);
        $snippets = $plugin->extends()['snippets'] ?? [];
        $this->assertArrayHasKey('form-page', $snippets);
        $this->assertArrayHasKey('form-errors-summary', $snippets);
    }

    public function testCsrfRouteReturnsToken(): void
    {
        $response = kirby()->router()->call(
            option('baptiste.kirby-form-snippets.csrf.route'),
            'GET'
        );

        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame(200, $response->code());

        $payload = json_decode($response->body(), true);
        $this->assertIsArray($payload);
        $this->assertNotEmpty($payload['token']);
    }

    public function testHoneytimeRouteReturnsEncryptedValue(): void
    {
        $response = kirby()->router()->call(
            option('baptiste.kirby-form-snippets.honeytime.route'),
            'GET'
        );

        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame(200, $response->code());

        $payload = json_decode($response->body(), true);
        $this->assertIsArray($payload);
        $this->assertNotEmpty($payload['value']);
    }

    public function testFilterStateReadsQueryParameters(): void
    {
        $_GET['q'] = 'kirby';

        $state = new RepliqFilterState();

        $this->assertSame('kirby', $state->old('q'));
        $this->assertSame([], $state->error('q'));
        $this->assertFalse($state->success());
    }

    public function testFormConfigHookCanAdjustConfig(): void
    {
        kirby()->extend([
            'hooks' => [
                'repliq.form.config' => function (array $config, string $formKey, string $context): array {
                    if ($formKey !== 'contact') {
                        return $config;
                    }

                    $config['title'] = 'Contact modifié';

                    return $config;
                },
            ],
        ]);

        $config = RepliqForm::buildConfig('contact');

        $this->assertNotNull($config);
        $this->assertSame('Contact modifié', $config['title']);
    }
}
