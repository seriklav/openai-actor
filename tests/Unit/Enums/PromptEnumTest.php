<?php

namespace Tests\Unit\Enums;

use App\Enums\OpenAI\PromptEnum;
use Tests\TestCase;

class PromptEnumTest extends TestCase
{
    public function test_has_extract_actor_data_case(): void
    {
        $this->assertEquals('extract_actor_data', PromptEnum::EXTRACT_ACTOR_DATA->value);
    }

    public function test_prompt_method_returns_string(): void
    {
        $prompt = PromptEnum::EXTRACT_ACTOR_DATA->prompt();

        $this->assertIsString($prompt);
        $this->assertNotEmpty($prompt);
    }

    public function test_extract_actor_data_prompt_includes_description(): void
    {
        $description = fake()->sentence();

        $prompt = PromptEnum::EXTRACT_ACTOR_DATA->prompt(['description' => $description]);

        $this->assertStringContainsString($description, $prompt);
    }

    public function test_extract_actor_data_prompt_handles_empty_description(): void
    {
        $prompt = PromptEnum::EXTRACT_ACTOR_DATA->prompt(['description' => '']);

        $this->assertIsString($prompt);
        $this->assertNotEmpty($prompt);
    }

    public function test_extract_actor_data_prompt_handles_missing_description(): void
    {
        $prompt = PromptEnum::EXTRACT_ACTOR_DATA->prompt();

        $this->assertIsString($prompt);
        $this->assertNotEmpty($prompt);
    }

    public function test_extract_actor_data_prompt_contains_required_fields(): void
    {
        $prompt = PromptEnum::EXTRACT_ACTOR_DATA->prompt();

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
        $prompt = PromptEnum::EXTRACT_ACTOR_DATA->prompt();

        $this->assertStringContainsString('JSON', $prompt);
    }

    public function test_extract_actor_data_prompt_specifies_gender_options(): void
    {
        $prompt = PromptEnum::EXTRACT_ACTOR_DATA->prompt();

        $this->assertStringContainsString('male', $prompt);
        $this->assertStringContainsString('female', $prompt);
        $this->assertStringContainsString('other', $prompt);
    }

    public function test_extract_actor_data_prompt_instructs_about_null_values(): void
    {
        $prompt = PromptEnum::EXTRACT_ACTOR_DATA->prompt();

        $this->assertStringContainsString('null', $prompt);
    }

    public function test_extract_actor_data_prompt_has_type_rules(): void
    {
        $prompt = PromptEnum::EXTRACT_ACTOR_DATA->prompt();

        $this->assertStringContainsString('strings', $prompt);
        $this->assertStringContainsString('integers', $prompt);
    }

    public function test_extract_actor_data_prompt_handles_special_characters_in_description(): void
    {
        $description = fake()->name() . " from " . fake()->city();

        $prompt = PromptEnum::EXTRACT_ACTOR_DATA->prompt(['description' => $description]);

        $this->assertStringContainsString($description, $prompt);
    }

    public function test_extract_actor_data_prompt_handles_unicode_characters(): void
    {
        $description = fake()->name() . ", " . fake()->numberBetween(20, 60) . " y.o., " . fake()->city();

        $prompt = PromptEnum::EXTRACT_ACTOR_DATA->prompt(['description' => $description]);

        $this->assertStringContainsString($description, $prompt);
    }

    public function test_extract_actor_data_prompt_handles_long_description(): void
    {
        $description = str_repeat(fake()->sentence() . ' ', 50);

        $prompt = PromptEnum::EXTRACT_ACTOR_DATA->prompt(['description' => $description]);

        $this->assertStringContainsString($description, $prompt);
    }

    public function test_extract_actor_data_prompt_mentions_no_invention_policy(): void
    {
        $prompt = PromptEnum::EXTRACT_ACTOR_DATA->prompt();

        $this->assertStringContainsString('not invent', $prompt);
    }

    public function test_extract_actor_data_prompt_mentions_multiple_languages(): void
    {
        $prompt = PromptEnum::EXTRACT_ACTOR_DATA->prompt();

        $this->assertStringContainsString('English', $prompt);
        $this->assertStringContainsString('Ukrainian', $prompt);
    }

    public function test_extract_actor_data_prompt_requests_only_json_output(): void
    {
        $prompt = PromptEnum::EXTRACT_ACTOR_DATA->prompt();

        $this->assertStringContainsString('ONLY JSON', $prompt);
        $this->assertStringContainsString('No extra text', $prompt);
        $this->assertStringContainsString('no explanations', $prompt);
        $this->assertStringContainsString('no markdown', $prompt);
    }

    public function test_prompt_method_accepts_context_array(): void
    {
        $description = fake()->sentence();
        $context = [
            'description' => $description,
            'other_param' => 'ignored',
        ];

        $prompt = PromptEnum::EXTRACT_ACTOR_DATA->prompt($context);

        $this->assertStringContainsString($description, $prompt);
    }

    public function test_enum_can_be_instantiated_from_string(): void
    {
        $enum = PromptEnum::from('extract_actor_data');

        $this->assertEquals(PromptEnum::EXTRACT_ACTOR_DATA, $enum);
    }

    public function test_enum_value_is_string(): void
    {
        $this->assertIsString(PromptEnum::EXTRACT_ACTOR_DATA->value);
    }

    public function test_extract_actor_data_prompt_specifies_height_weight_age_as_integers(): void
    {
        $prompt = PromptEnum::EXTRACT_ACTOR_DATA->prompt();

        $this->assertMatchesRegularExpression('/height.*integers?/i', $prompt);
        $this->assertMatchesRegularExpression('/weight.*integers?/i', $prompt);
        $this->assertMatchesRegularExpression('/age.*integers?/i', $prompt);
    }

    public function test_extract_actor_data_prompt_mentions_no_units_for_numbers(): void
    {
        $prompt = PromptEnum::EXTRACT_ACTOR_DATA->prompt();

        $this->assertStringContainsString('no units', $prompt);
        $this->assertStringContainsString('only numbers', $prompt);
    }
}
