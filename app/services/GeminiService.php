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

    // ============================================================================
    // UTILITY METHODS (Internal use only)
    // ============================================================================

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

        try {
            $resp = $this->client->post("models/{$model}:generateContent", [
                'query' => ['key' => $this->apiKey],
                'headers' => ['Content-Type' => 'application/json'],
                'json' => $payload,
            ]);
            $json = json_decode((string) $resp->getBody(), true);

            if ($json === null) {
                error_log('Gemini API - Failed to decode JSON response. Status: ' . $resp->getStatusCode());
                return ['error' => 'Invalid JSON response'];
            }

            return $json ?: [];
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            error_log('Gemini API Request Exception: ' . $e->getMessage());
            if ($e->hasResponse()) {
                $errorBody = (string) $e->getResponse()->getBody();
                error_log('Gemini API Error Response: ' . $errorBody);
                $errorJson = json_decode($errorBody, true);
                return $errorJson ?: ['error' => ['message' => $e->getMessage()]];
            }
            return ['error' => ['message' => $e->getMessage()]];
        } catch (\Exception $e) {
            error_log('Gemini API General Exception: ' . $e->getMessage());
            return ['error' => ['message' => $e->getMessage()]];
        }
    }

    private function buildUserContent(string $text): array
    {
        return [
            'role' => 'user',
            'parts' => [['text' => $text]],
        ];
    }

    private function extractText(array $result): string
    {
        // Check for API errors first
        if (isset($result['error'])) {
            error_log('Gemini API Error: ' . json_encode($result['error']));
            return '';
        }

        // Check if candidates exist
        if (!isset($result['candidates']) || !is_array($result['candidates']) || empty($result['candidates'])) {
            error_log('Gemini API Response - No candidates found. Full response: ' . json_encode($result));
            return '';
        }

        $finishReason = $result['candidates'][0]['finishReason'] ?? null;

        // Check for safety ratings that blocked content (but not MAX_TOKENS which is just truncation)
        if ($finishReason && $finishReason !== 'STOP' && $finishReason !== 'MAX_TOKENS') {
            error_log('Gemini API - Content blocked. Finish reason: ' . $finishReason);
            if (isset($result['candidates'][0]['safetyRatings'])) {
                error_log('Safety ratings: ' . json_encode($result['candidates'][0]['safetyRatings']));
            }
            return '';
        }

        // Warn if content was truncated but still extract it
        if ($finishReason === 'MAX_TOKENS') {
            error_log('Gemini API - Response truncated due to MAX_TOKENS limit. Consider increasing maxOutputTokens.');
        }

        // Check for content structure
        if (!isset($result['candidates'][0]['content']['parts'])) {
            error_log('Gemini API Response - Unexpected structure. Full response: ' . json_encode($result));
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

    // ============================================================================
    // SUMMARY PAGE (summary.php)
    // ============================================================================

    /**
     * Generate a summary from source text
     * Used by: Summary page (generateSummary controller method)
     */
    public function generateSummary(string $sourceText, ?string $instructions = null): string
    {
        $model = $this->models['summary'] ?? $this->defaultModel;
        $prompt = "Summarize the following content into a short paragraph.\n\n" . ($instructions ? ("Constraints: " . $instructions . "\n\n") : '') . $sourceText;
        $contents = [$this->buildUserContent($prompt)];
        $result = $this->postGenerate($model, $contents);
        return $this->extractText($result);
    }

    // ============================================================================
    // NOTE PAGE (note.php)
    // ============================================================================

    /**
     * Generate study notes from source text
     * Used by: Note page (generateNotes controller method)
     */
    public function generateNotes(string $sourceText, ?string $instructions = null): string
    {
        $model = $this->models['notes'] ?? $this->defaultModel;
        $prompt = "Create study notes with headings, subpoints, definitions, examples, and key takeaways from the content. Use markdown.\n\n" . ($instructions ? ("Constraints: " . $instructions . "\n\n") : '') . $sourceText;
        $contents = [$this->buildUserContent($prompt)];
        $result = $this->postGenerate($model, $contents);
        return $this->extractText($result);
    }

    // ============================================================================
    // MINDMAP PAGE (mindmap.php)
    // ============================================================================

    /**
     * Generate mindmap markdown from source text
     * Used by: Mindmap page (generateMindmap controller method)
     */
    public function generateMindmapMarkdown(string $sourceText): string
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
        $prompt = $schema . "\n" . "\n\n" . 'Content: ' . $sourceText;
        $contents = [$this->buildUserContent($prompt)];
        $result = $this->postGenerate($model, $contents);
        return $this->extractText($result);
    }

    // ============================================================================
    // FLASHCARD PAGE (flashcard.php)
    // ============================================================================
    public function generateFlashcards(string $sourceText, ?string $instructions = null, ?string $flashcardAmount = null, ?string $flashcardType = null): string
    {
        if ($flashcardAmount == null) {
            $flashcardAmount = 'standard, (10-20 flashcards)';
        }
        if ($flashcardType == null) {
            $flashcardType = 'medium';
        }
        $model = $this->models['flashcards'] ?? $this->defaultModel;
        $schema = <<<PROMPT
        Create a set of flashcards based on the content provided.
        Each flashcard should have a term and definition.
        
        Return in exact JSON format and structure:
            {
                "flashcards": [
                    {
                        "term": "Term",
                        "definition": "Definition"
                    }
                ]
            }

        Output ONLY valid JSON, no markdown, no extra text.

        PROMPT;
        $prompt = $schema . "\n" . ($instructions ? ("Constraints: " . $instructions . "\n\n") : '') . "Flashcard Amount: " . $flashcardAmount . "\n\n" . "Level of Difficulty: " . $flashcardType . "\n\n" . 'Content: ' . $sourceText;
        $contents = [$this->buildUserContent($prompt)];
        $result = $this->postGenerate($model, $contents);
        $output = $this->extractText($result);
        return $this->cleanJsonOutput($output);
    }

    // ============================================================================
    // QUIZ PAGE (quiz.php)
    // ============================================================================
    public function generateMCQ(string $sourceText, ?string $instructions = null, ?string $questionAmount = null, ?string $questionDifficulty = null): string
    {
        if ($questionAmount == null) {
            $questionAmount = 'standard, (10-20 questions)';
        }
        if ($questionDifficulty == null) {
            $questionDifficulty = 'medium';
        }
        $model = $this->models['quiz'] ?? $this->defaultModel;

        // Increase maxOutputTokens for quiz generation to handle multiple questions
        $generationConfig = array_merge($this->generationConfig, [
            'maxOutputTokens' => 8192, // Increased from default 2048 for quiz generation
        ]);

        $schema = <<<PROMPT
        Create a multiple choice question based on the content provided.
        Each question should have a question and answer.
        
        Return in exact JSON format and structure:
            {
                "quiz": [
                    {
                        "question": "Question",
                        "answer": "Answer",
                        "options": [
                            "Option 1",
                            "Option 2",
                            "Option 3",
                            "Option 4"
                        ]
                    }
                ]
            }

        Output ONLY valid JSON, no markdown, no extra text.
        PROMPT;
        $prompt = $schema . "\n" . ($instructions ? ("Constraints: " . $instructions . "\n\n") : '') . "Question Amount: " . $questionAmount . "\n\n" . "Question Difficulty: " . $questionDifficulty . "\n\n" . 'Content: ' . $sourceText;
        $contents = [$this->buildUserContent($prompt)];
        $result = $this->postGenerate($model, $contents, $generationConfig);
        $output = $this->extractText($result);
        return $this->cleanJsonOutput($output);
    }

    // ============================================================================
    // SHARED UTILITY (Used by multiple pages)
    // ============================================================================

    /**
     * Generate a title from context
     * Used by: Summary page, Note page, Mindmap page (for generating titles)
     */
    public function generateTitle(string $titleContext): string
    {
        $model = $this->models['title'] ?? $this->defaultModel;
        $prompt = "Generate a title for the following context. The title should be short and descriptive. Only return the title. No formatting: " . $titleContext;
        $contents = [$this->buildUserContent($prompt)];
        $result = $this->postGenerate($model, $contents);
        return $this->extractText($result);
    }

    private function cleanJsonOutput(string $raw): string
    {
        if (empty($raw)) {
            return '';
        }
        
        $clean = trim($raw);
        
        // Remove markdown code blocks (```json or ```)
        $clean = preg_replace('/^```(?:json)?\s*/m', '', $clean);
        $clean = preg_replace('/```\s*$/m', '', $clean);
        
        // Remove any leading/trailing whitespace
        $clean = trim($clean);
        
        // Try to extract JSON if there's extra text
        // Find the first complete JSON object by counting braces
        $jsonStart = strpos($clean, '{');
        if ($jsonStart !== false) {
            $braceCount = 0;
            $jsonEnd = -1;
            for ($i = $jsonStart; $i < strlen($clean); $i++) {
                if ($clean[$i] === '{') {
                    $braceCount++;
                } elseif ($clean[$i] === '}') {
                    $braceCount--;
                    if ($braceCount === 0) {
                        $jsonEnd = $i;
                        break;
                    }
                }
            }
            
            if ($jsonEnd > $jsonStart) {
                $potentialJson = substr($clean, $jsonStart, $jsonEnd - $jsonStart + 1);
                // Validate it's valid JSON
                if (json_decode($potentialJson) !== null) {
                    $clean = $potentialJson;
                }
            }
        }
        
        // Replace escaped newlines and tabs
        $clean = str_replace(["\\n", "\\t"], ["\n", "\t"], $clean);
        
        // Log if cleaning changed the output significantly
        if ($clean !== trim($raw)) {
            error_log('JSON cleaned. Original length: ' . strlen($raw) . ', Cleaned length: ' . strlen($clean));
        }
        
        return $clean;
    }

    // ============================================================================
    // CHATBOT PAGE (chatbot.php)
    // ============================================================================
    public function generateChatbotResponse(string $sourceText, string $question): string
    {
        $model = $this->models['chatbot'] ?? $this->defaultModel;
        $prompt = "Generate a response to the following question. Review the content provided before generating the response. Only return the response. No formatting: " . 'Question: ' . $question . "\n\n" . 'Content: ' . $sourceText;
        $contents = [$this->buildUserContent($prompt)];
        $result = $this->postGenerate($model, $contents);
        return $this->extractText($result);
    }
}
