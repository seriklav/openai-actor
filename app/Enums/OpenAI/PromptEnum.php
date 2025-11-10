<?php

declare(strict_types=1);

namespace App\Enums\OpenAI;

enum PromptEnum: string
{
    case EXTRACT_ACTOR_DATA = 'extract_actor_data';

    public function prompt(array $context = []): string
    {
        return match ($this) {
            self::EXTRACT_ACTOR_DATA => $this->extractActorDataPrompt(
                (string)($context['description'] ?? '')
            ),
        };
    }

    private function extractActorDataPrompt(string $description): string
    {
        return <<<PROMPT
You are an information extractor. Understand any language (English/Ukrainian, etc.).
Return ONLY a single valid JSON object with these keys:
first_name, last_name, address, height, weight, gender, age.

Type rules:
- first_name, last_name, address: non-empty strings when present, otherwise null.
- height, weight, age: integers or null (no units, only numbers).
- gender: one of "male", "female", "other", or null.
- Do not invent information. If a value is not specified in the description, return null.

No extra text, no explanations, no markdown — ONLY JSON.

Description:
{$description}
PROMPT;
    }
}
