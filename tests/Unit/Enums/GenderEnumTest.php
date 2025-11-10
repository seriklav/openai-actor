<?php

namespace Tests\Unit\Enums;

use App\Enums\Actor\GenderEnum;
use Tests\TestCase;

class GenderEnumTest extends TestCase
{
    public function test_has_male_case(): void
    {
        $this->assertEquals('male', GenderEnum::MALE->value);
    }

    public function test_has_female_case(): void
    {
        $this->assertEquals('female', GenderEnum::FEMALE->value);
    }

    public function test_has_other_case(): void
    {
        $this->assertEquals('other', GenderEnum::OTHER->value);
    }

    public function test_male_label_returns_translated_string(): void
    {
        $label = GenderEnum::MALE->label();

        $this->assertIsString($label);
        $this->assertNotEmpty($label);
    }

    public function test_female_label_returns_translated_string(): void
    {
        $label = GenderEnum::FEMALE->label();

        $this->assertIsString($label);
        $this->assertNotEmpty($label);
    }

    public function test_other_label_returns_translated_string(): void
    {
        $label = GenderEnum::OTHER->label();

        $this->assertIsString($label);
        $this->assertNotEmpty($label);
    }

    public function test_values_returns_array_of_all_gender_values(): void
    {
        $values = GenderEnum::values();

        $this->assertIsArray($values);
        $this->assertCount(3, $values);
        $this->assertContains('male', $values);
        $this->assertContains('female', $values);
        $this->assertContains('other', $values);
    }

    public function test_all_cases_have_string_values(): void
    {
        $cases = GenderEnum::cases();

        foreach ($cases as $case) {
            $this->assertIsString($case->value);
        }
    }

    public function test_can_be_instantiated_from_string(): void
    {
        $male = GenderEnum::from('male');
        $female = GenderEnum::from('female');
        $other = GenderEnum::from('other');

        $this->assertEquals(GenderEnum::MALE, $male);
        $this->assertEquals(GenderEnum::FEMALE, $female);
        $this->assertEquals(GenderEnum::OTHER, $other);
    }

    public function test_try_from_returns_null_for_invalid_value(): void
    {
        $result = GenderEnum::tryFrom('invalid');

        $this->assertNull($result);
    }

    public function test_try_from_returns_enum_for_valid_value(): void
    {
        $result = GenderEnum::tryFrom('male');

        $this->assertInstanceOf(GenderEnum::class, $result);
        $this->assertEquals(GenderEnum::MALE, $result);
    }

    public function test_enum_values_are_lowercase(): void
    {
        $values = GenderEnum::values();

        foreach ($values as $value) {
            $this->assertEquals(strtolower($value), $value);
        }
    }

    public function test_cases_returns_all_gender_options(): void
    {
        $cases = GenderEnum::cases();

        $this->assertCount(3, $cases);
        $this->assertContainsOnlyInstancesOf(GenderEnum::class, $cases);
    }

    public function test_enum_is_backed_by_string(): void
    {
        $this->assertInstanceOf(\BackedEnum::class, GenderEnum::MALE);
        $this->assertIsString(GenderEnum::MALE->value);
    }

    public function test_gender_enum_comparison_works(): void
    {
        $male1 = GenderEnum::MALE;
        $male2 = GenderEnum::from('male');

        $this->assertTrue($male1 === $male2);
        $this->assertFalse($male1 === GenderEnum::FEMALE);
    }

    public function test_values_method_returns_only_string_values(): void
    {
        $values = GenderEnum::values();

        foreach ($values as $value) {
            $this->assertIsString($value);
        }
    }

    public function test_each_gender_has_unique_value(): void
    {
        $values = GenderEnum::values();

        $uniqueValues = array_unique($values);
        $this->assertCount(count($values), $uniqueValues);
    }

    public function test_label_method_exists_for_all_cases(): void
    {
        foreach (GenderEnum::cases() as $case) {
            $label = $case->label();
            $this->assertIsString($label);
            $this->assertNotEmpty($label);
        }
    }
}
