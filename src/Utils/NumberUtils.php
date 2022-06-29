<?php

declare(strict_types=1);

namespace Vural\OpenAPIFaker\Utils;

/**
 * @internal
 */
final class NumberUtils
{
    /**
     * @param int|float      $sample
     * @param int|float|null $minimum
     * @param int|float|null $maximum
     * @param int|float|null $multipleOf
     *
     * @return int|float
     */
    public static function ensureRange($sample, $minimum, $maximum, ?bool $exclusiveMinimum = null, ?bool $exclusiveMaximum = null, $multipleOf = null)
    {
        if ($minimum === null) {
            $minimum = $sample;
        }

        if ($maximum === null) {
            $maximum = $minimum;
        }

        if ($minimum > $sample) {
            $sample = $minimum;
        }

        if ($maximum < $sample) {
            $sample = $maximum;
        }

        if ($sample === $minimum && $exclusiveMinimum === true) {
            $sample++;
        }

        if ($sample === $maximum && $exclusiveMaximum === true) {
            $sample--;
        }

        if ($multipleOf !== null && $multipleOf !== 1) {
            $sample -= $sample % $multipleOf;
        }

        return $sample;
    }
}
