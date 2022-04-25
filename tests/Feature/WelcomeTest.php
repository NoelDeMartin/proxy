<?php

test('it works', function () {
    $response = $this->get('/');

    $response->assertStatus(200);
    $response->assertSee('Welcome to Proxy');
});
