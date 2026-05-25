<?php

declare(strict_types=1);

namespace Repliq\Tests;

use Jevets\Kirby\Exceptions\TokenMismatchException;
use Kirby\Email\Email;
use repliq\RepliqForm;
use Uniform\Guards\HoneytimeGuard;

final class SubmissionIntegrationTest extends TestCase
{
    public function testSubmitRouteIsRegisteredForPost(): void
    {
        $route = (string) option('baptist.kirby-form-snippets.submit.route');
        $match = kirby()->router()->find($route . '/contact', 'POST');

        $this->assertNotNull($match);
    }

    public function testContactSubmitSucceedsWithValidPostAndCsrf(): void
    {
        $form = $this->runSubmitPipeline('contact', $this->postWithCsrf([
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'message' => 'Bonjour depuis les tests',
            'website' => '',
        ]));

        $this->assertTrue($form->success());
        $this->assertSame([], $form->errors());
        $this->assertCount(1, Email::$emails);
        $this->assertSame(['contact@example.com' => null], Email::$emails[0]->to());
    }

    public function testContactSubmitRejectsMissingCsrfToken(): void
    {
        $this->expectException(TokenMismatchException::class);

        $this->runSubmitPipeline('contact', [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'message' => 'Bonjour',
            'website' => '',
        ]);
    }

    public function testContactSubmitRejectsInvalidCsrfToken(): void
    {
        $this->expectException(TokenMismatchException::class);

        csrf();

        $this->runSubmitPipeline('contact', [
            'csrf_token' => 'invalid-token',
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'message' => 'Bonjour',
            'website' => '',
        ]);
    }

    public function testContactSubmitRejectsInvalidEmail(): void
    {
        $form = $this->runSubmitPipeline('contact', $this->postWithCsrf([
            'name' => 'Jane Doe',
            'email' => 'not-an-email',
            'message' => 'Bonjour',
            'website' => '',
        ]));

        $this->assertFalse($form->success());
        $this->assertNotEmpty($form->errors('email'));
        $this->assertSame([], Email::$emails);
    }

    public function testContactSubmitRejectsFilledHoneypot(): void
    {
        $form = $this->runSubmitPipeline('contact', $this->postWithCsrf([
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'message' => 'Bonjour',
            'website' => 'https://spam.example',
        ]));

        $this->assertFalse($form->success());
        $this->assertSame([], Email::$emails);
    }

    public function testHoneytimeSubmitRejectsImmediateSubmission(): void
    {
        $config = RepliqForm::buildConfig('honeytime');
        $this->assertNotNull($config);

        $options = RepliqForm::resolveHoneytimeGuardOptions($config);
        $this->assertNotNull($options);

        $form = $this->runSubmitPipeline('honeytime', $this->postWithCsrf(array_merge([
            'email' => 'jane@example.com',
        ], [
            $options['field'] => HoneytimeGuard::encrypt($options['key'], (string) time()),
        ])));

        $this->assertFalse($form->success());
        $this->assertSame([], Email::$emails);
    }

    public function testHoneytimeSubmitSucceedsAfterDelay(): void
    {
        $config = RepliqForm::buildConfig('honeytime');
        $this->assertNotNull($config);

        $form = $this->runSubmitPipeline('honeytime', $this->postWithCsrf(array_merge([
            'email' => 'jane@example.com',
        ], $this->honeytimeFieldPayload($config))));

        $this->assertTrue($form->success());
        $this->assertCount(1, Email::$emails);
    }

    public function testCsrfTokenFromRouteCanBeUsedForSubmit(): void
    {
        $csrfRoute = option('baptiste.kirby-form-snippets.csrf.route');
        $response = kirby()->router()->call($csrfRoute, 'GET');
        $payload = json_decode($response->body(), true);

        $this->assertIsArray($payload);
        $this->assertNotEmpty($payload['token']);

        $form = $this->runSubmitPipeline('contact', [
            'csrf_token' => $payload['token'],
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'message' => 'Soumis via token route',
            'website' => '',
        ]);

        $this->assertTrue($form->success());
    }
}
