<?php

declare(strict_types=1);

namespace Vural\OpenAPIFaker\Tests\Unit\SchemaFaker;

use Vural\OpenAPIFaker\Options;
use Vural\OpenAPIFaker\SchemaFaker\BooleanFaker;
use Vural\OpenAPIFaker\Tests\SchemaFactory;
use Vural\OpenAPIFaker\Tests\Unit\UnitTestCase;

/**
 * @uses \Vural\OpenAPIFaker\Options
 *
 * @covers \Vural\OpenAPIFaker\SchemaFaker\BooleanFaker
 */
class StaticBooleanFakerTest extends UnitTestCase
{
    private Options $options;

    public function setUp(): void
    {
        parent::setUp();

        $this->options = (new Options())->setStrategy(Options::STRATEGY_STATIC);
    }

    /** @test */
    function it_can_generate_boolean_value()
    {
        $yaml = <<<YAML
type: boolean
YAML;

        $fakeData = BooleanFaker::generate(SchemaFactory::fromYaml($yaml), $this->options);

        self::assertIsBool($fakeData);
        self::assertTrue($fakeData);
    }

    /** @test */
    function it_can_generate_boolean_value_from_enum()
    {
        $yaml = <<<YAML
type: boolean
enum:
  - false
  - true
YAML;

        $fakeData = BooleanFaker::generate(SchemaFactory::fromYaml($yaml), $this->options);

        self::assertFalse($fakeData);
    }

    /** @test */
    function it_can_generate_default_value_from_enum()
    {
        $yaml = <<<YAML
type: boolean
enum:
  - false
  - true
default: true
YAML;

        $fakeData = BooleanFaker::generate(SchemaFactory::fromYaml($yaml), $this->options);

        self::assertIsBool($fakeData);
        self::assertTrue($fakeData);
    }

    /** @test */
    function it_can_generate_nullable_value()
    {
        $yaml = <<<YAML
type: boolean
nullable: true
YAML;

        $fakeData = BooleanFaker::generate(SchemaFactory::fromYaml($yaml), $this->options);

        self::assertNull($fakeData);
    }
}
