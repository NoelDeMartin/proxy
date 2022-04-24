<?php

namespace Tests\Feature;

use Tests\TestCase;

class WelcomeTest extends TestCase
{
    public function test_it_works()
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('Welcome to Proxy');
    }
}
