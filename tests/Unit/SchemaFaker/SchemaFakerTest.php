<?php

declare(strict_types=1);

namespace Vural\OpenAPIFaker\Tests\Unit\SchemaFaker;

use League\OpenAPIValidation\Schema\Exception\SchemaMismatch;
use League\OpenAPIValidation\Schema\SchemaValidator;
use Vural\OpenAPIFaker\Options;
use Vural\OpenAPIFaker\SchemaFaker\SchemaFaker;
use Vural\OpenAPIFaker\Tests\SchemaFactory;
use Vural\OpenAPIFaker\Tests\Unit\UnitTestCase;

use function array_keys;

/**
 * @uses \Vural\OpenAPIFaker\SchemaFaker\StringFaker
 * @uses \Vural\OpenAPIFaker\SchemaFaker\NumberFaker
 * @uses \Vural\OpenAPIFaker\SchemaFaker\ObjectFaker
 *
 * @covers \Vural\OpenAPIFaker\SchemaFaker\SchemaFaker
 */
class SchemaFakerTest extends UnitTestCase
{
    private Options $options;

    public function setUp(): void
    {
        parent::setUp();

        $this->options = new Options();
    }

    /** @test */
    function it_can_choose_one_schema_from_one_of()
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
    function it_can_merge_schemas_from_all_of()
    {
        $yaml = <<<YAML
allOf:
  -
    type: object
    properties:
      name:
        type: string
      email:
        type: string
        format: email
  -
    type: object
    properties:
      age:
        type: integer
        minimum: 18
    required:
      - name
      - email
      - age

YAML;

        $fakeData = (new SchemaFaker(SchemaFactory::fromYaml($yaml), $this->options))->generate();

        $this->assertMatchesJsonSnapshot($fakeData);
    }

    /** @test */
    function it_will_merge_all_of_with_existing_schema()
    {
        $yaml = <<<YAML
allOf:
  -
    type: object
    properties:
      id:
        type: integer
properties:
  format:
    pattern: compact
    type: string
required:
  - format
YAML;

        $fakeData = (new SchemaFaker(SchemaFactory::fromYaml($yaml), $this->options))->generate();

        self::assertIsArray($fakeData);
        self::assertArrayHasKey('format', $fakeData);
        $this->assertMatchesJsonSnapshot($fakeData);
    }

    /** @test */
    function it_will_merge_one_of_with_existing_schema()
    {
        $yaml = <<<YAML
oneOf:
  -
    type: object
    properties:
      id:
        type: integer
      format:
        pattern: compact
        type: string
  -
    type: object
    properties:
      format:
        pattern: compact
        type: string

required:
  - format
YAML;

        $fakeData = (new SchemaFaker(SchemaFactory::fromYaml($yaml), $this->options))->generate();

        self::assertIsArray($fakeData);
        self::assertArrayHasKey('format', $fakeData);
        $this->assertMatchesJsonSnapshot($fakeData);
    }

    /** @test */
    function it_can_recursively_merge_of_constraints()
    {
        $yaml = <<<YAML
oneOf:
  -
    oneOf:
     -
        oneOf:
          -
            allOf:
              -
                type: object
                properties:
                  foo:
                    type: integer
                  bap: 
                    pattern: compact
                    type: string
              -
                type: object
                properties:
                  baz:
                    type: string
                  bar:
                    type: string
                    format: date
required:
  - foo
  - bar
  - baz
  - bap
YAML;

        $fakeData = (new SchemaFaker(SchemaFactory::fromYaml($yaml), $this->options))->generate();

        self::assertIsArray($fakeData);
        self::assertArrayHasKey('foo', $fakeData);
        self::assertArrayHasKey('bar', $fakeData);
        self::assertArrayHasKey('baz', $fakeData);
        self::assertArrayHasKey('bap', $fakeData);
        $this->assertMatchesJsonSnapshot($fakeData);
    }

