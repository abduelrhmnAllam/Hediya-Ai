<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Arr;

class OpenAIService
{
    protected $key;
    protected $model;
    protected $baseUri;

    public function __construct()
    {
        $this->key = config('services.openai.key') ?? env('OPENAI_API_KEY');
        $this->model = config('services.openai.model') ?? env('OPENAI_MODEL', 'gpt-4o-mini');
        $this->baseUri = rtrim(config('services.openai.base_uri') ?? env('OPENAI_BASE_URI', 'https://api.openai.com/'), '/');
    }

    /**
     * Send user natural-language query to OpenAI and request structured JSON conditions.
     *
     * @param string $userInput
     * @return array|null Structured associative array or null on failure
     */
    public function parseToFilters(string $userInput): ?array
    {
        // Instruction prompt to force JSON output with simple schema
        $systemPrompt = <<<PROMPT
You are an assistant that converts user natural-language search requests into a strict JSON object
with keys: "category" (string|null), "min_price" (number|null), "max_price" (number|null), "keywords" (array of strings|null).
Only output JSON and nothing else. If a field is not present, set it to null.
Examples:
Input: "show me laptops under 500 in electronics category"
Output: {"category":"electronics","min_price":null,"max_price":500,"keywords":["laptops"]}
PROMPT;

        // Build messages for chat completions endpoint
        $messages = [
            ['role' => 'system', 'content' => $systemPrompt],
            ['role' => 'user', 'content' => $userInput]
        ];

        try {
            $response = Http::withToken($this->key)
                ->acceptJson()
                ->post($this->baseUri . '/v1/chat/completions', [
                    'model' => $this->model,
                    'messages' => $messages,
                    'max_tokens' => 300,
                    'temperature' => 0.0,
                ]);

            if ($response->failed()) {
                Log::error('OpenAI API error', ['status' => $response->status(), 'body' => $response->body()]);
                return null;
            }

            $body = $response->json();

            // Attempt to read content: for chat completions -> choices[0].message.content
            $content = Arr::get($body, 'choices.0.message.content') ?? Arr::get($body, 'choices.0.text');

            if (!$content) {
                Log::error('OpenAI returned empty content', ['body' => $body]);
                return null;
            }

            // Try to extract JSON from content (in case assistant adds backticks)
            $jsonString = $this->extractJson($content);

            if (!$jsonString) {
                Log::error('Failed to extract JSON from OpenAI content', ['content' => $content]);
                return null;
            }

            $parsed = json_decode($jsonString, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error('JSON decode error', ['error' => json_last_error_msg(), 'json' => $jsonString]);
                return null;
            }

            // Normalize keys: ensure presence of expected keys
            return [
                'category' => $parsed['category'] ?? null,
                'min_price' => isset($parsed['min_price']) ? $this->toNumberOrNull($parsed['min_price']) : null,
                'max_price' => isset($parsed['max_price']) ? $this->toNumberOrNull($parsed['max_price']) : null,
                'keywords' => is_array($parsed['keywords']) ? $parsed['keywords'] : (isset($parsed['keywords']) ? [$parsed['keywords']] : null),
            ];

        } catch (\Throwable $e) {
            Log::error('OpenAIService exception', ['exception' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Extract raw JSON substring from assistant content.
     */
    protected function extractJson(string $text): ?string
    {
        // Remove any surrounding ```json ... ``` or ``` ... ```
        // Try to find the first { ... } block
        if (preg_match('/({.*})/s', $text, $matches)) {
            return $matches[1];
        }
        return null;
    }

    protected function toNumberOrNull($val)
    {
        if (is_null($val) || $val === '') return null;
        if (is_numeric($val)) return $val + 0;
        // try to strip non-digits
        $clean = preg_replace('/[^0-9.]/', '', (string)$val);
        return $clean === '' ? null : ($clean + 0);
    }
}
