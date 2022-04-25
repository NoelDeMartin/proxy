<?php

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

test('it works', function () {
    // Arrange
    $url = 'https://example.com';
    $html = '<h1>Example page title</h1><p>Example page content</p>';

    Http::fake(['*' => Http::response($html)]);

    // Act
    $response = $this->post('/', compact('url'));

    // Assert
    Http::assertSent(fn (Request $request) => $request->url() === $url);

    $this->assertEquals($html, $response->getContent());
});
