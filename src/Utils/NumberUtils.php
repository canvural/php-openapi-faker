<?php

declare(strict_types=1);

namespace Vural\OpenAPIFaker\Utils;

/** @internal */
final class NumberUtils
{
    public static function ensureRange(int|float $sample, int|float|null $minimum, int|float|null $maximum, bool|null $exclusiveMinimum = null, bool|null $exclusiveMaximum = null, int|float|null $multipleOf = null): int|float
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
