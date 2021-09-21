<?php

declare(strict_types=1);

namespace Vural\OpenAPIFaker\SchemaFaker;

use cebe\openapi\spec\Schema;
use Faker\Provider\Base;
use Vural\OpenAPIFaker\Options;

use function array_diff;
use function array_keys;
use function array_merge;
use function count;
use function in_array;

/**
 * @internal
 */
final class ObjectFaker
{
    /**
     * @return array<mixed>
     */
    public static function generate(Schema $schema, Options $options, bool $request = false): array
    {
        $result = [];

        $requiredKeys         = $schema->required ?? [];
        $optionalKeys         = array_diff(array_keys($schema->properties), $requiredKeys);
        $selectedOptionalKeys = Base::randomElements($optionalKeys, Base::numberBetween(0, count($optionalKeys)));

        $allPropertyKeys = array_merge($requiredKeys, $selectedOptionalKeys);

        foreach ($schema->properties as $key => $property) {
            if ($property instanceof Schema) {
                if (($request && $property->readOnly) || (! $request && $property->writeOnly)) {
                    continue;
                }
            }

            if (! $options->getAlwaysFakeOptionals() && ! in_array($key, $allPropertyKeys, true)) {
                continue;
            }

            $result[$key] = (new SchemaFaker($property, $options))->generate();
        }

        return $result;
    }
}
