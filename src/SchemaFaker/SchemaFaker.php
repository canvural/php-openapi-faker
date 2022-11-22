<?php

declare(strict_types=1);

namespace Vural\OpenAPIFaker\SchemaFaker;

use cebe\openapi\spec\Schema;
use Faker\Provider\Base;
use Vural\OpenAPIFaker\Options;

use function array_key_exists;
use function array_keys;
use function array_reverse;
use function in_array;
use function is_array;
use function is_string;
use function reset;
use function Safe\json_decode;
use function Safe\json_encode;

/** @internal */
final class SchemaFaker
{
    private Schema $schema;

    public function __construct(Schema $schema, private Options $options, private bool $request = false)
    {
        $schemaData   = json_decode(json_encode($schema->getSerializableData()), true);
        $this->schema = new Schema($this->resolveOfConstraints($schemaData, $options));
    }

    /** @return array<mixed>|string|bool|int|float|null */
    public function generate(): array|string|bool|int|float|null
    {
        if ($this->schema->type === 'array') {
            return ArrayFaker::generate($this->schema, $this->options);
        }

        if ($this->schema->type === 'object') {
            return ObjectFaker::generate($this->schema, $this->options, $this->request);
        }

        if ($this->schema->type === 'string') {
            return StringFaker::generate($this->schema, $this->options);
        }

        if ($this->schema->type === 'boolean') {
            return BooleanFaker::generate($this->schema, $this->options);
        }

        if (in_array($this->schema->type, ['integer', 'number'], true)) {
            return NumberFaker::generate($this->schema, $this->options);
        }

        if ($this->schema->properties !== null) {
            return ObjectFaker::generate($this->schema, $this->options);
        }

        return [];
    }

    /**
     * @param array<mixed> $schema
     *
     * @return array<mixed>
     */
    private function resolveOfConstraints(array $schema, Options $options): array
    {
        $useStaticStrategy = $options->getStrategy() === Options::STRATEGY_STATIC;
        $copy              = $schema;

        foreach (array_keys($copy) as $key) {
            if ($key === 'oneOf') {
                $subSchema = $useStaticStrategy ? reset($copy[$key]) : Base::randomElement($copy[$key]);
                unset($schema['oneOf'], $copy['oneOf']);
                $resolvedSubSchema = $this->resolveOfConstraints($subSchema, $options);

                $schema = $this->merge($schema, $resolvedSubSchema);
            } elseif ($key === 'allOf') {
                $allSubSchemas = $copy[$key];
                unset($schema['allOf'], $copy['allOf']);
                foreach (array_reverse($allSubSchemas) as $subSchema) {
                    $resolvedSubSchema = $this->resolveOfConstraints($subSchema, $options);

                    $schema = $this->merge($schema, $resolvedSubSchema);
                }
            } elseif ($key === 'anyOf') {
                $subSchema = $useStaticStrategy ? reset($copy[$key]) : Base::randomElement($copy[$key]);
                unset($schema['anyOf'], $copy['anyOf']);
                $resolvedSubSchema = $this->resolveOfConstraints($subSchema, $options);

                $schema = $this->merge($schema, $resolvedSubSchema);
            } elseif (is_array($copy[$key])) {
                $schema[$key] = $this->merge($this->resolveOfConstraints($copy[$key], $options), $schema[$key]);
            }
        }

        return $schema;
    }

    /**
     * Merges $secondArray into $firstArray.
     *
     * Any scalar values in $firstArray will be overwritten by
     * scalar values in $secondArray. But arrays are merged
     * without overwriting.
     *
     * @param array<mixed> $firstArray
     * @param array<mixed> $secondArray
     *
     * @return array<mixed>
     */
    private function merge(array $firstArray, array $secondArray): array
    {
        // phpcs:ignore
        foreach (array_keys($secondArray) as $key) {
            if (! is_array($secondArray[$key])) {
                $firstArray[$key] = $secondArray[$key];

                continue;
            }

            if (! array_key_exists($key, $firstArray) || ! is_array($firstArray[$key])) {
                $firstArray[$key] = [];
            }

            foreach ($secondArray[$key] as $bk => $bv) {
                if (is_string($bk)) {
                    $firstArray[$key][$bk] = $bv;
                } else {
                    $firstArray[$key][] = $bv;
                }
            }
        }

        return $firstArray;
    }
}
