<?php

declare(strict_types=1);

namespace Vural\OpenAPIFaker;

use cebe\openapi\exceptions\TypeErrorException;
use cebe\openapi\exceptions\UnresolvableReferenceException;
use cebe\openapi\spec\MediaType;
use cebe\openapi\spec\OpenApi;
use cebe\openapi\spec\Operation;
use cebe\openapi\spec\RequestBody;
use cebe\openapi\spec\Response;
use cebe\openapi\spec\Schema;
use Exception;
use League\OpenAPIValidation\PSR7 as LeagueOpenAPI;
use Vural\OpenAPIFaker\Exception\NoExample;
use Vural\OpenAPIFaker\Exception\NoPath;
use Vural\OpenAPIFaker\Exception\NoRequest;
use Vural\OpenAPIFaker\Exception\NoResponse;
use Vural\OpenAPIFaker\Exception\NoSchema;
use Vural\OpenAPIFaker\SchemaFaker\RequestFaker;
use Vural\OpenAPIFaker\SchemaFaker\ResponseFaker;
use Vural\OpenAPIFaker\SchemaFaker\SchemaFaker;

use function array_key_exists;
use function method_exists;
use function strtolower;

final class OpenAPIFaker
{
    private OpenApi $openAPISchema;
    private Options $options;

    /** @codeCoverageIgnore  */
    private function __construct()
    {
        $this->options = new Options();
    }

    /**
     * @throws TypeErrorException
     * @throws UnresolvableReferenceException
     */
    public static function createFromJson(string $json): self
    {
        $instance                = new static();
        $instance->openAPISchema = (new LeagueOpenAPI\SchemaFactory\JsonFactory($json))->createSchema();

        return $instance;
    }

    /**
     * @throws TypeErrorException
     * @throws UnresolvableReferenceException
     */
    public static function createFromYaml(string $yaml): self
    {
        $instance                = new static();
        $instance->openAPISchema = (new LeagueOpenAPI\SchemaFactory\YamlFactory($yaml))->createSchema();

        return $instance;
    }

    public static function createFromSchema(OpenApi $schema): self
    {
        $instance                = new static();
        $instance->openAPISchema = $schema;

        return $instance;
    }

    /**
     * @throws NoPath
     * @throws NoRequest
     */
    public function mockRequest(
        string $path,
        string $method,
        string $contentType = 'application/json',
    ): mixed {
        $content = $this->findContentForRequest($path, $method, $contentType);

        return (new RequestFaker($content, $this->options))->generate();
    }

    /**
     * @throws NoPath
     * @throws NoRequest
     * @throws NoExample
     */
    public function mockRequestForExample(
        string $path,
        string $method,
        string $exampleName,
        string $contentType = 'application/json',
    ): mixed {
        $content = $this->findContentForRequest($path, $method, $contentType);

        return (new RequestFaker($content, $this->options))->generate($exampleName);
    }

    /**
     * @throws NoPath
     * @throws NoResponse
     */
    public function mockResponse(
        string $path,
        string $method,
        string $statusCode = '200',
        string $contentType = 'application/json',
    ): mixed {
        $content = $this->findContentForResponse($path, $method, $statusCode, $contentType);

        return (new ResponseFaker($content, $this->options))->generate();
    }

    /**
     * @throws NoPath
     * @throws NoResponse
     * @throws NoExample
     */
    public function mockResponseForExample(
        string $path,
        string $method,
        string $exampleName,
        string $statusCode = '200',
        string $contentType = 'application/json',
    ): mixed {
        $content = $this->findContentForResponse($path, $method, $statusCode, $contentType);

        return (new ResponseFaker($content, $this->options))->generate($exampleName);
    }

    /** @throws Exception */
    public function mockComponentSchema(string $schemaName): mixed
    {
        if ($this->openAPISchema->components === null) {
            throw NoSchema::forZeroComponents();
        }

        if (! array_key_exists($schemaName, $this->openAPISchema->components->schemas)) {
            throw NoSchema::forComponentName($schemaName);
        }

        /** @var Schema $schema */
        $schema = $this->openAPISchema->components->schemas[$schemaName];

        return (new SchemaFaker($schema, $this->options))->generate();
    }

    /** @param array{minItems?:?int, maxItems?:?int, alwaysFakeOptionals?:bool, strategy?:string} $options */
    public function setOptions(array $options): self
    {
        foreach ($options as $key => $value) {
            if (! method_exists($this->options, 'set' . $key)) {
                continue;
            }

            $this->options->{'set' . $key}($value);
        }

        return $this;
    }

    /** @throws NoPath */
    private function findOperation(string $path, string $method): Operation
    {
        try {
            $operation = (new LeagueOpenAPI\SpecFinder($this->openAPISchema))
                ->findOperationSpec(new LeagueOpenAPI\OperationAddress($path, strtolower($method)));
        } catch (LeagueOpenAPI\Exception\NoPath) {
            throw NoPath::forPathAndMethod($path, $method);
        }

        return $operation;
    }

    /**
     * @throws NoPath
     * @throws NoRequest
     */
    private function findContentForRequest(
        string $path,
        string $method,
        string $contentType = 'application/json',
    ): MediaType {
        $operation = $this->findOperation($path, $method);

        if ($operation->requestBody === null) {
            throw NoRequest::forPathAndMethod($path, $method);
        }

        /** @var RequestBody $requestBody */
        $requestBody = $operation->requestBody;
        $contents    = $requestBody->content;

        if (! array_key_exists($contentType, $contents)) {
            throw NoRequest::forPathAndMethodAndContentType($path, $method, $contentType);
        }

        /** @var MediaType $content */
        $content = $contents[$contentType];

        return $content;
    }

    /**
     * @throws NoPath
     * @throws NoResponse
     */
    private function findContentForResponse(
        string $path,
        string $method,
        string $statusCode = '200',
        string $contentType = 'application/json',
    ): MediaType {
        $operation = $this->findOperation($path, $method);

        if ($operation->responses === null) {
            throw NoResponse::forPathAndMethod($path, $method);
        }

        if (! $operation->responses->hasResponse($statusCode)) {
            throw NoResponse::forPathAndMethodAndStatusCode($path, $method, $statusCode);
        }

        /** @var Response $response */
        $response = $operation->responses->getResponse($statusCode);
        $contents = $response->content;

        if (! array_key_exists($contentType, $contents)) {
            throw NoResponse::forPathAndMethodAndStatusCode($path, $method, $statusCode);
        }

        /** @var MediaType $content */
        $content = $contents[$contentType];

        return $content;
    }
}