    /** @test */
    function it_can_merge_all_of_with_other_properties()
    {
        $yaml = <<<'YAML'
allOf:
  -
    properties:
      detail:
        type: string
      title:
        type: string
      type:
        format: uri
        type: string
      foo:
        oneOf:
          -
            type: object
            properties:
              shiz:
                type: string
                format: date
              ship:
                type: integer
          -
            properties:
              loop:
                type: object
                properties:
                  poop:
                    type: string
            type: object
          -
            type: integer
    required:
      - type
      - title
      - detail
      - foo
    type: object
description: 'A problem that indicates you are not allowed to see a particular Tweet, User, etc.'
properties:
  resource_id:
    type: string
  resource_type:
    enum:
      - tweet
    type: string
  section:
    enum:
      - data
      - includes
    type: string
  type:
    enum:
      - 'https://api.twitter.com/labs/1/problems/not-authorized-for-resource'
    type: string
required:
  - resource_id
  - resource_type
  - section
type: object
YAML;

        $fakeData = (new SchemaFaker(SchemaFactory::fromYaml($yaml), $this->options))->generate();

        self::assertIsArray($fakeData);
        self::assertArrayHasKey('resource_id', $fakeData);
        self::assertArrayHasKey('resource_type', $fakeData);
        self::assertArrayHasKey('section', $fakeData);
        self::assertArrayHasKey('type', $fakeData);
        self::assertArrayHasKey('title', $fakeData);
        self::assertArrayHasKey('detail', $fakeData);
        $this->assertMatchesJsonSnapshot($fakeData);
    }

    /** @test */
    function another_merge_test()
    {
        $json = <<<'JSON'
{
  "oneOf":[
    {
      "required":[
        "status"
      ],
      "allOf":[
        {
          "required":[
            "type",
            "title",
            "detail"
          ],
          "type":"object",
          "properties":{
            "detail":{
              "type":"string"
            },
            "title":{
              "type":"string"
            },
            "type":{
              "type":"string",
              "format":"uri"
            }
          }
        }
      ],
      "properties":{
        "status":{
          "type":"integer"
        },
        "type":{
          "enum":[
            "http://example.com"
          ],
          "type":"string"
        }
      },
      "description":"A generic problem with no additional information beyond that provided by the HTTP status code."
    }
  ],
  "description":"An HTTP Problem Details object, as defined in IETF RFC 7807 (https://tools.ietf.org/html/rfc7807)."
}
JSON;

        $fakeData = (new SchemaFaker(SchemaFactory::fromJson($json), $this->options))->generate();

        $this->assertMatchesJsonSnapshot($fakeData);

        try {
            (new SchemaValidator())->validate($fakeData, SchemaFactory::fromJson($json));
        } catch (SchemaMismatch $e) {
            self::fail($e->getMessage());
        }
    }

    /** @test */
    function allof_merge()
    {
        $json = <<<'JSON'
{
  "oneOf":[
    {
      "required":[
        "status"
      ],
      "allOf":[
        {
          "required":[
            "type",
            "title",
            "detail"
          ],
          "type":"object",
          "properties":{
            "detail":{
              "type":"string"
            },
            "title":{
              "type":"string"
            },
            "type":{
              "type":"string",
              "format":"uri"
            }
          }
        }
      ],
      "properties":{
        "status":{
          "type":"integer"
        },
        "type":{
          "enum":[
            "about:blank"
          ],
          "type":"string"
        }
      },
      "description":"A generic problem with no additional information beyond that provided by the HTTP status code."
    }
  ]
}
JSON;

        $fakeData = (new SchemaFaker(SchemaFactory::fromJson($json), $this->options))->generate();
        self::assertIsArray($fakeData);
        self::assertSame('about:blank', $fakeData['type']);
        $this->assertMatchesJsonSnapshot($fakeData);
    }

    /** @test */
    function it_can_choose_schemas_from_any_of()
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
            self::assertContains($key, ['age', 'nickname', 'pet_type', 'hunts']);
        }
    }
}
