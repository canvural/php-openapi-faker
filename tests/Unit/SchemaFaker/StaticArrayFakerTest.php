<?php

declare(strict_types=1);

namespace Vural\OpenAPIFaker\Tests\Unit\SchemaFaker;

use Vural\OpenAPIFaker\Options;
use Vural\OpenAPIFaker\SchemaFaker\ArrayFaker;
use Vural\OpenAPIFaker\Tests\SchemaFactory;
use Vural\OpenAPIFaker\Tests\Unit\UnitTestCase;

use function array_unique;
use function sort;

/**
 * @uses \Vural\OpenAPIFaker\SchemaFaker\BooleanFaker
 * @uses \Vural\OpenAPIFaker\SchemaFaker\NumberFaker
 * @uses \Vural\OpenAPIFaker\SchemaFaker\SchemaFaker
 * @uses \Vural\OpenAPIFaker\SchemaFaker\StringFaker
 * @uses \Vural\OpenAPIFaker\Options
 * @uses \Vural\OpenAPIFaker\Utils\NumberUtils
 * @uses \Vural\OpenAPIFaker\Utils\StringUtils
 *
 * @covers \Vural\OpenAPIFaker\SchemaFaker\ArrayFaker
 */
class StaticArrayFakerTest extends UnitTestCase
{
    private Options $options;

    public function setUp(): void
    {
        parent::setUp();

        $this->options = (new Options())->setStrategy(Options::STRATEGY_STATIC);
    }

    /** @test */
    function it_can_handle_min_items()
    {
        $fakeData = ArrayFaker::generate(SchemaFactory::fromJson(
            <<<'JSON'
{
  "type": "array",
  "items": {
    "type": "string"
  },
  "minItems": 3
}
JSON,
        ), $this->options);

        self::assertCount(3, $fakeData);
        $this->assertMatchesJsonSnapshot($fakeData);
    }

    /** @test */
    function it_will_ignore_max_items()
    {
        $fakeData = ArrayFaker::generate(SchemaFactory::fromJson(
            <<<'JSON'
{
  "type": "array",
  "items": {
    "type": "string"
  },
  "maxItems": 10
}
JSON,
        ), $this->options);

        self::assertCount(1, $fakeData);
        $this->assertMatchesJsonSnapshot($fakeData);
    }

    /** @test */
    function it_handles_nested_arrays()
    {
        $yaml = <<<'YAML'
type: array
items:
  type: array
  items:
    type: string
YAML;

        $fakeData = ArrayFaker::generate(SchemaFactory::fromYaml($yaml), $this->options);

        $this->assertMatchesJsonSnapshot($fakeData);
    }

    /** @test */
    function it_can_generate_one_element_from_enum()
    {
        $fakeData = ArrayFaker::generate(SchemaFactory::fromYaml(
            <<<'YAML'
type: array
items:
  type: integer
  enum:
    - 4
    - 88
    - 6789
YAML,
        ), $this->options);

        $this->assertMatchesJsonSnapshot($fakeData);

        $uniqueArray = array_unique($fakeData);
        sort($uniqueArray);
        self::assertSame([4], $uniqueArray);
    }

    /** @test */
    function it_can_override_minimum_items_with_option()
    {
        $options = $this->options->setMinItems(5);

        $fakeData = ArrayFaker::generate(SchemaFactory::fromYaml(
            <<<'YAML'
type: array
items:
  type: integer
minItems: 3
YAML,
        ), $options);

        self::assertCount(5, $fakeData);
        $this->assertMatchesJsonSnapshot($fakeData);
    }

    /** @test */
    function it_can_not_override_maximum_items_with_option()
    {
        $options = $this->options->setMaxItems(3);

        $fakeData = ArrayFaker::generate(SchemaFactory::fromYaml(
            <<<'YAML'
type: array
items:
  type: integer
maxItems: 4
YAML,
        ), $options);

        self::assertCount(1, $fakeData);
        $this->assertMatchesJsonSnapshot($fakeData);
    }

    /** @test */
    function it_can_generate_string_items()
    {
        $fakeData = ArrayFaker::generate(SchemaFactory::fromJson(
            <<<'JSON'
{
  "items": {
    "type": "string"
  }
}
JSON,
        ), $this->options);

        $this->assertMatchesJsonSnapshot($fakeData);
    }

    /** @test */
    function it_can_generate_integer_items()
    {
        $fakeData = ArrayFaker::generate(SchemaFactory::fromJson(
            <<<'JSON'
{
  "items": {
    "type": "integer"
  }
}
JSON,
        ), $this->options);

        $this->assertMatchesJsonSnapshot($fakeData);
    }

    /** @test */
    function it_can_generate_number_items()
    {
        $fakeData = ArrayFaker::generate(SchemaFactory::fromJson(
            <<<'JSON'
{
  "items": {
    "type": "number"
  }
}
JSON,
        ), $this->options);

        $this->assertMatchesJsonSnapshot($fakeData);
    }

    /** @test */
    function it_can_generate_boolean_items()
    {
        $fakeData = ArrayFaker::generate(SchemaFactory::fromJson(
            <<<'JSON'
{
  "items": {
    "type": "boolean"
  }
}
JSON,
        ), $this->options);

        $this->assertMatchesJsonSnapshot($fakeData);
    }

    /** @test */
    function it_can_generate_example_string_items()
    {
        $fakeData = ArrayFaker::generate(SchemaFactory::fromJson(
            <<<'JSON'
{
  "items": {
    "type": "string"
  },
  "example": ["string1","string2","string3"]
}
JSON,
        ), $this->options);

        $this->assertMatchesJsonSnapshot($fakeData);
    }

    /** @test */
    function it_can_generate_example_integer_items()
    {
        $fakeData = ArrayFaker::generate(SchemaFactory::fromJson(
            <<<'JSON'
{
  "type": "array",
  "items": {
    "type": "integer"
  },
  "example": [1,2,3]
}
JSON,
        ), $this->options);

        $this->assertMatchesJsonSnapshot($fakeData);
    }

    /** @test */
    function it_can_generate_example_of_an_individual_array_item()
    {
        $fakeData = ArrayFaker::generate(SchemaFactory::fromJson(
            <<<'JSON'
{
  "type": "array",
  "items": {
    "type": "integer",
    "example": 1
  }
}
JSON,
        ), $this->options);

        $this->assertMatchesJsonSnapshot($fakeData);
    }

    /** @test */
    function it_can_generate_example_for_array_of_objects()
    {
        $fakeData = ArrayFaker::generate(SchemaFactory::fromJson(
            <<<'JSON'
{
  "type": "array",
  "items": {
    "type": "object",
    "properties": {
      "id": {
        "type": "integer"
      },
      "name": {
        "type": "string"
      }
    }
  },
  "example": [
    {
      "id": 1,
      "name": "John"
    },
    {
      "id": 2,
      "name": "Jane"
    }
  ]
}
JSON,
        ), $this->options);

        $this->assertMatchesJsonSnapshot($fakeData);
    }
}
