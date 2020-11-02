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
 */
class ObjectFakerTest extends UnitTestCase
{
    private Options $options;

    public function setUp(): void
    {
        parent::setUp();

        $this->options = new Options();
    }

    /** @test */
    function it_can_generate_simple_object()
    {
        $yaml = <<<YAML
type: object
properties:
  id:
    type: integer
  name:
    type: string
YAML;

        $fakeData = ObjectFaker::generate(SchemaFactory::fromYaml($yaml), $this->options);

        $this->assertMatchesJsonSnapshot($fakeData);
    }

    /** @test */
    function it_can_generate_nested_objects()
    {
        $yaml = <<<YAML
type: object
properties:
    id:
      type: integer
    name:
      type: string
    contact_info:
      type: object
      properties:
        email:
          type: string
          format: email
        phone:
          type: string
YAML;

        $fakeData = ObjectFaker::generate(SchemaFactory::fromYaml($yaml), $this->options);

        $this->assertMatchesJsonSnapshot($fakeData);
    }

    /** @test */
    function it_includes_required_properties_all_the_time()
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
    type: date
  email:
    type: string
    format: email
required:
  - id
  - username
YAML;

        $fakeData = ObjectFaker::generate(SchemaFactory::fromYaml($yaml), $this->options);

        self::assertIsArray($fakeData);
        self::assertArrayHasKey('id', $fakeData);
        self::assertArrayHasKey('username', $fakeData);
        $this->assertMatchesJsonSnapshot($fakeData);
    }
}
