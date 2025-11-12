<?php

namespace App\Services;

use Gemini;
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
        $output = $this->generateText($model, $prompt);
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
}