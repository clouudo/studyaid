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
            'parts' => [['text' => $text]],
        ];
    }

    public function generateSummary(string $sourceText, ?string $instructions = null): string
    {
        $model = $this->models['summary'] ?? $this->defaultModel;
        $prompt = "Summarize the following content into a short paragraph.\n\n" . ($instructions ? ("Constraints: " . $instructions . "\n\n") : '') . $sourceText;
        $contents = [$this->buildUserContent($prompt)];
        $result = $this->postGenerate($model, $contents);
        return $this->extractText($result);
    }

    public function generateNotes(string $sourceText, ?string $instructions = null): string
    {
        $model = $this->models['notes'] ?? $this->defaultModel;
        $prompt = "Create study notes with headings, subpoints, definitions, examples, and key takeaways from the content. Use markdown.\n\n" . ($instructions ? ("Constraints: " . $instructions . "\n\n") : '') . $sourceText;
        $contents = [$this->buildUserContent($prompt)];
        $result = $this->postGenerate($model, $contents);
        return $this->extractText($result);
    }

    public function generateMindmapMarkdown(string $sourceText, ?string $instructions = null): string
    {
        $model = $this->models['mindmap'] ?? $this->defaultModel;
        $schema = <<<PROMPT

Create a mindmap in Markdown format optimized for Markmap.js visualization.

Rules:
1. Heading Structure
   * Use # for the main topic, then ##, ###, ####, etc. (no skipped levels).
   * Each idea should be a **heading**, not a paragraph.

2. Style & Formatting
   * Use **bold** for key terms and *italic* for emphasis.
   * Keep headings short and meaningful.
   * Avoid lists (-, *) — use subheadings instead.

3. Content
   * Organize ideas hierarchically (3-5 main sections, each with 2-4 subsections).
   * For structured data, use markdown tables under headings.

4. Output
   * Output only the Markdown (no explanations or extra text).
   * Start directly with #Main Topic.
PROMPT;
        $prompt = $schema . "\n" . ($instructions ? ("Constraints: " . $instructions . "\n\n") : '') . $sourceText;
        $contents = [$this->buildUserContent($prompt)];
        $result = $this->postGenerate($model, $contents);
        return $this->extractText($result);
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

    private function generateTitle(string $titleContext): string
    {
        $model = $this->models['title'] ?? $this->defaultModel;
        $prompt = "Generate a title for the following context: " . $titleContext;
        $contents = [$this->buildUserContent($prompt)];
        $result = $this->postGenerate($model, $contents);
        return $this->extractText($result);
    }
}
