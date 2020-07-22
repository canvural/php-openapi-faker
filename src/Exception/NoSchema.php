<?php

declare(strict_types=1);

namespace Vural\OpenAPIFaker\Exception;

use Exception;

use function Safe\sprintf;

class NoSchema extends Exception
{
    public string $name;

    public static function forZeroComponents(): self
    {
        return new self('OpenAPI spec does not have any components.');
    }

    public static function forComponentName(string $name): self
    {
        return new self(sprintf('OpenAPI spec does not have any component schema named %s.', $name));
    }
}
