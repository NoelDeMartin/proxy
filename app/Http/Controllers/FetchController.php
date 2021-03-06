<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProxyRequest;
use Illuminate\Support\Facades\Http;

class FetchController extends Controller
{
    public function __invoke(ProxyRequest $request) {
        return Http::get($request->get('url'));
    }
}
