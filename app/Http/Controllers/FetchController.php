<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProxyRequest;
use App\Support\Fetcher;
use Illuminate\Http\Response;

class FetchController extends Controller
{
    public function __invoke(ProxyRequest $request, Fetcher $fetcher): Response
    {
        $upstream = $fetcher->fetch($request->string('url')->toString());

        return response($upstream->body(), $upstream->status())->withHeaders([
            'Content-Type' => $upstream->header('Content-Type') ?: 'text/html',
            'Content-Disposition' => 'attachment',
            'Content-Security-Policy' => 'sandbox',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
