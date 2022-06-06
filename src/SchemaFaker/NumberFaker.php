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
     * @return int|float
     */
    private static function generateStatic(Schema $schema)
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

        if ($schema->format !== null) {
            return self::generateStaticFromFormat($schema);
        }

        if (($schema->type === 'integer')) {
            return (int) self::ensureLength(-mt_getrandmax(), $schema);
        }

        return (float) self::ensureLength(-PHP_INT_MAX, $schema);
    }

    /**
     * @return int|float
     */
    private static function generateStaticFromFormat(Schema $schema)
    {
        switch ($schema->format) {
            case 'int32':
                return (int) self::ensureLength(-mt_getrandmax(), $schema);

            case 'int64':
                return (int) self::ensureLength(-PHP_INT_MAX, $schema);

            case 'float':
                return (float) self::ensureLength(-mt_getrandmax() / 1000000, $schema);

            case 'double':
                return (float) self::ensureLength(-PHP_FLOAT_MAX, $schema);

            default:
                return self::ensureLength(-mt_getrandmax(), $schema);
        }
    }

    /**
     * @param int|float $sample
     * @param Schema $schema
     * @return int|float
     */
    private static function ensureLength($sample, Schema $schema)
    {
        $minimum    = $schema->minimum ?? $sample;
        $maximum    = $schema->maximum ?? $minimum;
        $multipleOf = $schema->multipleOf ?? 1;

        if ($minimum > $sample) {
            $sample = $minimum;
        }

        if ($maximum < $sample) {
            $sample = $maximum;
        }

        if ($sample === $minimum && $schema->exclusiveMinimum === true) {
            $sample++;
        }

        if ($sample === $maximum && $schema->exclusiveMaximum === true) {
            $sample--;
        }

        if ($multipleOf !== 1) {
            $mod = ($sample % $multipleOf) * -1;

            $sample += $mod;

            return $sample / $multipleOf;
        }

        return $sample;
    }
}
