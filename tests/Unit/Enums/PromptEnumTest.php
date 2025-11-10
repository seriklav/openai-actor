<?php

namespace Tests\Unit\Enums;

use App\Enums\OpenAI\PromptEnum;
use Tests\TestCase;

class PromptEnumTest extends TestCase
{
    public function test_has_extract_actor_data_case(): void
    {
        // Assert
        $this->assertEquals('extract_actor_data', PromptEnum::EXTRACT_ACTOR_DATA->value);
    }

    public function test_prompt_method_returns_string(): void
    {
        // Act
        $prompt = PromptEnum::EXTRACT_ACTOR_DATA->prompt();

        // Assert
        $this->assertIsString($prompt);
        $this->assertNotEmpty($prompt);
    }

    public function test_extract_actor_data_prompt_includes_description(): void
    {
        // Arrange
        $description = 'John Doe, 25 years old, male';

        // Act
        $prompt = PromptEnum::EXTRACT_ACTOR_DATA->prompt(['description' => $description]);

        // Assert
        $this->assertStringContainsString($description, $prompt);
    }

    public function test_extract_actor_data_prompt_handles_empty_description(): void
    {
        // Act
        $prompt = PromptEnum::EXTRACT_ACTOR_DATA->prompt(['description' => '']);

        // Assert
        $this->assertIsString($prompt);
        $this->assertNotEmpty($prompt);
    }

    public function test_extract_actor_data_prompt_handles_missing_description(): void
    {
        // Act
        $prompt = PromptEnum::EXTRACT_ACTOR_DATA->prompt();

        // Assert
        $this->assertIsString($prompt);
        $this->assertNotEmpty($prompt);
    }

    public function test_extract_actor_data_prompt_contains_required_fields(): void
    {
        // Act
        $prompt = PromptEnum::EXTRACT_ACTOR_DATA->prompt();

        // Assert
        $this->assertStringContainsString('first_name', $prompt);
        $this->assertStringContainsString('last_name', $prompt);
        $this->assertStringContainsString('address', $prompt);
        $this->assertStringContainsString('height', $prompt);
        $this->assertStringContainsString('weight', $prompt);
        $this->assertStringContainsString('gender', $prompt);
        $this->assertStringContainsString('age', $prompt);
    }

    public function test_extract_actor_data_prompt_mentions_json_format(): void
    {
        // Act
        $prompt = PromptEnum::EXTRACT_ACTOR_DATA->prompt();

        // Assert
        $this->assertStringContainsString('JSON', $prompt);
    }

    public function test_extract_actor_data_prompt_specifies_gender_options(): void
    {
        // Act
        $prompt = PromptEnum::EXTRACT_ACTOR_DATA->prompt();

        // Assert
        $this->assertStringContainsString('male', $prompt);
        $this->assertStringContainsString('female', $prompt);
        $this->assertStringContainsString('other', $prompt);
    }

    public function test_extract_actor_data_prompt_instructs_about_null_values(): void
    {
        // Act
        $prompt = PromptEnum::EXTRACT_ACTOR_DATA->prompt();

        // Assert
        $this->assertStringContainsString('null', $prompt);
    }

    public function test_extract_actor_data_prompt_has_type_rules(): void
    {
        // Act
        $prompt = PromptEnum::EXTRACT_ACTOR_DATA->prompt();

        // Assert
        $this->assertStringContainsString('strings', $prompt);
        $this->assertStringContainsString('integers', $prompt);
    }

    public function test_extract_actor_data_prompt_handles_special_characters_in_description(): void
    {
        // Arrange
        $description = "John O'Brien, 25 years old, lives in São Paulo";

        // Act
        $prompt = PromptEnum::EXTRACT_ACTOR_DATA->prompt(['description' => $description]);

        // Assert
        $this->assertStringContainsString($description, $prompt);
        $this->assertStringContainsString("O'Brien", $prompt);
        $this->assertStringContainsString("São Paulo", $prompt);
    }

    public function test_extract_actor_data_prompt_handles_unicode_characters(): void
    {
        // Arrange
        $description = 'Иван Петров, 30 лет, живёт в Москве';

        // Act
        $prompt = PromptEnum::EXTRACT_ACTOR_DATA->prompt(['description' => $description]);

        // Assert
        $this->assertStringContainsString($description, $prompt);
    }

    public function test_extract_actor_data_prompt_handles_long_description(): void
    {
        // Arrange
        $description = str_repeat('Very detailed actor description. ', 50);

        // Act
        $prompt = PromptEnum::EXTRACT_ACTOR_DATA->prompt(['description' => $description]);

        // Assert
        $this->assertStringContainsString($description, $prompt);
    }

    public function test_extract_actor_data_prompt_mentions_no_invention_policy(): void
    {
        // Act
        $prompt = PromptEnum::EXTRACT_ACTOR_DATA->prompt();

        // Assert
        $this->assertStringContainsString('not invent', $prompt);
    }

    public function test_extract_actor_data_prompt_mentions_multiple_languages(): void
    {
        // Act
        $prompt = PromptEnum::EXTRACT_ACTOR_DATA->prompt();

        // Assert
        $this->assertStringContainsString('English', $prompt);
        $this->assertStringContainsString('Ukrainian', $prompt);
        $this->assertStringContainsString('Russian', $prompt);
    }

    public function test_extract_actor_data_prompt_requests_only_json_output(): void
    {
        // Act
        $prompt = PromptEnum::EXTRACT_ACTOR_DATA->prompt();

        // Assert
        $this->assertStringContainsString('ONLY JSON', $prompt);
        $this->assertStringContainsString('No extra text', $prompt);
        $this->assertStringContainsString('no explanations', $prompt);
        $this->assertStringContainsString('no markdown', $prompt);
    }

    public function test_prompt_method_accepts_context_array(): void
    {
        // Arrange
        $context = [
            'description' => 'Test description',
            'other_param' => 'ignored',
        ];

        // Act
        $prompt = PromptEnum::EXTRACT_ACTOR_DATA->prompt($context);

        // Assert
        $this->assertStringContainsString('Test description', $prompt);
    }

    public function test_enum_can_be_instantiated_from_string(): void
    {
        // Act
        $enum = PromptEnum::from('extract_actor_data');

        // Assert
        $this->assertEquals(PromptEnum::EXTRACT_ACTOR_DATA, $enum);
    }

    public function test_enum_value_is_string(): void
    {
        // Assert
        $this->assertIsString(PromptEnum::EXTRACT_ACTOR_DATA->value);
    }

    public function test_extract_actor_data_prompt_specifies_height_weight_age_as_integers(): void
    {
        // Act
        $prompt = PromptEnum::EXTRACT_ACTOR_DATA->prompt();

        // Assert
        $this->assertMatchesRegularExpression('/height.*integers?/i', $prompt);
        $this->assertMatchesRegularExpression('/weight.*integers?/i', $prompt);
        $this->assertMatchesRegularExpression('/age.*integers?/i', $prompt);
    }

    public function test_extract_actor_data_prompt_mentions_no_units_for_numbers(): void
    {
        // Act
        $prompt = PromptEnum::EXTRACT_ACTOR_DATA->prompt();

        // Assert
        $this->assertStringContainsString('no units', $prompt);
        $this->assertStringContainsString('only numbers', $prompt);
    }
}
