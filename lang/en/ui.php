<?php

$appName = config('app.name');

return [
    'title' => $appName,
    'welcome' => [
        'title' => $appName,
        'description' => "This website is a proxy that can be used to read content from the web without running into <a target=\"_blank\" href=\"https://en.wikipedia.org/wiki/Cross-origin_resource_sharing\">CORS issues</a>. You can use it by calling the <a href=\"/fetch\">/fetch</a> endpoint.",
        'restricted' => 'It is, however, restricted to the following origins:',
        'open_source' => "Want to host this in your own server? You can, it's open source!",
        'github' => 'Get the source in GitHub',
    ],
    'fetch' => [
        'title' => 'Fetch endpoint',
        'description' => 'Make <code>POST</code> requests to this endpoint in order to read contents from a url.',
        'example' => 'For example:',
        'go_home' => 'go home',
    ],
];
