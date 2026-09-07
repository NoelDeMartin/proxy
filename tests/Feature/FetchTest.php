<?php

use App\Support\DnsResolver;
use App\Support\Fetcher;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    $this->dns = ['*' => ['93.184.216.34']];

    $this->mock(DnsResolver::class)
        ->shouldReceive('resolve')
        ->andReturnUsing(fn (string $host) => $this->dns[$host] ?? $this->dns['*']);
});

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

it('passes through the upstream status and content type', function () {
    Http::fake(['*' => Http::response('{"error":"not found"}', 404, ['Content-Type' => 'application/json'])]);

    $response = $this->post('/fetch', ['url' => 'https://example.com/missing']);

    $response->assertNotFound();
    $response->assertHeader('Content-Type', 'application/json');
    expect($response->getContent())->toBe('{"error":"not found"}');
});

it('prevents browsers from rendering fetched content as a page', function () {
    Http::fake(['*' => Http::response('<script>alert(1)</script>', 200, ['Content-Type' => 'text/html'])]);

    $response = $this->post('/fetch', ['url' => 'https://example.com']);

    $response->assertOk();
    $response->assertHeader('Content-Disposition', 'attachment');
    $response->assertHeader('Content-Security-Policy', 'sandbox');
    $response->assertHeader('X-Content-Type-Options', 'nosniff');
});

it('pins requests to the resolved addresses without following redirects', function () {
    $this->dns['example.com'] = ['93.184.216.34', '2606:2800:21f:cb07:6820:80da:af6b:8b2c'];
    $options = null;

    Http::fake(function (Request $request, array $requestOptions) use (&$options) {
        $options = $requestOptions;

        return Http::response('ok');
    });

    $this->post('/fetch', ['url' => 'https://example.com/recipe'])->assertOk();

    expect($options)
        ->toHaveKey('allow_redirects', false)
        ->toHaveKey('timeout', Fetcher::TIMEOUT_SECONDS)
        ->toHaveKey('connect_timeout', Fetcher::CONNECT_TIMEOUT_SECONDS)
        ->and($options['curl'][CURLOPT_RESOLVE])
        ->toBe(['example.com:443:93.184.216.34,[2606:2800:21f:cb07:6820:80da:af6b:8b2c]']);
});

it('follows redirects to public hosts', function () {
    Http::fake([
        'https://example.com/*' => Http::response(null, 301, ['Location' => '//www.example.com/moved']),
        'www.example.com/moved' => Http::response(null, 302, ['Location' => 'final?ref=1']),
        'www.example.com/final?ref=1' => Http::response('final page'),
    ]);

    $response = $this->post('/fetch', ['url' => 'https://example.com/recipe']);

    $response->assertOk();
    expect($response->getContent())->toBe('final page');
    Http::assertSentCount(3);
});

it('rejects redirects to private addresses', function () {
    Http::fake(['*' => Http::response(null, 302, ['Location' => 'http://169.254.169.254/hetzner/v1/metadata'])]);

    $this->post('/fetch', ['url' => 'https://example.com'])->assertForbidden();

    Http::assertSentCount(1);
    Http::assertNotSent(fn (Request $request) => str_contains($request->url(), '169.254.169.254'));
});

it('rejects redirects to unsupported schemes', function () {
    Http::fake(['*' => Http::response(null, 302, ['Location' => 'file:///etc/passwd'])]);

    $this->post('/fetch', ['url' => 'https://example.com'])->assertForbidden();

    Http::assertSentCount(1);
});

it('rejects redirects with malformed location headers', function () {
    Http::fake(['*' => Http::response(null, 302, ['Location' => 'http:///bad'])]);

    $this->post('/fetch', ['url' => 'https://example.com'])->assertStatus(502);

    Http::assertSentCount(1);
});

it('limits the number of redirects', function () {
    Http::fake(['*' => Http::response(null, 302, ['Location' => 'https://example.com/loop'])]);

    $this->post('/fetch', ['url' => 'https://example.com'])->assertStatus(502);

    Http::assertSentCount(Fetcher::MAX_REDIRECTS + 1);
});

it('rejects malformed urls and unsupported schemes', function (string $url) {
    Http::fake();

    $this->post('/fetch', compact('url'))->assertForbidden();

    Http::assertNothingSent();
})->with([
    'ftp://example.com/file',
    'file:///etc/passwd',
    'gopher://example.com',
    'example.com/no-scheme',
    'not a url',
    'http://',
]);

it('rejects urls with non-standard ports', function (string $url) {
    Http::fake();

    $this->post('/fetch', compact('url'))->assertForbidden();

    Http::assertNothingSent();
})->with([
    'https://example.com:8443/',
    'http://example.com:9000/',
    'http://example.com:3000/',
]);

it('rejects private, loopback, link-local and reserved addresses', function (string $url) {
    Http::fake();

    $this->post('/fetch', compact('url'))->assertForbidden();

    Http::assertNothingSent();
})->with([
    'http://127.0.0.1/',
    'http://127.1.2.3:80/',
    'http://0.0.0.0/',
    'http://10.0.0.1/',
    'http://172.17.0.2/',
    'http://192.168.1.1/',
    'http://169.254.169.254/hetzner/v1/metadata',
    'http://100.64.0.1/',
    'http://[::1]/',
    'http://[::]/',
    'http://[fe80::1]/',
    'http://[fd00::1]/',
    'http://[::ffff:127.0.0.1]/',
]);

it('rejects hostnames that resolve to private addresses', function (array $ips) {
    $this->dns['internal.example.com'] = $ips;
    Http::fake();

    $this->post('/fetch', ['url' => 'https://internal.example.com'])->assertForbidden();

    Http::assertNothingSent();
})->with([
    'ipv4' => [['172.18.0.5']],
    'ipv6' => [['fd00::5']],
    'public and private' => [['93.184.216.34', '10.0.0.5']],
    'public ipv4 and loopback ipv6' => [['93.184.216.34', '::1']],
]);

it('rejects hostnames that cannot be resolved', function () {
    $this->dns['missing.example.com'] = [];
    Http::fake();

    $this->post('/fetch', ['url' => 'https://missing.example.com'])->assertStatus(502);

    Http::assertNothingSent();
});

it('rejects responses that are too large', function () {
    Http::fake(['*' => Http::response(str_repeat('a', Fetcher::MAX_RESPONSE_BYTES + 1))]);

    $this->post('/fetch', ['url' => 'https://example.com'])->assertStatus(502);
});

it('fails gracefully when the upstream cannot be reached', function () {
    Http::fake(['*' => Http::failedConnection()]);

    $this->post('/fetch', ['url' => 'https://example.com'])->assertStatus(502);
});

it('throttles requests when limit is exceeded', function () {
    Http::fake(['*' => Http::response('ok')]);
    config()->set('rate_limiting.requests', 5);

    for ($i = 0; $i < 5; $i++) {
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
