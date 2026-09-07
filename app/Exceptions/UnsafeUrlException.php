<?php

namespace App\Exceptions;

use Symfony\Component\HttpKernel\Exception\HttpException;

class UnsafeUrlException extends HttpException
{
    public static function malformed(): self
    {
        return new self(403, 'The url is malformed.');
    }

    public static function scheme(string $scheme): self
    {
        return new self(403, "The '{$scheme}' scheme is not allowed, only http and https urls can be fetched.");
    }

    public static function port(int $port): self
    {
        return new self(403, "Port {$port} is not allowed, only ports 80 and 443 can be fetched.");
    }

    public static function host(string $host): self
    {
        return new self(403, "The host '{$host}' is not a valid hostname or IP address.");
    }

    public static function privateAddress(string $host, string $ip): self
    {
        return new self(403, "The host '{$host}' resolves to '{$ip}', which is not a public address.");
    }
}
