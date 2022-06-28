<?php

declare(strict_types=1);

namespace Vural\OpenAPIFaker\Tests\Unit\SchemaFaker;

use Vural\OpenAPIFaker\Options;
use Vural\OpenAPIFaker\SchemaFaker\NumberFaker;
use Vural\OpenAPIFaker\Tests\SchemaFactory;
use Vural\OpenAPIFaker\Tests\Unit\UnitTestCase;

/**
 * @uses \Vural\OpenAPIFaker\Options
 *
 * @covers \Vural\OpenAPIFaker\SchemaFaker\NumberFaker
 * @covers \Vural\OpenAPIFaker\Utils\NumberUtils
 */
class StaticNumberFakerTest extends UnitTestCase
{
    private Options $options;

    public function setUp(): void
    {
        parent::setUp();

        $this->options = (new Options())->setStrategy(Options::STRATEGY_STATIC);
    }

    /** @test */
    function it_can_generate_a_number()
    {
        $yaml = <<<YAML
type: number
YAML;

        $fakeData = NumberFaker::generate(SchemaFactory::fromYaml($yaml), $this->options);

        self::assertIsFloat($fakeData);
        $this->assertMatchesJsonSnapshot($fakeData);
    }

    /** @test */
    function it_can_handle_number_float_format()
    {
        $yaml = <<<YAML
type: number
format: float
YAML;

        $fakeData = NumberFaker::generate(SchemaFactory::fromYaml($yaml), $this->options);

        self::assertIsFloat($fakeData);
        $this->assertMatchesJsonSnapshot($fakeData);
    }

    /** @test */
    function it_can_handle_number_double_format()
    {
        $yaml = <<<YAML
type: number
format: double
YAML;

        $fakeData = NumberFaker::generate(SchemaFactory::fromYaml($yaml), $this->options);

        self::assertIsFloat($fakeData);
        $this->assertMatchesJsonSnapshot($fakeData);
    }

    /** @test */
    function it_can_generate_a_integer()
    {
        $yaml = <<<YAML
type: integer
YAML;

        $fakeData = NumberFaker::generate(SchemaFactory::fromYaml($yaml), $this->options);

        self::assertIsInt($fakeData);
        $this->assertMatchesJsonSnapshot($fakeData);
    }

    /** @test */
    function it_can_handle_integer_int32_format()
    {
        $yaml = <<<YAML
type: integer
format: int32
YAML;

        $fakeData = NumberFaker::generate(SchemaFactory::fromYaml($yaml), $this->options);

        self::assertIsInt($fakeData);
        $this->assertMatchesJsonSnapshot($fakeData);
    }

    /** @test */
    function it_can_handle_integer_int64_format()
    {
        $yaml = <<<YAML
type: integer
format: int64
YAML;

        $fakeData = NumberFaker::generate(SchemaFactory::fromYaml($yaml), $this->options);

        self::assertIsInt($fakeData);
        $this->assertMatchesJsonSnapshot($fakeData);
    }

    /** @test */
    function it_will_return_a_integer_if_unknown_format_is_given()
    {
        $yaml = <<<YAML
type: number
format: unkown
YAML;

        $fakeData = NumberFaker::generate(SchemaFactory::fromYaml($yaml), $this->options);

        self::assertIsInt($fakeData);
        $this->assertMatchesJsonSnapshot($fakeData);
    }

    /** @test */
    function it_can_handle_minimum_keyword()
    {
        $yaml = <<<YAML
type: integer
minimum: 100
YAML;

        $fakeData = NumberFaker::generate(SchemaFactory::fromYaml($yaml), $this->options);

        self::assertIsInt($fakeData);
        self::assertGreaterThanOrEqual(100, $fakeData);
        $this->assertMatchesJsonSnapshot($fakeData);
    }

    /** @test */
    function it_can_handle_maximum_keyword()
    {
        $yaml = <<<YAML
type: integer
maximum: 100
YAML;

        $fakeData = NumberFaker::generate(SchemaFactory::fromYaml($yaml), $this->options);

        self::assertIsInt($fakeData);
        self::assertLessThanOrEqual(100, $fakeData);
        $this->assertMatchesJsonSnapshot($fakeData);
    }

