<?php

namespace App\Support;

use App\Exceptions\UnsafeUrlException;
use App\Exceptions\UpstreamException;
use GuzzleHttp\Psr7\Exception\MalformedUriException;
use GuzzleHttp\Psr7\Uri;
use Psr\Http\Message\UriInterface;

/**
 * A url that has been verified to point to a host on the public internet.
 *
 * Instances can only be created through `resolve()`, so any code that receives
 * a PublicUrl can trust that the scheme, port and every IP address behind the
 * hostname have been checked. The resolved addresses are kept so that the HTTP
 * client can pin the connection to them instead of resolving the hostname a
 * second time (which would be vulnerable to DNS rebinding).
 */
final readonly class PublicUrl
{
    public const ALLOWED_SCHEMES = ['http', 'https'];

    public const ALLOWED_PORTS = [80, 443];

    /**
     * @param  list<string>  $ips
     */
    private function __construct(public UriInterface $uri, public array $ips)
    {
        //
    }

    /**
     * @throws UnsafeUrlException
     * @throws UpstreamException
     */
    public static function resolve(string|UriInterface $url, DnsResolver $dns): self
    {
        try {
            $uri = $url instanceof UriInterface ? $url : new Uri($url);
        } catch (MalformedUriException) {
            throw UnsafeUrlException::malformed();
        }

        // Credentials and fragments are never needed to fetch a page, and stripping
        // them removes any ambiguity about which part of the url is the host.
        $uri = $uri->withUserInfo('')->withFragment('');

        $scheme = $uri->getScheme();
        $host = $uri->getHost();

        if ($scheme === '' || $host === '') {
            throw UnsafeUrlException::malformed();
        }

        if (! in_array($scheme, self::ALLOWED_SCHEMES, true)) {
            throw UnsafeUrlException::scheme($scheme);
        }

        $port = $uri->getPort() ?? ($scheme === 'https' ? 443 : 80);

        if (! in_array($port, self::ALLOWED_PORTS, true)) {
            throw UnsafeUrlException::port($port);
        }

        foreach ($ips = self::resolveHost($host, $dns) as $ip) {
            if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_GLOBAL_RANGE) === false) {
                throw UnsafeUrlException::privateAddress($host, $ip);
            }
        }

        return new self($uri, $ips);
    }

    public function port(): int
    {
        return $this->uri->getPort() ?? ($this->uri->getScheme() === 'https' ? 443 : 80);
    }

    /**
     * @return list<string>
     */
    private static function resolveHost(string $host, DnsResolver $dns): array
    {
        $literal = trim($host, '[]');

        if (filter_var($literal, FILTER_VALIDATE_IP) !== false) {
            return [$literal];
        }

        if (filter_var($host, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME) === false) {
            throw UnsafeUrlException::host($host);
        }

        $ips = $dns->resolve($host);

        if ($ips === []) {
            throw UpstreamException::unresolvable($host);
        }

        return $ips;
    }
}
