<?php

declare(strict_types=1);

namespace Vural\OpenAPIFaker\SchemaFaker;

use cebe\openapi\spec\Schema;
use Faker\Provider\Base;
use Vural\OpenAPIFaker\Options;

use function array_unique;
use function count;
use function is_array;

use const SORT_REGULAR;
use const SORT_STRING;

/**
 * @internal
 */
final class ArrayFaker
{
    /**
     * @return array<mixed>
     */
    public static function generate(Schema $schema, Options $options): array
    {
        $minimum = $schema->minItems ?? 0;
        $maximum = $schema->maxItems ?? $minimum + 15;
        if ($options->getStrategy() === Options::STRATEGY_STATIC) {
            $minimum = 1;
            $maximum = 1;
        }

        if ($options->getMinItems() && $minimum < $options->getMinItems()) {
            /** @var int $minimum */
            $minimum = $options->getMinItems();
        }

        if ($options->getMaxItems() && $maximum > $options->getMaxItems()) {
            /** @var int $maximum */
            $maximum = $options->getMaxItems();

            // Don't allow user to set min items above our maximum
            if ($minimum > $maximum) {
                $minimum = $maximum;
            }
        }

        $itemSize = Base::numberBetween($minimum, $maximum);

        $fakeData = [];

        $itemSchema = new SchemaFaker($schema->items, $options);

        for ($i = 0; $i < $itemSize; $i++) {
            $fakeData[] = $itemSchema->generate();

            if ($schema->uniqueItems !== true) {
                continue;
            }

            $uniqueData = array_unique($fakeData, is_array($fakeData[0]) ? SORT_REGULAR : SORT_STRING);

            if (count($uniqueData) >= count($fakeData)) {
                continue;
            }

            $i -= count($fakeData) - count($uniqueData);

            $fakeData = $uniqueData;
        }

        return $fakeData;
    }
}
