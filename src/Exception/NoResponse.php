<?php

declare(strict_types=1);

namespace Vural\OpenAPIFaker\Exception;

use Exception;
use function Safe\sprintf;

class NoResponse extends Exception
{
    protected string $method;
    protected string $path;
    protected ?string $statusCode = null;

    public static function forPathAndMethod(string $path, string $method) : self
    {
        $e         = new self(sprintf('OpenAPI spec does not have a response for %s %s', $method, $path));
        $e->path   = $path;
        $e->method = $method;

        return $e;
    }

    public static function forPathAndMethodAndStatusCode(string $path, string $method, string $statusCode) : self
    {
        $e             = new self(sprintf('OpenAPI spec does not have a response for status code %s at %s %s', $statusCode, $method, $path));
        $e->path       = $path;
        $e->method     = $method;
        $e->statusCode = $statusCode;

        return $e;
    }
}
