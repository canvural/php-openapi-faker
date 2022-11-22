<?php

declare(strict_types=1);

namespace Vural\OpenAPIFaker\SchemaFaker;

use cebe\openapi\spec\Schema;
use Faker\Provider\Base;
use Vural\OpenAPIFaker\Options;
use Vural\OpenAPIFaker\Utils\NumberUtils;

use function mt_getrandmax;
use function reset;

use const PHP_INT_MAX;

/** @internal */
final class NumberFaker
{
    public static function generate(Schema $schema, Options $options): int|float|null
    {
        if ($options->getStrategy() === Options::STRATEGY_STATIC) {
            return self::generateStatic($schema);
        }

        return self::generateDynamic($schema);
    }

    private static function generateDynamic(Schema $schema): int|float|null
    {
        if ($schema->enum !== null) {
            return Base::randomElement($schema->enum);
        }

        $minimum    = $schema->minimum ?? -mt_getrandmax();
        $maximum    = $schema->maximum ??  mt_getrandmax();
        $multipleOf = $schema->multipleOf ?? 1;

        if ($schema->exclusiveMinimum) {
            ++$minimum;
        }

        if ($schema->exclusiveMaximum) {
            --$maximum;
        }

        if ($schema->type === 'integer') {
            return Base::numberBetween((int) $minimum, (int) $maximum) * $multipleOf;
        }

        return Base::randomFloat(null, $minimum, $maximum) * $multipleOf;
    }

    private static function generateStatic(Schema $schema): int|float|null
    {
        if (! empty($schema->default)) {
            return $schema->default;
        }

        if ($schema->example !== null) {
            return $schema->example;
        }

        if ($schema->nullable) {
            return null;
        }

        if ($schema->enum !== null) {
            $enums = $schema->enum;

            return reset($enums);
        }

        return self::generateStaticFromFormat($schema);
    }

    private static function generateStaticFromFormat(Schema $schema): int|float
    {
        $minimum          = $schema->minimum;
        $maximum          = $schema->maximum;
        $multipleOf       = $schema->multipleOf;
        $exclusiveMinimum = $schema->exclusiveMinimum;
        $exclusiveMaximum = $schema->exclusiveMaximum;

        switch ($schema->format) {
            case 'int32':
                return (int) NumberUtils::ensureRange(-mt_getrandmax(), $minimum, $maximum, $exclusiveMinimum, $exclusiveMaximum, $multipleOf);

            case 'int64':
                return (int) NumberUtils::ensureRange(-PHP_INT_MAX, $minimum, $maximum, $exclusiveMinimum, $exclusiveMaximum, $multipleOf);

            case 'float':
            case 'double':
                return (float) NumberUtils::ensureRange(-mt_getrandmax() / 1_000_000, $minimum, $maximum, $exclusiveMinimum, $exclusiveMaximum, $multipleOf);

            case null:
                $number = NumberUtils::ensureRange(0, $minimum, $maximum, $exclusiveMinimum, $exclusiveMaximum, $multipleOf);

                return $schema->type === 'number' ? (float) $number : (int) $number;

            default:
                $number = NumberUtils::ensureRange(0, $minimum, $maximum, $exclusiveMinimum, $exclusiveMaximum, $multipleOf);

                return (int) $number;
        }
    }
}
