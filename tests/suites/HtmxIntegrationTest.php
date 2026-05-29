<?php

declare(strict_types=1);

namespace Repliq\Tests;

use Kirby\Http\Response;
use repliq\RepliqForm;

final class HtmxIntegrationTest extends TestCase
{
    public function testHtmxSubmitReturnsHtmlFragmentOnValidationError(): void
    {
        $route = (string) option('baptiste.kirby-form-snippets.submit.route');
        $this->simulateRequest(
            'POST',
            $this->postWithCsrf([
                'name' => '',
                'email' => 'not-an-email',
                'message' => '',
                'website' => '',
            ]),
            '/' . $route . '/contact',
            ['HX-Request' => 'true']
        );

        $response = RepliqForm::handleSubmit('contact');

        $this->assertInstanceOf(Response::class, $response);
        $output = $response->body();
        $this->assertStringContainsString('repliq-form-container', $output);
        $this->assertStringContainsString('notif error', $output);
        $this->assertSame(422, $response->code());
    }

    public function testHtmxSubmitShowsSubmitErrorOnHoneypotFailure(): void
    {
        $route = (string) option('baptiste.kirby-form-snippets.submit.route');
        $this->simulateRequest(
            'POST',
            $this->postWithCsrf([
                'name' => 'Jane Doe',
                'email' => 'jane@example.com',
                'message' => 'Spam',
                'website' => 'https://spam.example',
            ]),
            '/' . $route . '/contact',
            ['HX-Request' => 'true']
        );

        $response = RepliqForm::handleSubmit('contact');

        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame(422, $response->code());
        $output = $response->body();
        $this->assertStringContainsString('form-submit-error', $output);
        $this->assertStringContainsString(RepliqForm::spamGuardMessage(), $output);
        $this->assertStringNotContainsString('patienter', $output);
    }

    public function testHtmxSubmitReturnsSuccessMessageOnValidPost(): void
    {
        $route = (string) option('baptiste.kirby-form-snippets.submit.route');
        $this->simulateRequest(
            'POST',
            $this->postWithCsrf([
                'name' => 'Jane Doe',
                'email' => 'jane@example.com',
                'message' => 'Bonjour via HTMX',
                'website' => '',
            ]),
            '/' . $route . '/contact',
            ['HX-Request' => 'true']
        );

        $response = RepliqForm::handleSubmit('contact');

        $this->assertInstanceOf(Response::class, $response);
        $output = $response->body();
        $this->assertStringContainsString('form-success', $output);
        $this->assertStringNotContainsString('method="post"', $output);
    }

    public function testHtmxSubmitUsesRememberedSuccessMessage(): void
    {
        $customMessage = 'Message personnalisé après envoi HTMX';
        RepliqForm::rememberFormPresentation('contact', [
            'successMessage' => $customMessage,
        ]);

        $route = (string) option('baptiste.kirby-form-snippets.submit.route');
        $this->simulateRequest(
            'POST',
            $this->postWithCsrf([
                'name' => 'Jane Doe',
                'email' => 'jane@example.com',
                'message' => 'Bonjour via HTMX',
                'website' => '',
            ]),
            '/' . $route . '/contact',
            ['HX-Request' => 'true']
        );

        $response = RepliqForm::handleSubmit('contact');

        $this->assertInstanceOf(Response::class, $response);
        $this->assertStringContainsString($customMessage, $response->body());
        $this->assertStringNotContainsString(
            'Merci, votre message a bien été envoyé.',
            $response->body()
        );
    }

    public function testHtmxSubmitDoesNotFlashSuccessForNextPageLoad(): void
    {
        $route = (string) option('baptiste.kirby-form-snippets.submit.route');
        $this->simulateRequest(
            'POST',
            $this->postWithCsrf([
                'name' => 'Jane Doe',
                'email' => 'jane@example.com',
                'message' => 'Bonjour via HTMX',
                'website' => '',
            ]),
            '/' . $route . '/contact',
            ['HX-Request' => 'true']
        );

        RepliqForm::handleSubmit('contact');

        $this->simulateRequest('GET', [], '/');

        $formData = repliq_form('contact');
        $this->assertNotNull($formData);
        $this->assertFalse($formData['form']->success());
    }

    public function testFormPageSnippetRendersHtmxAttributesWhenEnabled(): void
    {
        $this->resetHtmxScriptState();

        $formData = repliq_form('contact');

        $this->assertNotNull($formData);

        $html = snippet('form-page', [
            'formKey' => 'contact',
            'formData' => $formData,
            'htmx' => true,
        ], true);

        $this->assertIsString($html);
        $this->assertStringContainsString('hx-post=', $html);
        $this->assertStringContainsString('hx-target="#repliq-form-contact"', $html);
        $this->assertStringContainsString('data-repliq-form', $html);
        $this->assertStringContainsString('htmx.org', $html);
    }
}
