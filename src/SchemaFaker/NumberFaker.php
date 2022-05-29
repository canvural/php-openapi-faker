<?php

declare(strict_types=1);

namespace Vural\OpenAPIFaker\SchemaFaker;

use cebe\openapi\spec\Schema;
use Faker\Provider\Base;
use Vural\OpenAPIFaker\Options;

use function mt_getrandmax;

/**
 * @internal
 */
final class NumberFaker
{
    /**
     * @return int|float
     */
    public static function generate(Schema $schema, Options $options): ?int
    {
        if ($options->getStrategy() === Options::STRATEGY_STATIC) {
            return self::generateStatic($schema);
        }

        if ($schema->enum !== null) {
            return Base::randomElement($schema->enum);
        }

        $minimum    = $schema->minimum ?? -mt_getrandmax();
        $maximum    = $schema->maximum ??  mt_getrandmax();
        $multipleOf = $schema->multipleOf ?? 1;

        if ($schema->exclusiveMinimum === true) {
            $minimum++;
        }

        if ($schema->exclusiveMaximum === true) {
            $maximum--;
        }

        if ($schema->type === 'integer') {
            return Base::numberBetween((int) $minimum, (int) $maximum) * $multipleOf;
        }

        return Base::randomFloat(null, $minimum, $maximum) * $multipleOf;
    }

    private static function generateStatic(Schema $schema): ?int
    {
        if ($schema->enum !== null) {
            return reset($schema->enum);
        }

        if (!empty($schema->default)) {
            return $schema->default;
        }

        if ($schema->nullable) {
            return null;
        }

        return 0;
    }
}
