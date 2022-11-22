<?php

declare(strict_types=1);

namespace Vural\OpenAPIFaker\Exception;

use Exception;

use function sprintf;

final class NoExample extends Exception
{
    public static function forRequest(string $example): self
    {
        return new self(sprintf('OpenAPI spec does not have an example "%s" request', $example));
    }

    public static function forResponse(string $example): self
    {
        return new self(sprintf('OpenAPI spec does not have an example "%s" response', $example));
    }
}
