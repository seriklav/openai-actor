<?php

declare(strict_types=1);

namespace App\Services\OpenAi;

use App\Data\Actor\ActorData;
use App\Enums\OpenAI\PromptEnum;
use App\Exceptions\Actor\ActorAddressMissing;
use App\Exceptions\Actor\ActorFirstNameMissing;
use App\Exceptions\Actor\ActorLastNameMissing;
use App\Exceptions\OpenAI\InvalidOpenAiResponseException;
use OpenAI\Laravel\Facades\OpenAI;

class OpenAiService
{
    public function getActorData(string $description): ActorData
    {
        $prompt = PromptEnum::EXTRACT_ACTOR_DATA->prompt([
            'description' => $description,
        ]);

        $response = OpenAI::responses()->create([
            'model' => 'gpt-5',
            'input' => $prompt,
        ]);

        $outputText = null;
        if (isset($response->output[0])) {
            $firstOutput = $response->output[0];
            if ($firstOutput->type === 'message' && isset($firstOutput->content[0])) {
                $outputText = $firstOutput->content[0]->text;
            }
        }

        $outputText = $outputText ?? $response->outputText ?? $response->choices[0]['text'] ?? null;

        $data = json_decode((string)$outputText, true);

        if (!is_array($data)) {
            throw new InvalidOpenAiResponseException();
        }

        $dto = ActorData::from($data + ['description' => $description]);

        if (empty($dto->firstName)) {
            throw new ActorFirstNameMissing();
        }

        if (empty($dto->lastName)) {
            throw new ActorLastNameMissing();
        }

        if (empty($dto->address)) {
            throw new ActorAddressMissing();
        }

        return $dto;
    }
}
