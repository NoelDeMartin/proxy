<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProxyRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class FetchController extends Controller
{
    public function __invoke(ProxyRequest $request): Response
    {
        return Http::get($request->string('url'));
    }
}
