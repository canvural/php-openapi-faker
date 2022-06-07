<?php

declare(strict_types=1);

namespace Vural\OpenAPIFaker\Tests\Unit\SchemaFaker;

use Vural\OpenAPIFaker\Options;
use Vural\OpenAPIFaker\SchemaFaker\ObjectFaker;
use Vural\OpenAPIFaker\Tests\SchemaFactory;
use Vural\OpenAPIFaker\Tests\Unit\UnitTestCase;

/**
 * @uses \Vural\OpenAPIFaker\SchemaFaker\SchemaFaker
 * @uses \Vural\OpenAPIFaker\SchemaFaker\StringFaker
 * @uses \Vural\OpenAPIFaker\SchemaFaker\NumberFaker
 *
 * @covers \Vural\OpenAPIFaker\SchemaFaker\ObjectFaker
 * @covers \Vural\OpenAPIFaker\Options
 */
class StaticObjectFakerTest extends UnitTestCase
{
    private Options $options;

    public function setUp(): void
    {
        parent::setUp();

        $this->options = (new Options())->setStrategy(Options::STRATEGY_STATIC);
    }

    /** @test */
    function it_can_fake_all_properties_if_static_strategy_option_is_set()
    {
        $yaml = <<<YAML
type: object
properties:
  id:
    type: integer
  username:
    type: string
  name:
    type: string
  age:
    type: integer
  birthdate:
    type: string
    format: date
  email:
    type: string
    format: email
required:
  - id
  - username
YAML;

        $fakeData = ObjectFaker::generate(SchemaFactory::fromYaml($yaml), $this->options);

        self::assertIsArray($fakeData);
        self::assertCount(6, $fakeData);

        $this->assertMatchesJsonSnapshot($fakeData);
    }
}
