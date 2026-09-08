<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProxyRequest;
use App\Support\Fetcher;
use App\Support\SecurityHeaders;
use Illuminate\Http\Response;

class FetchController extends Controller
{
    public function __invoke(ProxyRequest $request, Fetcher $fetcher): Response
    {
        $upstream = $fetcher->fetch($request->string('url')->toString());

        return SecurityHeaders::untrusted($upstream->body(), $upstream->status(), [
            'Content-Type' => $upstream->header('Content-Type') ?: 'text/html',
            'Content-Disposition' => 'attachment',
        ]);
    }
}
