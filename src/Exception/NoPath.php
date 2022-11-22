<?php

declare(strict_types=1);

namespace Vural\OpenAPIFaker\Exception;

use Exception;

use function sprintf;

final class NoPath extends Exception
{
    public static function forPathAndMethod(string $path, string $method): self
    {
        return new self(sprintf('OpenAPI spec does not have a path for %s %s', $method, $path));
    }
}
