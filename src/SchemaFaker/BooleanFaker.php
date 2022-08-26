<?php

declare(strict_types=1);

namespace Vural\OpenAPIFaker\SchemaFaker;

use cebe\openapi\spec\Schema;
use Faker\Provider\Base;

use function random_int;

/** @internal */
final class BooleanFaker
{
    public static function generate(Schema $schema): bool
    {
        if ($schema->enum !== null) {
            return Base::randomElement($schema->enum);
        }

        return random_int(0, 1) < 0.5;
    }
}
