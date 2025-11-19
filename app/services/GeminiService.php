<?php

namespace App\Services;

use Gemini;
use Gemini\Data\GenerationConfig;
use Gemini\Enums\ModelType;

class GeminiService
{
    private $client;
    private $defaultModel;
    private $models;
    private $generationConfig;

    public function __construct()
    {
        $config = require __DIR__ . '/../config/gemini.php';
        $this->client = Gemini::client($config['api_key']);
        $this->defaultModel = $config['model'];
        $this->models = $config['models'];
        $this->generationConfig = $config['generation_config'];
    }

    private function generateText(string $model, string $prompt, ?array $generationConfig = null): string
    {
        try {
            $response = $this->client->generativeModel($model)->generateContent($prompt);
            return $response->text();
        } catch (\Exception $e) {
            error_log('Gemini API - Error generating text: ' . $e->getMessage());
            return '';
        }
    }

    public function formatContent(string $content): string
    {
        $model = $this->models['default'] ?? $this->defaultModel;
        $schema = <<<PROMPT
        Format the following content into organised and structured content. 
        Do not change the content of the original text.
        Do not add any extra text and only organise the content into logical chapters and sections.
        Return the content in markdown format.
        PROMPT;
        $prompt = $schema . "\n\n" . 'Content: ' . $content;
        return $this->generateText($model, $prompt);
    }

    public function generateSummary(string $sourceText, ?string $instructions = null): string
    {
        $model = $this->models['summary'] ?? $this->defaultModel;
        $prompt = "Summarize the following content into a short paragraph.\n\n" . ($instructions ? ("Constraints: " . $instructions . "\n\n") : '') . $sourceText;
        return $this->generateText($model, $prompt);
    }

