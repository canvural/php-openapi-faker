<?php

declare(strict_types=1);

namespace Vural\OpenAPIFaker\SchemaFaker;

use cebe\openapi\spec\Schema;
use Faker\Provider\Base;
use Vural\OpenAPIFaker\Options;
use Vural\OpenAPIFaker\Utils\NumberUtils;

use function mt_getrandmax;
use function reset;

use const PHP_FLOAT_MAX;
use const PHP_INT_MAX;

/**
 * @internal
 */
final class NumberFaker
{
    /**
     * @return int|float|null
     */
    public static function generate(Schema $schema, Options $options)
    {
        if ($options->getStrategy() === Options::STRATEGY_STATIC) {
            return self::generateStatic($schema);
        }

        return self::generateDynamic($schema);
    }

    /**
     * @return int|float
     */
    private static function generateDynamic(Schema $schema)
    {
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

    /**
     * @return int|float|null
     */
    private static function generateStatic(Schema $schema)
    {
        if (! empty($schema->default)) {
            return $schema->default;
        }

        if ($schema->nullable) {
            return null;
        }

        if ($schema->enum !== null) {
            $enums = $schema->enum;

            return reset($enums);
        }

        if ($schema->format !== null) {
            return self::generateStaticFromFormat($schema);
        }

        $minimum          = $schema->minimum;
        $maximum          = $schema->maximum;
        $multipleOf       = $schema->multipleOf;
        $exclusiveMinimum = $schema->exclusiveMinimum;
        $exclusiveMaximum = $schema->exclusiveMaximum;

        if (($schema->type === 'integer')) {
            return (int) NumberUtils::ensureRange(-mt_getrandmax(), $minimum, $maximum, $exclusiveMinimum, $exclusiveMaximum, $multipleOf);
        }

        return (float) NumberUtils::ensureRange(-PHP_INT_MAX, $minimum, $maximum, $exclusiveMinimum, $exclusiveMaximum, $multipleOf);
    }

    /**
     * @return int|float
     */
    private static function generateStaticFromFormat(Schema $schema)
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
                return (float) NumberUtils::ensureRange(-mt_getrandmax() / 1000000, $minimum, $maximum, $exclusiveMinimum, $exclusiveMaximum, $multipleOf);

            case 'double':
                return (float) NumberUtils::ensureRange(-PHP_FLOAT_MAX, $minimum, $maximum, $exclusiveMinimum, $exclusiveMaximum, $multipleOf);

            default:
                return NumberUtils::ensureRange(-mt_getrandmax(), $minimum, $maximum, $exclusiveMinimum, $exclusiveMaximum, $multipleOf);
        }
    }
}
