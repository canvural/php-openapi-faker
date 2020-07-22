<?php

declare(strict_types=1);

namespace Vural\OpenAPIFaker\Exception;

use Exception;

use function Safe\sprintf;

class NoRequest extends Exception
{
    protected string $method;
    protected string $path;
    protected string $contentType;

    public static function forPathAndMethod(string $path, string $method): self
    {
        $e         = new self(sprintf('OpenAPI spec does not have a response for %s %s', $method, $path));
        $e->path   = $path;
        $e->method = $method;

        return $e;
    }

    public static function forPathAndMethodAndContentType(string $path, string $method, string $contentType): self
    {
        $e              = new self(sprintf('OpenAPI spec does not have a response for %s %s', $method, $path));
        $e->path        = $path;
        $e->method      = $method;
        $e->contentType = $contentType;

        return $e;
    }
}
