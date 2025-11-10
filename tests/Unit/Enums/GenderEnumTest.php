<?php

namespace Tests\Unit\Enums;

use App\Enums\Actor\GenderEnum;
use Tests\TestCase;

class GenderEnumTest extends TestCase
{
    public function test_has_male_case(): void
    {
        // Assert
        $this->assertEquals('male', GenderEnum::MALE->value);
    }

    public function test_has_female_case(): void
    {
        // Assert
        $this->assertEquals('female', GenderEnum::FEMALE->value);
    }

    public function test_has_other_case(): void
    {
        // Assert
        $this->assertEquals('other', GenderEnum::OTHER->value);
    }

    public function test_male_label_returns_translated_string(): void
    {
        // Act
        $label = GenderEnum::MALE->label();

        // Assert
        $this->assertIsString($label);
        $this->assertNotEmpty($label);
    }

    public function test_female_label_returns_translated_string(): void
    {
        // Act
        $label = GenderEnum::FEMALE->label();

        // Assert
        $this->assertIsString($label);
        $this->assertNotEmpty($label);
    }

    public function test_other_label_returns_translated_string(): void
    {
        // Act
        $label = GenderEnum::OTHER->label();

        // Assert
        $this->assertIsString($label);
        $this->assertNotEmpty($label);
    }

    public function test_values_returns_array_of_all_gender_values(): void
    {
        // Act
        $values = GenderEnum::values();

        // Assert
        $this->assertIsArray($values);
        $this->assertCount(3, $values);
        $this->assertContains('male', $values);
        $this->assertContains('female', $values);
        $this->assertContains('other', $values);
    }

    public function test_all_cases_have_string_values(): void
    {
        // Act
        $cases = GenderEnum::cases();

        // Assert
        foreach ($cases as $case) {
            $this->assertIsString($case->value);
        }
    }

    public function test_can_be_instantiated_from_string(): void
    {
        // Act
        $male = GenderEnum::from('male');
        $female = GenderEnum::from('female');
        $other = GenderEnum::from('other');

        // Assert
        $this->assertEquals(GenderEnum::MALE, $male);
        $this->assertEquals(GenderEnum::FEMALE, $female);
        $this->assertEquals(GenderEnum::OTHER, $other);
    }

    public function test_try_from_returns_null_for_invalid_value(): void
    {
        // Act
        $result = GenderEnum::tryFrom('invalid');

        // Assert
        $this->assertNull($result);
    }

    public function test_try_from_returns_enum_for_valid_value(): void
    {
        // Act
        $result = GenderEnum::tryFrom('male');

        // Assert
        $this->assertInstanceOf(GenderEnum::class, $result);
        $this->assertEquals(GenderEnum::MALE, $result);
    }

    public function test_enum_values_are_lowercase(): void
    {
        // Act
        $values = GenderEnum::values();

        // Assert
        foreach ($values as $value) {
            $this->assertEquals(strtolower($value), $value);
        }
    }

    public function test_cases_returns_all_gender_options(): void
    {
        // Act
        $cases = GenderEnum::cases();

        // Assert
        $this->assertCount(3, $cases);
        $this->assertContainsOnlyInstancesOf(GenderEnum::class, $cases);
    }

    public function test_enum_is_backed_by_string(): void
    {
        // Assert
        $this->assertInstanceOf(\BackedEnum::class, GenderEnum::MALE);
        $this->assertIsString(GenderEnum::MALE->value);
    }

    public function test_gender_enum_comparison_works(): void
    {
        // Act
        $male1 = GenderEnum::MALE;
        $male2 = GenderEnum::from('male');

        // Assert
        $this->assertTrue($male1 === $male2);
        $this->assertFalse($male1 === GenderEnum::FEMALE);
    }

    public function test_values_method_returns_only_string_values(): void
    {
        // Act
        $values = GenderEnum::values();

        // Assert
        foreach ($values as $value) {
            $this->assertIsString($value);
        }
    }

    public function test_each_gender_has_unique_value(): void
    {
        // Act
        $values = GenderEnum::values();

        // Assert
        $uniqueValues = array_unique($values);
        $this->assertCount(count($values), $uniqueValues);
    }

    public function test_label_method_exists_for_all_cases(): void
    {
        // Act & Assert
        foreach (GenderEnum::cases() as $case) {
            $label = $case->label();
            $this->assertIsString($label);
            $this->assertNotEmpty($label);
        }
    }
}
