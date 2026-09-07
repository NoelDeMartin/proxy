<?php

namespace App\Exceptions;

use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

class UpstreamException extends HttpException
{
    public static function unresolvable(string $host): self
    {
        return new self(502, "The host '{$host}' could not be resolved.");
    }

    public static function unreachable(?Throwable $previous = null): self
    {
        return new self(502, 'The url could not be fetched.', $previous);
    }

    public static function tooManyRedirects(int $limit): self
    {
        return new self(502, "The url redirected more than {$limit} times.");
    }

    public static function responseTooLarge(int $limit): self
    {
        return new self(502, "The response is larger than the {$limit} bytes limit.");
    }
}
