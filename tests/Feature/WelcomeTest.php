<?php

test('it works', function () {
    $response = $this->get('/');

    $response->assertStatus(200);
    $response->assertSee('This website is a proxy that can be used to read content from the web');
});
