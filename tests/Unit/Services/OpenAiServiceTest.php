<?php

namespace Tests\Unit\Services;

use App\Data\Actor\ActorData;
use App\Exceptions\Actor\ActorAddressMissing;
use App\Exceptions\Actor\ActorFirstNameMissing;
use App\Exceptions\Actor\ActorLastNameMissing;
use App\Exceptions\OpenAI\InvalidOpenAiResponseException;
use App\Services\OpenAi\OpenAiService;
use Illuminate\Support\Facades\Config;
use Mockery;
use OpenAI\Laravel\Facades\OpenAI;
use OpenAI\Responses\Responses\CreateResponse;
use Tests\TestCase;

class OpenAiServiceTest extends TestCase
{
    protected OpenAiService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new OpenAiService();
    }

    public function test_successfully_extracts_actor_data_from_valid_response(): void
    {
        // Arrange
        $description = 'John Doe, 25 years old, 180cm, 75kg, male, lives in New York';
        $mockResponse = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'address' => 'New York',
            'height' => 180,
            'weight' => 75,
            'gender' => 'male',
            'age' => 25,
        ];

        $responseMock = Mockery::mock('overload:' . CreateResponse::class);
        $responseMock->outputText = json_encode($mockResponse);

        OpenAI::shouldReceive('responses->create')
            ->once()
            ->andReturn($responseMock);

        // Act
        $result = $this->service->getActorData($description);

        // Assert
        $this->assertInstanceOf(ActorData::class, $result);
        $this->assertEquals('John', $result->firstName);
        $this->assertEquals('Doe', $result->lastName);
        $this->assertEquals('New York', $result->address);
        $this->assertEquals(180, $result->height);
        $this->assertEquals(75, $result->weight);
        $this->assertEquals('male', $result->gender);
        $this->assertEquals(25, $result->age);
        $this->assertEquals($description, $result->description);
    }

    public function test_throws_exception_when_openai_returns_invalid_json(): void
    {
        // Arrange
        $description = 'Invalid description';

        $responseMock = Mockery::mock(CreateResponse::class);
        $responseMock->outputText = 'This is not a valid JSON';

        OpenAI::shouldReceive('responses->create')
            ->once()
            ->andReturn($responseMock);

        // Assert & Act
        $this->expectException(InvalidOpenAiResponseException::class);
        $this->service->getActorData($description);
    }

    public function test_throws_exception_when_first_name_is_missing(): void
    {
        // Arrange
        $description = 'Test description';
        $mockResponse = [
            'first_name' => null,
            'last_name' => 'Doe',
            'address' => 'New York',
            'height' => 180,
            'weight' => 75,
            'gender' => 'male',
            'age' => 25,
        ];

        $responseMock = Mockery::mock(CreateResponse::class);
        $responseMock->outputText = json_encode($mockResponse);

        OpenAI::shouldReceive('responses->create')
            ->once()
            ->andReturn($responseMock);

        // Assert & Act
        $this->expectException(ActorFirstNameMissing::class);
        $this->service->getActorData($description);
    }

    public function test_throws_exception_when_last_name_is_missing(): void
    {
        // Arrange
        $description = 'Test description';
        $mockResponse = [
            'first_name' => 'John',
            'last_name' => null,
            'address' => 'New York',
            'height' => 180,
            'weight' => 75,
            'gender' => 'male',
            'age' => 25,
        ];

        $responseMock = Mockery::mock(CreateResponse::class);
        $responseMock->outputText = json_encode($mockResponse);

        OpenAI::shouldReceive('responses->create')
            ->once()
            ->andReturn($responseMock);

        // Assert & Act
        $this->expectException(ActorLastNameMissing::class);
        $this->service->getActorData($description);
    }

    public function test_throws_exception_when_address_is_missing(): void
    {
        // Arrange
        $description = 'Test description';
        $mockResponse = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'address' => null,
            'height' => 180,
            'weight' => 75,
            'gender' => 'male',
            'age' => 25,
        ];

        $responseMock = Mockery::mock(CreateResponse::class);
        $responseMock->outputText = json_encode($mockResponse);

        OpenAI::shouldReceive('responses->create')
            ->once()
            ->andReturn($responseMock);

        // Assert & Act
        $this->expectException(ActorAddressMissing::class);
        $this->service->getActorData($description);
    }

    public function test_handles_empty_first_name_string(): void
    {
        // Arrange
        $description = 'Test description';
        $mockResponse = [
            'first_name' => '',
            'last_name' => 'Doe',
            'address' => 'New York',
            'height' => 180,
            'weight' => 75,
            'gender' => 'male',
            'age' => 25,
        ];

        $responseMock = Mockery::mock(CreateResponse::class);
        $responseMock->outputText = json_encode($mockResponse);

        OpenAI::shouldReceive('responses->create')
            ->once()
            ->andReturn($responseMock);

        // Assert & Act
        $this->expectException(ActorFirstNameMissing::class);
        $this->service->getActorData($description);
    }

    public function test_extracts_data_with_optional_fields_as_null(): void
    {
        // Arrange
        $description = 'Minimal actor info';
        $mockResponse = [
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'address' => 'London',
            'height' => null,
            'weight' => null,
            'gender' => null,
            'age' => null,
        ];

        $responseMock = Mockery::mock(CreateResponse::class);
        $responseMock->outputText = json_encode($mockResponse);

        OpenAI::shouldReceive('responses->create')
            ->once()
            ->andReturn($responseMock);

        // Act
        $result = $this->service->getActorData($description);

        // Assert
        $this->assertInstanceOf(ActorData::class, $result);
        $this->assertEquals('Jane', $result->firstName);
        $this->assertEquals('Smith', $result->lastName);
        $this->assertEquals('London', $result->address);
        $this->assertNull($result->height);
        $this->assertNull($result->weight);
        $this->assertNull($result->gender);
        $this->assertNull($result->age);
    }
}