    public function generateNotes(string $sourceText, ?string $instructions = null): string
    {
        $model = $this->models['notes'] ?? $this->defaultModel;
        $prompt = "Create study notes with headings, subpoints, definitions, examples, and key takeaways from the content. Use markdown.\n\n" . ($instructions ? ("Constraints: " . $instructions . "\n\n") : '') . $sourceText;
        return $this->generateText($model, $prompt);
    }

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
        return $this->generateText($model, $prompt);
    }

    // ============================================================================
    // FLASHCARD PAGE (flashcard.php)
    // ============================================================================
    public function generateFlashcards(string $sourceText, ?string $instructions = null, ?int $flashcardAmount = null, ?string $flashcardType = null): string
    {
        if ($flashcardAmount === null) {
            $flashcardAmount = 15;
        }
        $flashcardAmount = max(1, min(25, $flashcardAmount));
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
        $prompt = $schema . "\n"
            . ($instructions ? ("Constraints: " . $instructions . "\n\n") : '')
            . "Flashcard Amount: " . $flashcardAmount . " flashcards\n\n"
            . "Level of Difficulty: " . $flashcardType . "\n\n"
            . 'Content: ' . $sourceText;
        $output = $this->generateText($model, $prompt);
        return $this->cleanJsonOutput($output);
    }

    // ============================================================================
    // QUIZ PAGE (quiz.php)
    // ============================================================================
    public function generateMixedQuiz(
        string $sourceText,
        array $distribution,
        int $totalQuestions,
        string $questionDifficulty = 'medium',
        ?string $instructions = null
    ): string {
        $model = $this->models['quiz'] ?? $this->defaultModel;
        $generationConfig = array_merge($this->generationConfig, [
            'maxOutputTokens' => 8192,
        ]);

        $typeBreakdown = [];
        foreach ($distribution as $type => $count) {
            if ($count > 0) {
                $label = ucfirst(str_replace('_', ' ', $type));
                $typeBreakdown[] = "{$label}: {$count}";
            }
        }
        $typeLines = implode("\n", $typeBreakdown);

        $schema = <<<PROMPT
You are an educational quiz generator. Create {$totalQuestions} questions using the provided document content.
Match this distribution:
{$typeLines}

Question type rules:
- multiple_choice: Provide exactly 4 options array, answer must match one option text.
- checkbox: Provide 4 options, answer must be an array of the correct option texts (multiple allowed).
- true_false: Provide two options (True, False) and answer must be "True" or "False".
- short_answer: Open-ended response 1-2 sentences, include concise answer field.
- long_answer: Paragraph-style answer 3-5 sentences, include concise answer summary.

Return JSON ONLY in this structure:
{
  "quiz": [
    {
      "type": "multiple_choice|checkbox|true_false|short_answer|long_answer",
      "question": "string",
      "options": ["A","B","C","D"],   // only for multiple_choice, checkbox, true_false
      "answer": "string or array",
      "explanation": "short reasoning for the answer"
    }
  ]
}
PROMPT;

        $prompt = $schema . "\n"
            . "Question Difficulty: {$questionDifficulty}\n"
            . "Total Questions: {$totalQuestions}\n"
            . ($instructions ? ("Constraints: {$instructions}\n") : '')
            . "\nContent:\n{$sourceText}";

        $contents = [$this->buildUserContent($prompt)];
        $result = $this->postGenerate($model, $contents, $generationConfig);
        $output = $this->extractText($result);
        return $this->cleanJsonOutput($output);
    }

    public function generateMCQ(string $sourceText, ?string $instructions = null, ?string $questionAmount = null, ?string $questionDifficulty = null): string
    {
        if ($questionAmount == null) {
            $questionAmount = 'standard, (10-20 questions)';
        }
        if ($questionDifficulty == null) {
            $questionDifficulty = 'medium';
        }
        $model = $this->models['quiz'] ?? $this->defaultModel;

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
        $output = $this->generateText($model, $prompt);
        return $this->cleanJsonOutput($output);
    }

    public function evaluateOpenAnswer(
        string $question,
        string $expectedAnswer,
        string $userAnswer,
        string $type = 'short_answer',
        string $difficulty = 'medium'
    ): array {
        $model = $this->models['quiz'] ?? $this->defaultModel;
        if (trim($userAnswer) === '') {
            return [
                'score' => 0,
                'isCorrect' => false,
                'suggestion' => 'No answer provided.'
            ];
        }

        $prompt = <<<PROMPT
You are grading a student's response for a {$type} question (difficulty: {$difficulty}).
Compare the student's answer with the expected answer. Provide:
- score: value between 0 and 1 (two decimal places) representing correctness.
- isCorrect: true if score >= 0.6, false otherwise.
- suggestion: short constructive feedback referencing missing information or improvements. If answer is excellent, acknowledge it.

Return JSON ONLY:
{
  "score": 0.0,
  "isCorrect": true,
  "suggestion": "string"
}

Question: {$question}
Expected Answer: {$expectedAnswer}
Student Answer: {$userAnswer}
PROMPT;

        $contents = [$this->buildUserContent($prompt)];
        $result = $this->postGenerate($model, $contents);
        $output = $this->extractText($result);
        $clean = $this->cleanJsonOutput($output);
        $decoded = json_decode($clean, true);
        if (!is_array($decoded)) {
            return [
                'score' => 0,
                'isCorrect' => false,
                'suggestion' => 'Unable to evaluate answer automatically.'
            ];
        }
        return [
            'score' => isset($decoded['score']) ? (float)$decoded['score'] : 0,
            'isCorrect' => isset($decoded['isCorrect']) ? (bool)$decoded['isCorrect'] : false,
            'suggestion' => $decoded['suggestion'] ?? ''
        ];
    }
    public function generateShortQuestion(string $sourceText, ?string $instructions = null, ?string $questionAmount = null, ?string $questionDifficulty = null){
        if ($questionAmount == null) {
            $questionAmount = 'standard, (10-20 questions)';
        }
        if ($questionDifficulty == null) {
            $questionDifficulty = 'medium';
        }
        $model = $this->models['quiz'] ?? $this->defaultModel;

        $prompt = <<<PROMPT
        Create a short question based on the content provided.
        Each question should have a question and answer.
        
        Return in exact JSON format and structure:
            {
                "quiz": [
                    {
                        "question": "Question",
                        "answer": "Answer"
                    }
                ]
            }

        Output ONLY valid JSON, no markdown, no extra text.
        PROMPT;
        $prompt = $prompt . "\n" . ($instructions ? ("Constraints: " . $instructions . "\n\n") : '') . "Question Amount: " . $questionAmount . "\n\n" . "Question Difficulty: " . $questionDifficulty . "\n\n" . 'Content: ' . $sourceText;
        $output = $this->generateText($model, $prompt);
        return $this->cleanJsonOutput($output);
    }

    public function generateTitle(string $titleContext): string
    {
        $model = $this->models['title'] ?? $this->defaultModel;
        $prompt = "Generate a title for the following context. The title should be short and descriptive. Only return the title. No formatting: " . $titleContext;
        return $this->generateText($model, $prompt);
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
                if (json_decode($potentialJson) !== null) {
                    $clean = $potentialJson;
                }
            }
        }

        $clean = str_replace(["\\n", "\\t"], ["\n", "\t"], $clean);

        if ($clean !== trim($raw)) {
            error_log('JSON cleaned. Original length: ' . strlen($raw) . ', Cleaned length: ' . strlen($clean));
        }

        return $clean;
    }

    public function generateChatbotResponse(string $sourceText, string $question, ?string $chatHistory = null): string
    {
        $model = $this->models['chatbot'] ?? $this->defaultModel;

        $prompt = "Generate a helpful and accurate response to the user's question using the provided content. "
            . "If relevant, use information from the chat history for better context. "
            . "Only return the response. No formatting.\n\n"
            . "Question: {$question}\n\n"
            . "Content: {$sourceText}\n\n";

        if ($chatHistory != null) {
            $prompt .= "Chat History:\n{$chatHistory}\n\n";
        }

        return $this->generateText($model, $prompt);
    }

    public function compressChatHistory(array $questionChats, array $responseChats): string
    {
        $model = $this->models['chatbot'] ?? $this->defaultModel;
        $message = '';

        foreach ($questionChats as $i => $question) {
            $message .= "User: {$question}\nAssistant: " . ($responseChats[$i] ?? 'No response found') . "\n\n";
        }

        $prompt = <<<PROMPT
Compress the following chat history into a concise summary. 
Focus on key questions, decisions, and factual context. 
Avoid repetition and do not include formatting, headers, or markdown.

Chat history:
$message
PROMPT;
        return $this->generateText($model, $prompt);
    }

    public function generateEmbedding(string $text): array
    {
        $model = $this->models['embedding'] ?? 'text-embedding-004';
        try {
            $response = $this->client->embeddingModel($model)->embedContent($text);
            return $response->embedding->values;
        } catch (\Exception $e) {
            error_log('Gemini API - Error generating embedding: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Build user content from text string
     * @param string $text
     * @return string
     */
    private function buildUserContent(string $text): string
    {
        return $text;
    }

    /**
     * Generate content with custom generation config
     * @param string $model
     * @param array $contents
     * @param array|null $generationConfig
     * @return mixed
     */
    private function postGenerate(string $model, array $contents, ?array $generationConfig = null)
    {
        try {
            $generativeModel = $this->client->generativeModel($model);
            
            // Merge with default generation config if provided
            if ($generationConfig !== null) {
                $mergedConfig = array_merge($this->generationConfig ?? [], $generationConfig);
                $config = new GenerationConfig(
                    candidateCount: 1,
                    stopSequences: [],
                    maxOutputTokens: $mergedConfig['maxOutputTokens'] ?? null,
                    temperature: $mergedConfig['temperature'] ?? null,
                    topP: $mergedConfig['topP'] ?? null,
                    topK: $mergedConfig['topK'] ?? null,
                );
                $generativeModel = $generativeModel->withGenerationConfig($config);
            }
            
            // Generate content - contents array should contain strings
            $response = $generativeModel->generateContent(...$contents);
            return $response;
        } catch (\Exception $e) {
            error_log('Gemini API - Error generating content: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Extract text from GenerateContentResponse
     * @param mixed $response
     * @return string
     */
    private function extractText($response): string
    {
        try {
            if (method_exists($response, 'text')) {
                return $response->text();
            }
            return '';
        } catch (\Exception $e) {
            error_log('Gemini API - Error extracting text: ' . $e->getMessage());
            return '';
        }
    }
}