<?php

declare(strict_types=1);

namespace Vural\OpenAPIFaker\Tests\Integration;

use PHPUnit\Framework\TestCase;
use Vural\OpenAPIFaker\Exception\NoPath;
use Vural\OpenAPIFaker\Exception\NoRequest;
use Vural\OpenAPIFaker\Exception\NoResponse;
use Vural\OpenAPIFaker\Exception\NoSchema;
use Vural\OpenAPIFaker\OpenAPIFaker;
use Vural\OpenAPIFaker\Options;

use function count;

class OpenAPIFakerTest extends TestCase
{
    /**
     * @test
     * @covers \Vural\OpenAPIFaker\OpenAPIFaker::createFromJson
     */
    function it_can_create_faker_from_json()
    {
        $specJson = <<<'JSON'
{
  "openapi": "3.0.2",
  "paths": {
    "/todos": {
      "get": {
        "responses": {
          "200": {
            "description": "Get Todo Items",
            "content": {
              "application/json": {
                "schema": {
                  "$ref": "#/components/schemas/Todos"
                }
              }
            }
          }
        }
      }
    }
  },
  "components": {
    "schemas": {
      "Todo": {
        "type": "object",
        "required": [
          "id",
          "name"
        ],
        "properties": {
          "id": {
            "type": "integer",
            "format": "int64"
          },
          "name": {
            "type": "string"
          },
          "tag": {
            "type": "string"
          }
        }
      },
      "Todos": {
        "type": "array",
        "items": {
          "$ref": "#/components/schemas/Todo"
        }
      }
    }
  }
}
JSON;

        $faker = OpenAPIFaker::createFromJson($specJson);

        self::assertInstanceOf(OpenAPIFaker::class, $faker);
    }

    /**
     * @test
     * @covers \Vural\OpenAPIFaker\OpenAPIFaker::createFromYaml
     */
    function it_can_create_faker_from_yaml()
    {
        $specYaml = <<<'YAML'
openapi: 3.0.2
paths:
  /todos:
    post:
      requestBody:
        required: true
        content:
          application/json:
            schema:
              $ref: '#/components/schemas/Todo'
          application/xml:
            schema:
              $ref: '#/components/schemas/Todo'
    get:
      responses:
        '200':
          description: 'Get Todo Items'
          content: 
            application/json:
              schema:
                $ref: '#/components/schemas/Todos'
components:
  schemas:
    Todo:
      type: object
      required:
        - id
        - name
      properties:
        id:
          type: integer
          format: int64
        name:
          type: string
        tag:
          type: string
    Todos:
      type: array
      items:
        $ref: '#/components/schemas/Todo'

YAML;

        $faker = OpenAPIFaker::createFromYaml($specYaml);

        self::assertInstanceOf(OpenAPIFaker::class, $faker);
    }

    /**
     * @test
     * @testWith
     *      ["/todos", "get"]
     *      ["/todos", "post", "text/plain"]
     */
    function it_throws_exception_if_request_cannot_be_found(string $path, string $method, string $contentType = 'application/json')
    {
        $specYaml = <<<'YAML'
openapi: 3.0.2
paths:
  /todos:
    post:
      requestBody:
        required: true
        content:
          application/json:
            schema:
              $ref: '#/components/schemas/Todo'
          application/xml:
            schema:
              $ref: '#/components/schemas/Todo'
    get:
      responses:
        '200':
          description: 'Get Todo Items'
          content: 
            application/json:
              schema:
                $ref: '#/components/schemas/Todos'
components:
  schemas:
    Todo:
      type: object
      required:
        - id
        - name
      properties:
        id:
          type: integer
          format: int64
        name:
          type: string
        tag:
          type: string
    Todos:
      type: array
      items:
        $ref: '#/components/schemas/Todo'
YAML;

        self::expectException(NoRequest::class);
        $faker = OpenAPIFaker::createFromYaml($specYaml);
        $faker->mockRequest($path, $method, $contentType);
    }

    /**
     * @test
     * @testWith
     *      ["/todos", "post"]
     *      ["/todos", "get", "201"]
     *      ["/todos", "get", "200", "text/plain"]
     */
    function it_throws_exception_if_response_cannot_be_found(string $path, string $method, string $statusCode = '200', string $contentType = 'application/json')
    {
        $specYaml = <<<'YAML'
openapi: 3.0.2
paths:
  /todos:
    post:
      describe: Empty
    get:
      responses:
        '200':
          description: 'Get Todo Items'
          content: 
            application/json:
              schema:
                $ref: '#/components/schemas/Todos'
components:
  schemas:
    Todo:
      type: object
      required:
        - id
        - name
      properties:
        id:
          type: integer
          format: int64
        name:
          type: string
        tag:
          type: string
    Todos:
      type: array
      items:
        $ref: '#/components/schemas/Todo'
YAML;

        self::expectException(NoResponse::class);
        $faker = OpenAPIFaker::createFromYaml($specYaml);
        $faker->mockResponse($path, $method, $statusCode, $contentType);
    }

    /** @test */
    function it_throws_exception_if_operation_not_found_in_responses()
    {
        $specYaml = <<<'YAML'
openapi: 3.0.2
paths:
  /todos:
    post:
      describe: Empty
    get:
      responses:
        '200':
          description: 'Get Todo Items'
          content: 
            application/json:
              schema:
                $ref: '#/components/schemas/Todos'
components:
  schemas:
    Todo:
      type: object
      required:
        - id
        - name
      properties:
        id:
          type: integer
          format: int64
        name:
          type: string
        tag:
          type: string
    Todos:
      type: array
      items:
        $ref: '#/components/schemas/Todo'
YAML;

        self::expectException(NoPath::class);
        $faker = OpenAPIFaker::createFromYaml($specYaml);
        $faker->mockResponse('/todoss', 'GET');
    }

