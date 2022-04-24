<?php

namespace Tests\Feature;

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ProxyTest extends TestCase
{
    public function test_it_works()
    {
        // Arrange
        $url = 'https://example.com';
        $html = '<h1>Example page title</h1><p>Example page content</p>';

        Http::fake(['*' => Http::response($html)]);

        // Act
        $response = $this->post('/', compact('url'));

        // Assert
        Http::assertSent(fn (Request $request) => $request->url() === $url);

        $this->assertEquals($html, $response->getContent());
    }
}
