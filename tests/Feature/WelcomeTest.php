<?php

it('works', function () {
    $response = $this->get('/');

    $response->assertOk();
    $response->assertSee('This website is a proxy that can be used to read content from the web');
});
