<?php

declare(strict_types=1);

namespace Vural\OpenAPIFaker\SchemaFaker;

use cebe\openapi\spec\Schema;
use Faker\Provider\Base;
use function array_unique;
use function array_values;

/**
 * @internal
 */
final class ArrayFaker
{
    /**
     * @return array<mixed>
     */
    public static function generate(Schema $schema) : array
    {
        $minimum  = $schema->minItems ?? 0;
        $maximum  = $schema->maxItems ?? $minimum + 15;
        $itemSize = Base::numberBetween($minimum, $maximum);

        $fakeData = [];

        $itemSchema = new SchemaFaker($schema->items);

        for ($i = 0; $i < $itemSize; $i++) {
            $fakeData[] = $itemSchema->generate();
        }

        if ($schema->uniqueItems === true) {
            $fakeData = array_values(array_unique($fakeData));
        }

        return $fakeData;
    }
}
