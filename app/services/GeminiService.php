<?php

namespace App\Services;

use GuzzleHttp\Client;

class GeminiService
{
    private $client;
    private $apiKey;
    private $baseUrl;
    private $defaultModel;
    private $models;
    private $generationConfig;

    public function __construct()
    {
        $config = require __DIR__ . '/../config/gemini.php';
        $this->apiKey = $config['api_key'];
        $this->baseUrl = rtrim($config['base_url'], '/');
        $this->defaultModel = $config['model'];
        $this->models = $config['models'];
        $this->generationConfig = $config['generation_config'];
        $this->client = new Client([
            'base_uri' => $this->baseUrl . '/',
            'timeout' => 60,
        ]);
    }

    private function requireKey()
    {
        if (!$this->apiKey) {
            throw new \RuntimeException('Missing GEMINI_API_KEY. Set environment variable or app/config/gemini.php');
        }
    }

    private function postGenerate(string $model, array $contents, ?array $generationConfig = null): array
    {
        $this->requireKey();
        $payload = [
            'contents' => $contents,
            'generationConfig' => $generationConfig ?: $this->generationConfig,
        ];
        $resp = $this->client->post("models/{$model}:generateContent", [
            'query' => ['key' => $this->apiKey],
            'headers' => ['Content-Type' => 'application/json'],
            'json' => $payload,
        ]);
        $json = json_decode((string) $resp->getBody(), true);
        return $json ?: [];
    }

    private function buildUserContent(string $text): array
    {
        return [
            'role' => 'user',
            'parts' => [ ['text' => $text] ],
        ];
    }

    public function generateSummary(string $sourceText, ?string $instructions = null): string
    {
        $model = $this->models['summary'] ?? $this->defaultModel;
        $prompt = "Summarize the following content into concise bullet points and a short paragraph.\n\n" . ($instructions ? ("Constraints: " . $instructions . "\n\n") : '') . $sourceText;
        $contents = [ $this->buildUserContent($prompt) ];
        $result = $this->postGenerate($model, $contents);
        return $this->extractText($result);
    }

    public function generateNotes(string $sourceText, ?string $instructions = null): string
    {
        $model = $this->models['notes'] ?? $this->defaultModel;
        $prompt = "Create study notes with headings, subpoints, definitions, examples, and key takeaways from the content. Use markdown.\n\n" . ($instructions ? ("Constraints: " . $instructions . "\n\n") : '') . $sourceText;
        $contents = [ $this->buildUserContent($prompt) ];
        $result = $this->postGenerate($model, $contents);
        return $this->extractText($result);
    }

    public function generateMindmapJson(string $sourceText, ?string $instructions = null): array
    {
        $model = $this->models['mindmap'] ?? $this->defaultModel;
        $schema = <<<PROMPT
You are to produce a mindmap as strict JSON with this shape:
{
  "topic": string,
  "nodes": [
    {"title": string, "children": [ ... recursive same shape ... ]}
  ]
}
No markdown, no code fences, JSON only.
PROMPT;
        $prompt = $schema . "\n" . ($instructions ? ("Constraints: " . $instructions . "\n\n") : '') . $sourceText;
        $contents = [ $this->buildUserContent($prompt) ];
        $result = $this->postGenerate($model, $contents, [
            'temperature' => 0.1,
            'maxOutputTokens' => 2048,
        ]);
        $text = $this->extractText($result);
        $json = json_decode($text, true);
        if (!is_array($json)) {
            // Fallback: wrap as minimal structure
            $json = [
                'topic' => 'Mindmap',
                'nodes' => [ ['title' => substr($text, 0, 100), 'children' => []] ],
            ];
        }
        return $json;
    }

    private function extractText(array $result): string
    {
        if (!isset($result['candidates'][0]['content']['parts'])) {
            return '';
        }
        $parts = $result['candidates'][0]['content']['parts'];
        $buffer = '';
        foreach ($parts as $part) {
            if (isset($part['text'])) {
                $buffer .= $part['text'];
            }
        }
        return $buffer;
    }
}


