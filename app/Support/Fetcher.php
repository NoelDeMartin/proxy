<?php

namespace App\Support;

use App\Exceptions\UpstreamException;
use GuzzleHttp\Psr7\Exception\MalformedUriException;
use GuzzleHttp\Psr7\Uri;
use GuzzleHttp\Psr7\UriResolver;
use Illuminate\Http\Client\HttpClientException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class Fetcher
{
    public const MAX_REDIRECTS = 5;

    public const MAX_RESPONSE_BYTES = 3 * 1024 * 1024;

    public const TIMEOUT_SECONDS = 10;

    public const CONNECT_TIMEOUT_SECONDS = 5;

    public function __construct(private DnsResolver $dns)
    {
        //
    }

    public function fetch(string $url): Response
    {
        $target = PublicUrl::resolve($url, $this->dns);

        // Redirects are followed manually so that every hop goes through the same
        // validation (and IP pinning) as the original url.
        for ($redirects = 0; $redirects <= self::MAX_REDIRECTS; $redirects++) {
            $response = $this->request($target);
            $location = $response->header('Location');

            if (! $response->redirect() || $location === '') {
                return $response;
            }

            try {
                $target = PublicUrl::resolve(UriResolver::resolve($target->uri, new Uri($location)), $this->dns);
            } catch (MalformedUriException $exception) {
                throw UpstreamException::unreachable($exception);
            }
        }

        throw UpstreamException::tooManyRedirects(self::MAX_REDIRECTS);
    }

    private function request(PublicUrl $target): Response
    {
        $tooLarge = false;

        try {
            $response = Http::withoutRedirecting()
                ->connectTimeout(self::CONNECT_TIMEOUT_SECONDS)
                ->timeout(self::TIMEOUT_SECONDS)
                ->withUserAgent($this->userAgent())
                ->withOptions([
                    'curl' => [CURLOPT_RESOLVE => [$this->pinnedAddresses($target)]],
                    'progress' => function (int $downloadTotal, int $downloaded) use (&$tooLarge): void {
                        if (max($downloadTotal, $downloaded) > self::MAX_RESPONSE_BYTES) {
                            $tooLarge = true;

                            // Throwing from the progress callback aborts the transfer.
                            throw UpstreamException::responseTooLarge(self::MAX_RESPONSE_BYTES);
                        }
                    },
                ])
                ->get((string) $target->uri);
        } catch (HttpClientException $exception) {
            throw $tooLarge
                ? UpstreamException::responseTooLarge(self::MAX_RESPONSE_BYTES)
                : UpstreamException::unreachable($exception);
        }

        // Handlers that don't report progress (such as fakes) deliver the whole body at once.
        if (strlen($response->body()) > self::MAX_RESPONSE_BYTES) {
            throw UpstreamException::responseTooLarge(self::MAX_RESPONSE_BYTES);
        }

        return $response;
    }

    /**
     * Build a cURL --resolve entry so the connection goes to the addresses that
     * were validated, instead of resolving the hostname again.
     */
    private function pinnedAddresses(PublicUrl $target): string
    {
        $ips = array_map(
            fn (string $ip) => str_contains($ip, ':') ? "[{$ip}]" : $ip,
            $target->ips,
        );

        return $target->uri->getHost().':'.$target->port().':'.implode(',', $ips);
    }

    private function userAgent(): string
    {
        $name = config()->string('app.name');
        $url = config()->string('app.url');

        return "{$name} (+{$url})";
    }
}
