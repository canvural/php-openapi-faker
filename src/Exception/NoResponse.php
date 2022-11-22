<?php

declare(strict_types=1);

namespace Vural\OpenAPIFaker\Exception;

use Exception;

use function sprintf;

final class NoResponse extends Exception
{
    public static function forPathAndMethod(string $path, string $method): self
    {
        return new self(sprintf('OpenAPI spec does not have a response for %s %s', $method, $path));
    }

    public static function forPathAndMethodAndStatusCode(string $path, string $method, string $statusCode): self
    {
        return new self(sprintf('OpenAPI spec does not have a response for status code %s at %s %s', $statusCode, $method, $path));
    }
}
