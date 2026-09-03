<?php

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

it('is documented', function () {
    $response = $this->get('/fetch');

    $response->assertOk();
    $response->assertSee('Fetch endpoint');
});

it('fetches content', function () {
    // Arrange
    $url = fake()->url;
    $html = fake()->randomHtml;

    Http::fake(['*' => Http::response($html)]);

    // Act
    $response = $this->post('/fetch', compact('url'));

    // Assert
    Http::assertSent(fn (Request $request) => $request->url() === $url);

    expect($response->getContent())->toBe($html);
});

it('throttles requests when limit is exceeded', function () {
    Http::fake(['*' => Http::response('ok')]);

    for ($i = 0; $i < 100; $i++) {
        $this->post('/fetch', ['url' => 'https://example.com'])->assertOk();
    }

    $this->post('/fetch', ['url' => 'https://example.com'])->assertStatus(429);
});

it('includes cors headers on fetch endpoint', function () {
    Http::fake();
    config()->set('cors.allowed_origins', ['*']);

    $response = $this->post('/fetch', ['url' => 'https://example.com'], [
        'Origin' => 'https://example.com',
    ]);

    $response->assertHeader('Access-Control-Allow-Origin', '*');
});

it('does not allow cors for unknown origins', function () {
    Http::fake();
    config()->set('cors.allowed_origins', ['https://allowed1.com', 'https://allowed2.com']);

    $response = $this->options('/fetch', [], [
        'Origin' => 'https://unknown.com',
        'Access-Control-Request-Method' => 'POST',
    ]);

    $response->assertHeaderMissing('Access-Control-Allow-Origin');
    Http::assertNothingSent();
});

it('allows cors preflight request for allowed origins', function () {
    Http::fake();
    config()->set('cors.allowed_origins', ['https://allowed1.com', 'https://allowed2.com']);

    $response = $this->options('/fetch', [], [
        'Origin' => 'https://allowed1.com',
        'Access-Control-Request-Method' => 'POST',
    ]);

    $response->assertNoContent();
    $response->assertHeader('Access-Control-Allow-Origin', 'https://allowed1.com');
    $response->assertHeader('Access-Control-Allow-Methods', 'POST');
    Http::assertNothingSent();
});
