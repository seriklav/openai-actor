<?php

namespace Tests\Unit\Services;

use App\Data\Actor\ActorData;
use App\Enums\Actor\GenderEnum;
use App\Exceptions\Actor\ActorAddressMissing;
use App\Exceptions\Actor\ActorFirstNameMissing;
use App\Exceptions\Actor\ActorLastNameMissing;
use App\Exceptions\OpenAI\InvalidOpenAiResponseException;
use App\Services\OpenAi\OpenAiService;
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

    protected function mockOpenAIResponse(string $text): void
    {
        OpenAI::fake([
            CreateResponse::fake([
                'output' => [
                    [
                        'id' => 'msg_test_123',
                        'type' => 'message',
                        'role' => 'assistant',
                        'status' => 'completed',
                        'content' => [
                            [
                                'type' => 'output_text',
                                'text' => $text,
                                'annotations' => [],
                            ],
                        ],
                    ],
                ],
            ]),
        ]);
    }

    public function test_successfully_extracts_actor_data_from_valid_response(): void
    {
        $firstName = fake()->firstName();
        $lastName = fake()->lastName();
        $address = fake()->city();
        $gender = GenderEnum::MALE->value;
        $description = "$firstName, 25 years old, 180cm, 75kg, $gender, lives in $address";

        $mockResponse = [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'address' => $address,
            'height' => 180,
            'weight' => 75,
            'gender' => $gender,
            'age' => 25,
        ];

        $this->mockOpenAIResponse(json_encode($mockResponse));

        $result = $this->service->getActorData($description);

        $this->assertInstanceOf(ActorData::class, $result);
        $this->assertEquals($firstName, $result->firstName);
        $this->assertEquals($lastName, $result->lastName);
        $this->assertEquals($address, $result->address);
        $this->assertEquals(180, $result->height);
        $this->assertEquals(75, $result->weight);
        $this->assertEquals($gender, $result->gender);
        $this->assertEquals(25, $result->age);
        $this->assertEquals($description, $result->description);
    }

    public function test_throws_exception_when_openai_returns_invalid_json(): void
    {
        $description = 'Invalid description';
        $this->mockOpenAIResponse('This is not a valid JSON');

        $this->expectException(InvalidOpenAiResponseException::class);
        $this->service->getActorData($description);
    }

    public function test_throws_exception_when_first_name_is_missing(): void
    {
        $lastName = fake()->lastName();
        $address = fake()->city();
        $gender = GenderEnum::MALE->value;
        $description = 'Test description';

        $mockResponse = [
            'first_name' => null,
            'last_name' => $lastName,
            'address' => $address,
            'height' => 180,
            'weight' => 75,
            'gender' => $gender,
            'age' => 25,
        ];

        $this->mockOpenAIResponse(json_encode($mockResponse));

        $this->expectException(ActorFirstNameMissing::class);
        $this->service->getActorData($description);
    }

    public function test_throws_exception_when_last_name_is_missing(): void
    {
        $firstName = fake()->firstName();
        $address = fake()->city();
        $gender = GenderEnum::MALE->value;
        $description = 'Test description';

        $mockResponse = [
            'first_name' => $firstName,
            'last_name' => null,
            'address' => $address,
            'height' => 180,
            'weight' => 75,
            'gender' => $gender,
            'age' => 25,
        ];

        $this->mockOpenAIResponse(json_encode($mockResponse));

        $this->expectException(ActorLastNameMissing::class);
        $this->service->getActorData($description);
    }

    public function test_throws_exception_when_address_is_missing(): void
    {
        $firstName = fake()->firstName();
        $lastName = fake()->lastName();
        $gender = GenderEnum::MALE->value;
        $description = 'Test description';

        $mockResponse = [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'address' => null,
            'height' => 180,
            'weight' => 75,
            'gender' => $gender,
            'age' => 25,
        ];

        $this->mockOpenAIResponse(json_encode($mockResponse));

        $this->expectException(ActorAddressMissing::class);
        $this->service->getActorData($description);
    }

    public function test_handles_empty_first_name_string(): void
    {
        $lastName = fake()->lastName();
        $address = fake()->city();
        $gender = GenderEnum::MALE->value;
        $description = 'Test description';

        $mockResponse = [
            'first_name' => '',
            'last_name' => $lastName,
            'address' => $address,
            'height' => 180,
            'weight' => 75,
            'gender' => $gender,
            'age' => 25,
        ];

        $this->mockOpenAIResponse(json_encode($mockResponse));

        $this->expectException(ActorFirstNameMissing::class);
        $this->service->getActorData($description);
    }

    public function test_extracts_data_with_optional_fields_as_null(): void
    {
        $firstName = fake()->firstName();
        $lastName = fake()->lastName();
        $address = fake()->city();
        $description = 'Minimal actor info';

        $mockResponse = [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'address' => $address,
            'height' => null,
            'weight' => null,
            'gender' => null,
            'age' => null,
        ];

        $this->mockOpenAIResponse(json_encode($mockResponse));

        $result = $this->service->getActorData($description);

        $this->assertInstanceOf(ActorData::class, $result);
        $this->assertEquals($firstName, $result->firstName);
        $this->assertEquals($lastName, $result->lastName);
        $this->assertEquals($address, $result->address);
        $this->assertNull($result->height);
        $this->assertNull($result->weight);
        $this->assertNull($result->gender);
        $this->assertNull($result->age);
    }
}
