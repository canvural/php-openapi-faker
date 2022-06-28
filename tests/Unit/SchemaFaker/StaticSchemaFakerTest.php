<?php

declare(strict_types=1);

namespace Vural\OpenAPIFaker\Tests\Unit\SchemaFaker;

use Vural\OpenAPIFaker\Options;
use Vural\OpenAPIFaker\SchemaFaker\SchemaFaker;
use Vural\OpenAPIFaker\Tests\SchemaFactory;
use Vural\OpenAPIFaker\Tests\Unit\UnitTestCase;

use function array_keys;

/**
 * @uses \Vural\OpenAPIFaker\SchemaFaker\StringFaker
 * @uses \Vural\OpenAPIFaker\SchemaFaker\NumberFaker
 * @uses \Vural\OpenAPIFaker\SchemaFaker\ObjectFaker
 * @uses \Vural\OpenAPIFaker\Options
 * @uses \Vural\OpenAPIFaker\Utils\NumberUtils
 * @uses \Vural\OpenAPIFaker\Utils\StringUtils
 *
 * @covers \Vural\OpenAPIFaker\SchemaFaker\SchemaFaker
 */
class StaticSchemaFakerTest extends UnitTestCase
{
    private Options $options;

    public function setUp(): void
    {
        parent::setUp();

        $this->options = (new Options())->setStrategy(Options::STRATEGY_STATIC);
    }

    /** @test */
    function it_can_choose_first_schema_from_one_of()
    {
        $yaml = <<<YAML
oneOf:
  -
    type: string
    format: ipv4
  -
    type: string
    format: ipv6

YAML;

        $fakeData = (new SchemaFaker(SchemaFactory::fromYaml($yaml), $this->options))->generate();

        $this->assertMatchesSnapshot($fakeData);
    }

    /** @test */
    function it_can_choose_first_schema_from_any_of()
    {
        $specYaml = <<<'YAML'
anyOf:
  -
    type: object
    properties: 
      age: 
        type: integer
      nickname: 
        type: string
    required:
      - age
  -
    type: object
    properties:
      pet_type:
        type: string
        enum: [Cat, Dog]
      hunts:
        type: boolean
    required: 
      - pet_type
YAML;

        $fakeData = (new SchemaFaker(SchemaFactory::fromYaml($specYaml), $this->options))->generate();
        self::assertIsArray($fakeData);
        self::assertNotEmpty($fakeData);
        foreach (array_keys($fakeData) as $key) {
            self::assertContains($key, ['age', 'nickname']);
        }
    }
}
