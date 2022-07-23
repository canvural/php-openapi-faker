<?php

declare(strict_types=1);

namespace Vural\OpenAPIFaker\SchemaFaker;

use cebe\openapi\spec\Example;
use cebe\openapi\spec\MediaType;
use cebe\openapi\spec\Reference;
use cebe\openapi\spec\Schema;
use Vural\OpenAPIFaker\Options;

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
     */
    public function generate()
    {
        if ($this->options->getStrategy() === Options::STRATEGY_STATIC && ! empty($this->examples)) {
            /** @var Example $firstExample */
            $firstExample = reset($this->examples);

            return $firstExample->value;
        }

        return (new SchemaFaker($this->schema, $this->options, true))->generate();
    }
}
