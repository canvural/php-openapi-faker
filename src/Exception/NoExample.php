<?php

declare(strict_types=1);

namespace Vural\OpenAPIFaker\Exception;

use Exception;

use function Safe\sprintf;

class NoExample extends Exception
{
    protected string $example;

    public static function forRequest(string $example): self
    {
        $e          = new self(sprintf('OpenAPI spec does not have a example "%s" request', $example));
        $e->example = $example;

        return $e;
    }

    public static function forResponse(string $example): self
    {
        $e          = new self(sprintf('OpenAPI spec does not have a example "%s" response', $example));
        $e->example = $example;

        return $e;
    }
}
