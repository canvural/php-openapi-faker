<?php

declare(strict_types=1);

namespace Vural\OpenAPIFaker\Tests\Unit\SchemaFaker;

use Vural\OpenAPIFaker\SchemaFaker\ArrayFaker;
use Vural\OpenAPIFaker\Tests\SchemaFactory;
use Vural\OpenAPIFaker\Tests\Unit\UnitTestCase;

use function array_unique;
use function mt_srand;
use function Safe\sort;

use const MT_RAND_PHP;

/**
 * @uses \Vural\OpenAPIFaker\SchemaFaker\SchemaFaker
 * @uses \Vural\OpenAPIFaker\SchemaFaker\StringFaker
 * @uses \Vural\OpenAPIFaker\SchemaFaker\NumberFaker
 *
 * @covers \Vural\OpenAPIFaker\SchemaFaker\ArrayFaker
 */
class ArrayFakerTest extends UnitTestCase
{
    /** @test */
    function it_can_generate_items()
    {
        $fakeData = ArrayFaker::generate(SchemaFactory::fromJson(
            <<<JSON
{
  "items": {
    "type": "string"
  }
}
JSON
        ));

        $this->assertMatchesJsonSnapshot($fakeData);
    }

    /** @test */
    function it_can_handle_min_items()
    {
        $fakeData = ArrayFaker::generate(SchemaFactory::fromJson(
            <<< JSON
{
  "type": "array",
  "items": {
    "type": "string"
  },
  "minItems": 3
}
JSON
        ));

        $this->assertMatchesJsonSnapshot($fakeData);
    }

    /** @test */
    function it_can_handle_max_items()
    {
        $fakeData = ArrayFaker::generate(SchemaFactory::fromJson(
            <<< JSON
{
  "type": "array",
  "items": {
    "type": "string"
  },
  "maxItems": 10
}
JSON
        ));

        $this->assertMatchesJsonSnapshot($fakeData);
    }

    /** @test */
    function it_can_handle_both_min_and_max_items()
    {
        $fakeData = ArrayFaker::generate(SchemaFactory::fromJson(
            <<< JSON
{
  "type": "array",
  "items": {
    "type": "string"
  },
  "minItems": 8,
  "maxItems": 10
}
JSON
        ));

        $this->assertMatchesJsonSnapshot($fakeData);
    }

    /** @test */
    function it_will_use_minimum_plus_15_as_max_items_if_its_not_given()
    {
        $fakeData = ArrayFaker::generate(SchemaFactory::fromJson(
            <<< JSON
{
  "type": "array",
  "items": {
    "type": "string"
  }
}
JSON
        ));

        $this->assertMatchesJsonSnapshot($fakeData);
    }

    /** @test */
    function it_handles_nested_arrays()
    {
        $yaml = <<<YAML
type: array
items:
  type: array
  items:
    type: string
YAML;

        $fakeData = ArrayFaker::generate(SchemaFactory::fromYaml($yaml));

        $this->assertMatchesJsonSnapshot($fakeData);
    }

    /** @test */
    function it_can_generate_unique_elements()
    {
        mt_srand(227, MT_RAND_PHP);

        $fakeData = ArrayFaker::generate(SchemaFactory::fromJson(
            <<< JSON
{
  "type": "array",
  "items": {
    "type": "integer",
    "minimum": 1,
    "maximum": 5
  },
  "minItems": 5,
  "maxItems": 5,
  "uniqueItems": true
}
JSON
        ));

        self::assertIsArray($fakeData);
        self::assertCount(5, $fakeData);
        self::assertSame($fakeData, array_unique($fakeData));
    }

    /** @test */
    function it_can_generate_elements_from_enum()
    {
        $fakeData = ArrayFaker::generate(SchemaFactory::fromYaml(
            <<<YAML
type: array
items:
    type: integer
    enum:
      - 4
      - 88
      - 6789
minItems: 10
YAML
        ));

        $this->assertMatchesJsonSnapshot($fakeData);

        $uniqueArray = array_unique($fakeData);
        sort($uniqueArray);
        self::assertSame([4, 88, 6789], $uniqueArray);
    }
}
