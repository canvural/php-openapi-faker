<?php

declare(strict_types=1);

namespace Vural\OpenAPIFaker\SchemaFaker;

use cebe\openapi\spec\Schema;
use Faker\Provider\Base;
use Vural\OpenAPIFaker\Options;

use function random_int;

/**
 * @internal
 */
final class BooleanFaker
{
    public static function generate(Schema $schema, Options  $options): ?bool
    {
        if ($options->getStrategy() === Options::STRATEGY_STATIC) {
            return self::generateStatic($schema);
        }

        return self::generateDynamic($schema);
    }

    /**
     * @return int|float
     */
    private static function generateDynamic(Schema $schema): bool
    {
        if ($schema->enum !== null) {
            return Base::randomElement($schema->enum);
        }

        return random_int(0, 1) < 0.5;
    }

    private static function generateStatic(Schema $schema): ?bool
    {
        if (!empty($schema->default)) {
            return $schema->default;
        }

        if ($schema->nullable) {
            return null;
        }

        if ($schema->enum !== null) {
            $enums = $schema->enum;

            return reset($enums);
        }

        return true;
    }
}
