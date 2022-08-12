<?php

declare(strict_types=1);

namespace Vural\OpenAPIFaker\SchemaFaker;

use cebe\openapi\spec\Example;
use cebe\openapi\spec\MediaType;
use cebe\openapi\spec\Reference;
use cebe\openapi\spec\Schema;
use Vural\OpenAPIFaker\Exception\NoExample;
use Vural\OpenAPIFaker\Options;

use function array_key_exists;
use function reset;

/**
 * @internal
 */
final class RequestFaker
{
    /** @var Schema|Reference|null */
    private $schema;
    private Options $options;
    /** @var Example[]|Reference[] */
    private array $examples;

    public function __construct(MediaType $mediaType, Options $options)
    {
        $this->schema   = $mediaType->schema;
        $this->options  = $options;
        $this->examples = $mediaType->examples;
    }

    /**
     * @return array<mixed>|string|bool|int|float|null
     *
     * @throws NoExample
     */
    public function generate(?string $exampleName = null)
    {
        if ($this->options->getStrategy() === Options::STRATEGY_STATIC && ! empty($this->examples)) {
            if ($exampleName !== null) {
                if (! array_key_exists($exampleName, $this->examples)) {
                    throw NoExample::forRequest($exampleName);
                }

                /** @var Example $exampleName */
                $exampleName = $this->examples[$exampleName];
            } else {
                /** @var Example $exampleName */
                $exampleName = reset($this->examples);
            }

            return $exampleName->value;
        }

        return (new SchemaFaker($this->schema, $this->options, true))->generate();
    }
}