    /** @test */
    function it_throws_exception_if_operation_not_found_in_requests()
    {
        $specYaml = <<<'YAML'
openapi: 3.0.2
paths:
  /todos:
    post:
      describe: Empty
    get:
      responses:
        '200':
          description: 'Get Todo Items'
          content: 
            application/json:
              schema:
                $ref: '#/components/schemas/Todos'
components:
  schemas:
    Todo:
      type: object
      required:
        - id
        - name
      properties:
        id:
          type: integer
          format: int64
        name:
          type: string
        tag:
          type: string
    Todos:
      type: array
      items:
        $ref: '#/components/schemas/Todo'
YAML;

        self::expectException(NoPath::class);
        $faker = OpenAPIFaker::createFromYaml($specYaml);
        $faker->mockRequest('/todoss', 'GET');
    }

    /** @test */
    function it_will_throw_exception_if_spec_does_not_have_any_components()
    {
        $specYaml = <<<'YAML'
openapi: 3.0.2
paths:
  /todos:
    post:
      describe: Empty
YAML;

        self::expectException(NoSchema::class);
        $faker = OpenAPIFaker::createFromYaml($specYaml);
        $faker->mockComponentSchema('DummySchema');
    }

    /** @test */
    function it_will_throw_exception_if_schema_does_not_exist()
    {
        $specYaml = <<<'YAML'
openapi: 3.0.2
paths:
  /todos:
    post:
      describe: Empty
components:
  schemas:
    Todo:
      type: object
      required:
        - id
        - name
      properties:
        id:
          type: integer
          format: int64
        name:
          type: string
        tag:
          type: string
YAML;

        self::expectException(NoSchema::class);
        $faker = OpenAPIFaker::createFromYaml($specYaml);
        $faker->mockComponentSchema('DummySchema');
    }

    /**
     * @uses \Vural\OpenAPIFaker\SchemaFaker\NumberFaker
     * @uses \Vural\OpenAPIFaker\SchemaFaker\SchemaFaker
     * @uses \Vural\OpenAPIFaker\OpenAPIFaker::createFromYaml
     * @uses \Vural\OpenAPIFaker\OpenAPIFaker::mockComponentSchema
     *
     * @test
     * @covers \Vural\OpenAPIFaker\Options
     * @covers \Vural\OpenAPIFaker\OpenAPIFaker::setOptions
     * @covers \Vural\OpenAPIFaker\SchemaFaker\ArrayFaker
     */
    function it_can_set_options()
    {
        $specYaml = <<<YAML
openapi: 3.0.2
components:
  schemas:
    Dummy:
      type: array
      items:
          type: integer
      minItems: 3
YAML;

        $fakeData = OpenAPIFaker::createFromYaml($specYaml)->setOptions([
            'minItems' => 5,
            'notExistingOption' => 'foo',
            'maxItems' => 6,
        ])->mockComponentSchema('Dummy');

        self::assertGreaterThanOrEqual(5, count($fakeData));
        self::assertLessThanOrEqual(6, count($fakeData));
    }

    /** @test */
    function it_can_mock_a_specific_schema()
    {
        $specYaml = self::getTodosSpec();

        $faker = OpenAPIFaker::createFromYaml($specYaml);
        $todo  = $faker->mockComponentSchema('Todo');

        self::assertIsArray($todo);
        self::assertArrayHasKey('id', $todo);
        self::assertIsInt($todo['id']);
        self::assertArrayHasKey('name', $todo);
        self::assertIsString($todo['name']);
    }

    /** @test */
    function it_will_mock_the_response()
    {
        $yamlSpec = self::getTodosSpec();

        $fakeData = OpenAPIFaker::createFromYaml($yamlSpec)->mockResponse('/todos', 'GET');

        self::assertIsArray($fakeData);
        self::assertGreaterThanOrEqual(0, count($fakeData));

        foreach ($fakeData as $fakeDatum) {
            self::assertIsArray($fakeDatum);
            self::assertArrayHasKey('id', $fakeDatum);
            self::assertIsInt($fakeDatum['id']);
            self::assertArrayHasKey('name', $fakeDatum);
            self::assertIsString($fakeDatum['name']);
        }
    }

    /** @test */
    function it_will_mock_the_response_with_example_data()
    {
        $yamlSpec     = self::getTodosSpec();
        $fakerOptions = [
            'strategy' => Options::STRATEGY_STATIC,
        ];

        $faker    = OpenAPIFaker::createFromYaml($yamlSpec)->setOptions($fakerOptions);
        $fakeData = $faker->mockResponse('/todos', 'GET');

        $expected = [
            [
                'id' => 100,
                'name' => 'watering plants',
                'tag' => 'homework',
            ],
            [
                'id' => 101,
                'name' => 'prepare food',
                'tag' => 'homework',
            ],
        ];

        self::assertEquals($expected, $fakeData);
    }

    private function getTodosSpec(): string
    {
        return <<<'YAML'
openapi: 3.0.2
paths:
  /todos:
    post:
      describe: Empty
    get:
      responses:
        '200':
          description: 'Get Todo Items'
          content: 
            application/json:
              schema:
                $ref: '#/components/schemas/Todos'
              examples: 
                textExample:
                  $ref: '#/components/examples/TextExample'
components:
  schemas:
    Todo:
      type: object
      required:
        - id
        - name
      properties:
        id:
          type: integer
          format: int64
        name:
          type: string
        tag:
          type: string
    Todos:
      type: array
      items:
        $ref: '#/components/schemas/Todo'
  examples:
    TextExample:
      summary: A todo example
      value:
        - id: 100
          name: watering plants
          tag: homework
        - id: 101
          name: prepare food
          tag: homework
YAML;
    }
}
