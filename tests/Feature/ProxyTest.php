<?php

use function Pest\Faker\faker;
use function Pest\Laravel\post;

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

test('it works', function () {
    // Arrange
    $url = faker()->url;
    $html = faker()->randomHtml;

    Http::fake(['*' => Http::response($html)]);

    // Act
    $response = post('/', compact('url'));

    // Assert
    Http::assertSent(fn (Request $request) => $request->url() === $url);

    $this->assertEquals($html, $response->getContent());
});