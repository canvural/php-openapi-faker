<?php

declare(strict_types=1);

namespace Vural\OpenAPIFaker\Exception;

use Exception;
use function Safe\sprintf;

class NoPath extends Exception
{
    protected string $method;
    protected string $path;

    public static function forPathAndMethod(string $path, string $method) : self
    {
        $e         = new self(sprintf('OpenAPI spec does not have a path for %s %s', $method, $path));
        $e->path   = $path;
        $e->method = $method;

        return $e;
    }
}