    /** @test */
    function it_can_handle_both_minimum_and_maximum_keyword()
    {
        $yaml = <<<YAML
type: number
minimum: 100
maximum: 200
YAML;

        $fakeData = NumberFaker::generate(SchemaFactory::fromYaml($yaml), $this->options);

        self::assertIsFloat($fakeData);
        self::assertEquals(100, $fakeData);
        $this->assertMatchesJsonSnapshot($fakeData);
    }

    /** @test */
    function it_can_handle_exclusive_minimum_keyword()
    {
        $yaml = <<<YAML
type: integer
minimum: 100
maximum: 102
exclusiveMinimum: true
YAML;

        $fakeData = NumberFaker::generate(SchemaFactory::fromYaml($yaml), $this->options);

        self::assertIsInt($fakeData);
        self::assertEquals(101, $fakeData);
        $this->assertMatchesJsonSnapshot($fakeData);
    }

    /** @test */
    function it_can_handle_exclusive_maximum_keyword()
    {
        $yaml = <<<YAML
type: integer
minimum: 100
maximum: 102
exclusiveMaximum: true
YAML;

        $fakeData = NumberFaker::generate(SchemaFactory::fromYaml($yaml), $this->options);

        self::assertIsInt($fakeData);
        self::assertEquals(100, $fakeData);
        self::assertLessThan(102, $fakeData);
        $this->assertMatchesJsonSnapshot($fakeData);
    }

    /** @test */
    function it_can_handle_both_exclusive_minimum_and_exclusive_maximum_keyword()
    {
        $yaml = <<<YAML
type: integer
minimum: 100
maximum: 102
exclusiveMinimum: true
exclusiveMaximum: true
YAML;

        $fakeData = NumberFaker::generate(SchemaFactory::fromYaml($yaml), $this->options);

        self::assertIsInt($fakeData);
        self::assertSame(101, $fakeData);
        $this->assertMatchesJsonSnapshot($fakeData);
    }

    /** @test */
    function it_can_handle_multiple_of_keyword_with_integer_number()
    {
        $yaml = <<<YAML
type: integer
multipleOf: 10
YAML;

        $fakeData = NumberFaker::generate(SchemaFactory::fromYaml($yaml), $this->options);

        self::assertIsInt($fakeData);
        self::assertSame(0, $fakeData % 10);
    }

    /** @test */
    function it_can_handle_multiple_of_keyword_with_float_number()
    {
        $yaml = <<<YAML
type: number
multipleOf: 8
YAML;

        $fakeData = NumberFaker::generate(SchemaFactory::fromYaml($yaml), $this->options);

        self::assertIsFloat($fakeData);
        self::assertSame(0, $fakeData % 8);
    }

    /** @test */
    function it_can_generate_elements_from_enum()
    {
        $yaml = <<<YAML
type: number
enum:
  - 1010.9865
  - -123.321
  - 111.123
YAML;

        $fakeData = NumberFaker::generate(SchemaFactory::fromYaml($yaml), $this->options);

        self::assertIsFloat($fakeData);
        $this->assertMatchesJsonSnapshot($fakeData);
    }

    /** @test */
    function it_can_generate_default_value_from_enum()
    {
        $yaml = <<<YAML
type: number
enum:
    - 1010.9865
    - -123.321
    - 111.123
default: -123.321
YAML;

        $fakeData = NumberFaker::generate(SchemaFactory::fromYaml($yaml), $this->options);

        $this->assertMatchesSnapshot($fakeData);
    }

    /** @test */
    function it_can_generate_nullable_value()
    {
        $yaml = <<<YAML
 type: number
 nullable: true
 YAML;

        $fakeData = NumberFaker::generate(SchemaFactory::fromYaml($yaml), $this->options);

        self::assertNull($fakeData);
    }
}
