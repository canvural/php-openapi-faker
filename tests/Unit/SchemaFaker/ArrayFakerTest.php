<?php

declare(strict_types=1);

namespace Vural\OpenAPIFaker\Tests\Unit\SchemaFaker;

use Vural\OpenAPIFaker\Options;
use Vural\OpenAPIFaker\SchemaFaker\ArrayFaker;
use Vural\OpenAPIFaker\Tests\SchemaFactory;
use Vural\OpenAPIFaker\Tests\Unit\UnitTestCase;

use function array_unique;
use function count;
use function mt_srand;
use function Safe\sort;

use const MT_RAND_PHP;

/**
 * @uses \Vural\OpenAPIFaker\SchemaFaker\SchemaFaker
 * @uses \Vural\OpenAPIFaker\SchemaFaker\StringFaker
 * @uses \Vural\OpenAPIFaker\SchemaFaker\NumberFaker
 * @uses \Vural\OpenAPIFaker\Options
 *
 * @covers \Vural\OpenAPIFaker\SchemaFaker\ArrayFaker
 */
class ArrayFakerTest extends UnitTestCase
{
    private Options $options;

    public function setUp(): void
    {
        parent::setUp();

        $this->options = new Options();
    }

    /** @test */
    function it_can_generate_items()
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

        $this->assertMatchesJsonSnapshot($fakeData);
    }

    /** @test */
    function it_can_handle_max_items()
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

        $this->assertMatchesJsonSnapshot($fakeData);
    }

    /** @test */
    function it_can_handle_both_min_and_max_items()
    {
        $fakeData = ArrayFaker::generate(SchemaFactory::fromJson(
            <<<'JSON'
{
  "type": "array",
  "items": {
    "type": "string"
  },
  "minItems": 8,
  "maxItems": 10
}
JSON,
        ), $this->options);

        $this->assertMatchesJsonSnapshot($fakeData);
    }

    /** @test */
    function it_will_use_minimum_plus_15_as_max_items_if_its_not_given()
    {
        $fakeData = ArrayFaker::generate(SchemaFactory::fromJson(
            <<<'JSON'
{
  "type": "array",
  "items": {
    "type": "string"
  }
}
JSON,
        ), $this->options);

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
    function it_can_generate_unique_elements()
    {
        mt_srand(227, MT_RAND_PHP);

        $fakeData = ArrayFaker::generate(SchemaFactory::fromJson(
            <<<'JSON'
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
JSON,
        ), $this->options);

        self::assertIsArray($fakeData);
        self::assertCount(5, $fakeData);
        self::assertSame($fakeData, array_unique($fakeData));
    }

    /** @test */
    function it_can_generate_elements_from_enum()
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
minItems: 10
YAML,
        ), $this->options);

        $this->assertMatchesJsonSnapshot($fakeData);

        $uniqueArray = array_unique($fakeData);
        sort($uniqueArray);
        self::assertSame([4, 88, 6789], $uniqueArray);
    }

    /** @test */
    function it_can_override_minimum_items_with_option()
    {
        $options = (new Options())->setMinItems(5);

        $fakeData = ArrayFaker::generate(SchemaFactory::fromYaml(
            <<<'YAML'
type: array
items:
    type: integer
minItems: 3
YAML,
        ), $options);

        self::assertGreaterThan(5, count($fakeData));
        $this->assertMatchesJsonSnapshot($fakeData);
    }

    /** @test */
    function it_will_not_override_minimum_items_with_option_if_option_is_smaller()
    {
        $options = (new Options())->setMinItems(1);

        $fakeData = ArrayFaker::generate(SchemaFactory::fromYaml(
            <<<'YAML'
type: array
items:
    type: integer
minItems: 3
maxItems: 3
YAML,
        ), $options);

        self::assertCount(3, $fakeData);
        $this->assertMatchesJsonSnapshot($fakeData);
    }

    /** @test */
    function it_can_override_maximum_items_with_option()
    {
        $options = (new Options())->setMaxItems(3);

        $fakeData = ArrayFaker::generate(SchemaFactory::fromYaml(
            <<<'YAML'
type: array
items:
    type: integer
maxItems: 4
YAML,
        ), $options);

        self::assertLessThanOrEqual(3, count($fakeData));
        $this->assertMatchesJsonSnapshot($fakeData);
    }

    /** @test */
    function it_will_not_override_maximum_items_with_option_if_option_is_greater()
    {
        $options = (new Options())->setMaxItems(5);

        $fakeData = ArrayFaker::generate(SchemaFactory::fromYaml(
            <<<'YAML'
type: array
items:
    type: integer
minItems: 3
maxItems: 3
YAML,
        ), $options);

        self::assertCount(3, $fakeData);
        $this->assertMatchesJsonSnapshot($fakeData);
    }

    /** @test */
    function it_can_override_both_minimum_and_maximum_items_with_options()
    {
        $options = (new Options())->setMinItems(3)->setMaxItems(3);

        $fakeData = ArrayFaker::generate(SchemaFactory::fromYaml(
            <<<'YAML'
type: array
items:
    type: integer
minItems: 1
maxItems: 4
YAML,
        ), $options);

        self::assertCount(3, $fakeData);
        $this->assertMatchesJsonSnapshot($fakeData);
    }

    /** @test */
    function it_can_override_minimum_if_its_greater_than_maximum()
    {
        $options = (new Options())->setMaxItems(4);

        $fakeData = ArrayFaker::generate(SchemaFactory::fromYaml(
            <<<'YAML'
type: array
items:
    type: integer
minItems: 5
YAML,
        ), $options);

        self::assertCount(4, $fakeData);
        $this->assertMatchesJsonSnapshot($fakeData);
    }
}
