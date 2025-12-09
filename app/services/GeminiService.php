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
    private $rateLimiter;
    private $config;

    public function __construct()
    {
        $config = require __DIR__ . '/../config/gemini.php';
        $this->config = $config;
        $this->client = Gemini::client($config['api_key']);
        $this->defaultModel = $config['model'];
        $this->models = $config['models'];
        $this->generationConfig = $config['generation_config'];
        $this->rateLimiter = new RateLimiter();
    }

    /**
     * Clean and validate UTF-8 text
     * 
     * Removes invalid UTF-8 sequences and ensures text is properly encoded
     * 
     * @param string $text Text to clean
     * @return string Cleaned UTF-8 text
     */
    private function cleanUtf8(string $text): string
    {
        if (empty($text)) {
            return '';
        }

        // Remove null bytes and other control characters (except newlines, tabs, carriage returns)
        $text = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $text);

        // Check if text is valid UTF-8
        if (!mb_check_encoding($text, 'UTF-8')) {
            // Try to convert from various encodings
            $encodings = ['Windows-1252', 'ISO-8859-1', 'UTF-8'];
            foreach ($encodings as $encoding) {
                if (mb_check_encoding($text, $encoding)) {
                    $text = mb_convert_encoding($text, 'UTF-8', $encoding);
                    break;
                }
            }
            
            // If still invalid, use filter_var to remove invalid sequences
            $text = filter_var($text, FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH);
            
            // Final cleanup: remove any remaining invalid UTF-8 sequences
            $text = mb_convert_encoding($text, 'UTF-8', 'UTF-8');
        }

        // Remove invalid UTF-8 sequences using regex
        $text = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $text);
        
        // Remove replacement characters that indicate encoding issues
        $text = str_replace(["\xEF\xBF\xBD", "\xFF\xFD"], '', $text);
        
        // Normalize line endings
        $text = preg_replace('/\r\n|\r/', "\n", $text);
        
        // Trim and return
        return trim($text);
    }

    /**
     * Generate text with rate limiting, retry logic, and overload prevention
     * 
     * @param string $model Model name
     * @param string $prompt Prompt text
     * @param array|null $generationConfig Generation configuration
     * @param int|null $userId User ID for rate limiting (optional)
     * @return string Generated text
     * @throws \RuntimeException On API errors or rate limit exceeded
     */
    private function generateText(string $model, string $prompt, ?array $generationConfig = null, ?int $userId = null): string
    {
        // Clean UTF-8 encoding before processing
        $prompt = $this->cleanUtf8($prompt);
        
        // Validate prompt size (prevent oversized requests)
        $maxPromptLength = 1000000; // ~1M characters
        if (strlen($prompt) > $maxPromptLength) {
            throw new \RuntimeException('Prompt too large. Maximum length: ' . $maxPromptLength . ' characters.');
        }
        
        // Ensure prompt is not empty after cleaning
        if (empty(trim($prompt))) {
            throw new \RuntimeException('Prompt is empty or contains only invalid characters.');
        }

        // Rate limiting check
        if ($userId !== null) {
            $rateCheck = $this->rateLimiter->checkRateLimit($userId);
            if (!$rateCheck['allowed']) {
                $waitTime = $rateCheck['wait_time'];
                $reason = $rateCheck['reason'] === 'minute_limit' ? 'per minute' : 'per hour';
                throw new \RuntimeException(
                    "Rate limit exceeded ({$reason}). Please wait {$waitTime} seconds. " .
                    "Current: {$rateCheck['current']}/{$rateCheck['limit']} requests."
                );
            }

            // Check concurrent request limit
            if (!$this->rateLimiter->checkConcurrentLimit($userId)) {
                throw new \RuntimeException('Too many concurrent requests. Please wait for current requests to complete.');
            }

            // Acquire lock for concurrent tracking
            $lockFile = $this->rateLimiter->acquireLock($userId);
            if (!$lockFile) {
                throw new \RuntimeException('Unable to acquire request lock. Please try again.');
            }
        }

        $maxRetries = $this->config['rate_limiting']['max_retries'] ?? 3;
        $retryDelay = $this->config['rate_limiting']['retry_delay'] ?? 2;
        $lastException = null;

        // Retry logic with exponential backoff
        for ($attempt = 0; $attempt <= $maxRetries; $attempt++) {
            try {
                // Delay between calls (except first attempt)
                if ($attempt > 0) {
                    $delay = $retryDelay * pow(2, $attempt - 1); // Exponential backoff
                    sleep($delay);
                } else if ($userId !== null) {
                    // Small delay between calls for rate limiting
                    $this->rateLimiter->delay();
                }

                $generativeModel = $this->client->generativeModel($model);
                
                // Apply generation config if provided
                if ($generationConfig !== null) {
                    $config = new GenerationConfig(
                        candidateCount: 1,
                        stopSequences: [],
                        maxOutputTokens: $generationConfig['maxOutputTokens'] ?? null,
                        temperature: $generationConfig['temperature'] ?? null,
                        topP: $generationConfig['topP'] ?? null,
                        topK: $generationConfig['topK'] ?? null
                    );
                    $generativeModel = $generativeModel->withGenerationConfig($config);
                }
                
                $response = $generativeModel->generateContent($prompt);
                $result = $response->text();

                // Record successful request
                if ($userId !== null) {
                    $this->rateLimiter->recordRequest($userId);
                    if (isset($lockFile)) {
                        $this->rateLimiter->releaseLock($lockFile);
                    }
                }

                return $result;
            } catch (\Exception $e) {
                $lastException = $e;
                $errorMessage = $e->getMessage();
                
                // Check if it's a rate limit error from API
                if (stripos($errorMessage, 'rate limit') !== false || 
                    stripos($errorMessage, '429') !== false ||
                    stripos($errorMessage, 'quota') !== false) {
                    // Rate limit error - wait longer before retry
                    if ($attempt < $maxRetries) {
                        $waitTime = $retryDelay * pow(2, $attempt + 1);
                        error_log("Gemini API - Rate limit hit, waiting {$waitTime} seconds before retry " . ($attempt + 1));
                        sleep($waitTime);
                        continue;
                    }
                }

                // Check if it's a UTF-8 encoding error
                if (stripos($errorMessage, 'UTF-8') !== false || 
                    stripos($errorMessage, 'malformed') !== false ||
                    stripos($errorMessage, 'encoding') !== false ||
                    stripos($errorMessage, 'incorrectly encoded') !== false) {
                    // UTF-8 error - try cleaning the prompt and retry once
                    if ($attempt === 0) {
                        error_log("Gemini API - UTF-8 encoding error detected, cleaning prompt and retrying");
                        $prompt = $this->cleanUtf8($prompt);
                        continue;
                    } else {
                        // Already retried, throw error
                        error_log("Gemini API - UTF-8 encoding error persists after cleaning");
                        throw new \RuntimeException(
                            'Text contains invalid UTF-8 characters that could not be cleaned. ' .
                            'Please ensure your document uses valid UTF-8 encoding.',
                            0,
                            $e
                        );
                    }
                }

                // Check if it's a temporary error (5xx)
                if (stripos($errorMessage, '500') !== false || 
                    stripos($errorMessage, '503') !== false ||
                    stripos($errorMessage, '502') !== false) {
                    // Temporary server error - retry
                    if ($attempt < $maxRetries) {
                        error_log("Gemini API - Temporary error, retrying: " . $errorMessage);
                        continue;
                    }
                }

                // For other errors, don't retry
                break;
            }
        }

        // Release lock on failure
        if ($userId !== null && isset($lockFile)) {
            $this->rateLimiter->releaseLock($lockFile);
        }

        error_log('Gemini API - Error generating text after ' . ($attempt + 1) . ' attempts: ' . ($lastException ? $lastException->getMessage() : 'Unknown error'));
        throw new \RuntimeException('Gemini API Error: ' . ($lastException ? $lastException->getMessage() : 'Unknown error'), 0, $lastException);
    }

    public function formatContent(string $content, ?int $userId = null): string
    {
        $model = $this->models['default'] ?? $this->defaultModel;
        $schema = <<<PROMPT
Format the following content into well-structured and logically organized chapters and sections.
Preserve all original content exactly as-is.
Do not include any additional explanations or text.
Organize hierarchically using headings and subheadings where appropriate.
Output should be plain text formatted with markdown-style headers such as #, ##, ###, etc.
PROMPT;
        $prompt = $schema . "\n\n" . 'Content: ' . $content;
        return $this->generateText($model, $prompt, null, $userId);
    }

    public function generateSummary(string $sourceText, ?string $instructions = null, ?int $userId = null): string
    {
        $model = $this->models['summary'] ?? $this->defaultModel;
        $prompt = "Summarize the following content into 3 short paragraphs.\n\n" . ($instructions ? ("Constraints: " . $instructions . "\n\n") : '') . $sourceText;
        return $this->generateText($model, $prompt, null, $userId);
    }

    public function generateNotes(string $sourceText, ?string $instructions = null, ?int $userId = null): string
    {
        $model = $this->models['notes'] ?? $this->defaultModel;
        $prompt = "Create study notes with headings, subpoints, definitions, examples, and key takeaways from the content. Use markdown. Notes should be concise and short.\n\n" . ($instructions ? ("Constraints: " . $instructions . "\n\n") : '') . $sourceText;
        return $this->generateText($model, $prompt, null, $userId);
    }

    public function generateMindmapMarkdown(string $sourceText): string
    {
        $model = $this->models['mindmap'] ?? $this->defaultModel;
        $schema = <<<PROMPT
        Create a simple and concise mindmap in Markdown format optimized for Markmap.js visualization.
        
        Rules:
        
        1. **Structure**
           * Use # for main topic, then only ## and ### (maximum 3 levels).
           * No more than 5 main branches (##).
           * Each main branch should have 1–3 sub-branches (###).
        
        2. **Formatting**
           * Write short, clear headings (< 6 words if possible).
           * Use **bold** for key terms.
           * Avoid paragraphs or long sentences — keep statements minimal.
        
        3. **Content**
           * Focus on core ideas, do not add extended explanations.
           * Replace bulleted lists with hierarchical subheadings.
           * For comparisons or structured data, use inline text instead of tables.
        
        4. **Length**
           * Keep total number of nodes below 25.
           * Aim for ~10–15 nodes for brevity.
        
        5. **Output Format**
           * Return only valid Markdown with no extra text before/after.
           * Start directly with # [Main Topic].
        PROMPT;
        $prompt = $schema . "\n\n" . 'Content: ' . $sourceText;
        $markdown = $this->generateText($model, $prompt);
        
        return $this->cleanMarkdownOutput($markdown);
    }

    /**
     * Clean markdown output by removing code fences and ensuring valid format
     */
    private function cleanMarkdownOutput(string $markdown): string
    {
        $markdown = trim($markdown);
        
        // Remove markdown code fences if present
        $markdown = preg_replace('/^```(?:markdown)?\s*/m', '', $markdown);
        $markdown = preg_replace('/```\s*$/m', '', $markdown);
        $markdown = trim($markdown);
        
        // Ensure it starts with a heading
        if (!preg_match('/^#+\s+/m', $markdown)) {
            $markdown = "# Mindmap\n\n" . $markdown;
        }
        
        return $markdown;
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
        $bloomLevels = 'remember',
        ?string $instructions = null
    ): string {
        $model = $this->models['quiz'] ?? $this->defaultModel;
        $generationConfig = array_merge($this->generationConfig, [
            'maxOutputTokens' => 8192,
        ]);

        // Map Bloom's taxonomy level to description
        $bloomDescriptions = [
            'remember' => 'Remember: Questions should test recall of facts, terms, basic concepts, and definitions. Focus on memorization and recognition.',
            'understand' => 'Understand: Questions should test comprehension, interpretation, explanation, and classification of concepts.',
            'apply' => 'Apply: Questions should test the ability to use information in new situations, solve problems, and implement procedures.',
            'analysis' => 'Analysis: Questions should test the ability to break down information, identify relationships, compare and contrast, and analyze structure.',
            'evaluate' => 'Evaluate: Questions should test the ability to justify decisions, critique arguments, assess value, and make judgments.',
            'create' => 'Create: Questions should test the ability to produce new or original work, design solutions, construct theories, and synthesize information.'
        ];

        // Handle both legacy single string and new per-type array format
        $isPerTypeBloom = is_array($bloomLevels);
        
        $typeBreakdown = [];
        foreach ($distribution as $type => $count) {
            if ($count > 0) {
                $label = ucfirst(str_replace('_', ' ', $type));
                if ($isPerTypeBloom && isset($bloomLevels[$type])) {
                    $level = $bloomLevels[$type];
                    $levelLabel = ucfirst($level);
                    $typeBreakdown[] = "{$label}: {$count} questions at {$levelLabel} level";
                } else {
                    $typeBreakdown[] = "{$label}: {$count}";
                }
            }
        }
        $typeLines = implode("\n", $typeBreakdown);

        // Build Bloom's taxonomy instructions
        $bloomInstructions = "";
        if ($isPerTypeBloom) {
            $bloomInstructions = "Bloom's Taxonomy Levels per Question Type:\n";
            foreach ($distribution as $type => $count) {
                if ($count > 0) {
                    $level = $bloomLevels[$type] ?? 'remember';
                    $label = ucfirst(str_replace('_', ' ', $type));
                    $bloomInstructions .= "- {$label}: {$bloomDescriptions[$level]}\n";
                }
            }
        } else {
            // Legacy single level for all questions
            $bloomDescription = $bloomDescriptions[$bloomLevels] ?? $bloomDescriptions['remember'];
            $bloomInstructions = "Bloom's Taxonomy Level: {$bloomDescription}\n";
        }

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
            . $bloomInstructions
            . "Total Questions: {$totalQuestions}\n"
            . ($instructions ? ("Constraints: {$instructions}\n") : '')
            . "\nContent:\n{$sourceText}";

        $output = $this->generateText($model, $prompt, $generationConfig);
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
        string $bloomLevel = 'remember'
    ): array {
        $model = $this->models['quiz'] ?? $this->defaultModel;
        if (trim($userAnswer) === '') {
            return [
                'score' => 0,
                'isCorrect' => false,
                'suggestion' => 'No answer provided.'
            ];
        }

        // Map Bloom's taxonomy level to description
        $bloomDescriptions = [
            'remember' => 'Remember: Evaluate based on recall of facts, terms, basic concepts, and definitions.',
            'understand' => 'Understand: Evaluate based on comprehension, interpretation, explanation, and classification.',
            'apply' => 'Apply: Evaluate based on ability to use information in new situations and solve problems.',
            'analysis' => 'Analysis: Evaluate based on ability to break down information, identify relationships, and analyze structure.',
            'evaluate' => 'Evaluate: Evaluate based on ability to justify decisions, critique arguments, and make judgments.',
            'create' => 'Create: Evaluate based on ability to produce new or original work and synthesize information.'
        ];
        $bloomDescription = $bloomDescriptions[$bloomLevel] ?? $bloomDescriptions['remember'];

        $prompt = <<<PROMPT
You are grading a student's response for a {$type} question.
Bloom's Taxonomy Level: {$bloomDescription}

Compare the student's answer with the expected answer. Provide:
- score: value between 0 and 1 (two decimal places) representing correctness based on the Bloom's taxonomy level.
- isCorrect: true if score >= 0.6, false otherwise.
- suggestion: Provide constructive feedback that:
  1. References the expected answer content
  2. Points out what is missing or could be improved
  3. If the answer is excellent, acknowledge it
  4. Be specific and helpful for learning

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

        $output = $this->generateText($model, $prompt);
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

    /**
     * Evaluates quiz answers by comparing user answers with correct answers
     * @param array $userAnswers Array of user answers indexed by question ID
     * @param array $questions Array of questions with their correct answers and options
     * @return array Feedback with score, feedback text, and suggestions
     */
    public function evaluateAnswers(array $userAnswers, array $questions): array
    {
        $totalQuestions = count($questions);
        $correctCount = 0;
        $feedbackItems = [];
        $suggestions = [];
        $results = []; // Structured results for frontend

        foreach ($questions as $question) {
            $questionId = $question['questionID'];
            $questionType = strtolower($question['type'] ?? 'multiple_choice');
            $userAnswer = $userAnswers[$questionId] ?? null;
            $correctAnswer = $question['answer'] ?? '';

            $isCorrect = false;
            $suggestion = '';

            if ($userAnswer === null || $userAnswer === '') {
                $feedbackItems[] = "Question {$questionId}: No answer provided.";
                $suggestions[] = "Question {$questionId}: Please review the question and provide an answer.";
                $suggestion = "Please review the question and provide an answer.";
                
                // Add to results array
                $results[] = [
                    'questionID' => $questionId,
                    'question' => $question['question'] ?? '',
                    'type' => $questionType,
                    'userAnswer' => null,
                    'correctAnswer' => is_array($correctAnswer) ? $correctAnswer : [$correctAnswer],
                    'isCorrect' => false,
                    'suggestion' => $suggestion,
                    'options' => $question['options'] ?? []
                ];
                continue;
            }

            // Evaluate based on question type
            switch ($questionType) {
                case 'multiple_choice':
                case 'true_false':
                    // Normalize both answers for comparison (trim, lowercase for case-insensitive)
                    $normalizedUserAnswer = trim(strtolower((string)$userAnswer));
                    $normalizedCorrectAnswer = trim(strtolower((string)$correctAnswer));
                    $isCorrect = ($normalizedUserAnswer === $normalizedCorrectAnswer);
                    
                    // Log for debugging if incorrect
                    if (!$isCorrect) {
                        $userAnswerStr = is_array($userAnswer) ? json_encode($userAnswer) : (string)$userAnswer;
                        $correctAnswerStr = is_array($correctAnswer) ? json_encode($correctAnswer) : (string)$correctAnswer;
                        error_log("[Quiz Evaluation] MCQ mismatch - Question ID: {$questionId}, User: '{$userAnswerStr}', Correct: '{$correctAnswerStr}'");
                        $suggestion = "Review the correct answer: " . (is_array($correctAnswer) ? implode(', ', $correctAnswer) : $correctAnswer);
                    }
                    break;

                case 'checkbox':
                    // For checkbox, user answer is an array, correct answer might be JSON
                    $userAnswerArray = is_array($userAnswer) ? $userAnswer : json_decode($userAnswer, true);
                    $correctAnswerArray = is_array($correctAnswer) ? $correctAnswer : json_decode($correctAnswer, true);
                    
                    if (is_array($userAnswerArray) && is_array($correctAnswerArray)) {
                        sort($userAnswerArray);
                        sort($correctAnswerArray);
                        $isCorrect = ($userAnswerArray === $correctAnswerArray);
                    } else {
                        $isCorrect = false;
                    }
                    
                    if (!$isCorrect) {
                        $suggestion = "Review the correct answer(s): " . (is_array($correctAnswer) ? implode(', ', $correctAnswer) : $correctAnswer);
                    }
                    break;

                case 'short_answer':
                case 'long_answer':
                    // Use AI evaluation for open-ended questions
                    // Get Bloom's taxonomy level from question config or default to 'remember'
                    $bloomLevel = $question['bloomLevel'] ?? 'remember';
                    // Convert correctAnswer to string if it's an array
                    $correctAnswerStr = is_array($correctAnswer) ? implode(' ', $correctAnswer) : (string)$correctAnswer;
                    $evaluation = $this->evaluateOpenAnswer(
                        $question['question'] ?? '',
                        $correctAnswerStr,
                        is_array($userAnswer) ? implode(' ', $userAnswer) : (string)$userAnswer,
                        $questionType,
                        $bloomLevel
                    );
                    $isCorrect = $evaluation['isCorrect'];
                    // Always include suggestion for short/long answer questions (even if correct, for feedback)
                    $suggestion = $evaluation['suggestion'] ?? '';
                    // Add to suggestions array for both correct and incorrect answers
                    if (!empty($suggestion)) {
                        $suggestions[] = "Question {$questionId}: " . $suggestion;
                    }
                    break;

                default:
                    $isCorrect = (trim($userAnswer) === trim($correctAnswer));
                    if (!$isCorrect) {
                        $suggestion = "Review the correct answer: " . (is_array($correctAnswer) ? implode(', ', $correctAnswer) : $correctAnswer);
                    }
            }

            if ($isCorrect) {
                $correctCount++;
                $feedbackItems[] = "Question {$questionId}: Correct!";
            } else {
                // Convert correct answer to string for display (handles arrays for checkbox)
                $correctAnswerDisplay = is_array($correctAnswer) 
                    ? implode(', ', $correctAnswer) 
                    : (string)$correctAnswer;
                $feedbackItems[] = "Question {$questionId}: Incorrect. Expected: {$correctAnswerDisplay}";
                if ($questionType !== 'short_answer' && $questionType !== 'long_answer') {
                    $suggestions[] = "Question {$questionId}: Review the correct answer: {$correctAnswerDisplay}";
                }
            }
            
            // Add structured result for frontend
            $results[] = [
                'questionID' => $questionId,
                'question' => $question['question'] ?? '',
                'type' => $questionType,
                'userAnswer' => is_array($userAnswer) ? $userAnswer : [$userAnswer],
                'correctAnswer' => is_array($correctAnswer) ? $correctAnswer : [$correctAnswer],
                'isCorrect' => $isCorrect,
                'suggestion' => $suggestion,
                'options' => $question['options'] ?? [],
                'explanation' => $question['explanation'] ?? ''
            ];
        }

        $score = $totalQuestions > 0 ? ($correctCount / $totalQuestions) * 100 : 0;
        $percentage = round($score, 2);

        return [
            'score' => $percentage,
            'percentage' => $percentage, // For frontend compatibility
            'feedback' => implode("\n", $feedbackItems),
            'suggestions' => implode("\n", $suggestions),
            'correctCount' => $correctCount,
            'totalQuestions' => $totalQuestions,
            'results' => $results // Structured results array for frontend
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

        $prompt = "You are a helpful assistant answering questions based on the provided document content. "
            . "Answer the user's question using ONLY the information from the provided content. "
            . "If the content doesn't contain enough information to answer the question, say so clearly. "
            . "Do not make up information or use knowledge outside the provided content.\n\n"
            . "Question: {$question}\n\n"
            . "Relevant Content from Document:\n{$sourceText}\n\n";

        if ($chatHistory != null) {
            $prompt .= "Previous Conversation Context:\n{$chatHistory}\n\n";
        }

        $prompt .= "Answer:";

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
        // Validate input
        if (empty(trim($text))) {
            error_log('Gemini API - Error generating embedding: Empty text provided');
            return [];
        }

        // Check if API key is configured
        $config = require __DIR__ . '/../config/gemini.php';
        if (empty($config['api_key'])) {
            error_log('Gemini API - Error generating embedding: API key not configured');
            return [];
        }

        $model = $this->models['embedding'] ?? 'text-embedding-004';
        
        try {
            $response = $this->client->embeddingModel($model)->embedContent($text);
            
            // Check if response and embedding exist
            if (!$response || !isset($response->embedding)) {
                error_log('Gemini API - Error generating embedding: Invalid response structure');
                error_log('Gemini API - Response type: ' . gettype($response));
                if ($response) {
                    error_log('Gemini API - Response class: ' . get_class($response));
                }
                return [];
            }
            
            $values = $response->embedding->values ?? [];
            
            // Validate that we got actual embedding values
            if (empty($values) || !is_array($values)) {
                error_log('Gemini API - Error generating embedding: No embedding values returned');
                error_log('Gemini API - Values type: ' . gettype($values));
                error_log('Gemini API - Values count: ' . (is_array($values) ? count($values) : 'N/A'));
                return [];
            }
            
            return $values;
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();
            
            // Check for UTF-8 encoding errors
            if (stripos($errorMessage, 'UTF-8') !== false || 
                stripos($errorMessage, 'malformed') !== false ||
                stripos($errorMessage, 'encoding') !== false) {
                error_log('Gemini API - UTF-8 encoding error in embedding generation. Text length: ' . strlen($text));
                error_log('Gemini API - First 200 chars: ' . substr($text, 0, 200));
            }
            
            error_log('Gemini API - Error generating embedding: ' . $errorMessage);
            error_log('Gemini API - Exception class: ' . get_class($e));
            error_log('Gemini API - Stack trace: ' . $e->getTraceAsString());
            return [];
        } catch (\Throwable $e) {
            error_log('Gemini API - Fatal error generating embedding: ' . $e->getMessage());
            error_log('Gemini API - Exception class: ' . get_class($e));
            return [];
        }
    }

    public function synthesizeDocument(string $sourceText, string $instructions): string
    {
        $model = $this->models['synthesize'] ?? $this->defaultModel;
        
        // Increased output capacity for synthesize document (supports up to 5 documents)
        $generationConfig = array_merge($this->generationConfig, [
            'maxOutputTokens' => 32768, // Increased from 8192 for larger synthesized documents
        ]);
        
        $prompt = $instructions . "\n\n"
            . "IMPORTANT: Use ONLY the following relevant content retrieved from the selected documents. "
            . "This content has been selected because it matches your query. "
            . "Base your synthesized document strictly on this content and do not include information not found in it.\n\n"
            . "Relevant Content:\n{$sourceText}\n\n"
            . "Synthesize the document now:";
        return $this->generateText($model, $prompt, $generationConfig);
    }

    /**
     * Answer homework question from extracted text or image
     * Returns array with 'hasQuestion' (bool) and 'answer' (string)
     */
    public function answerHomeworkQuestion(string $extractedText, ?string $instructions = null): array
    {
        $model = $this->models['default'] ?? $this->defaultModel;
        
        // Build instruction section if provided
        $userInstructions = '';
        if (!empty($instructions)) {
            $userInstructions = "\n\nUSER INSTRUCTIONS (follow these specific instructions):\n{$instructions}\n";
        }
        
        $prompt = <<<PROMPT
You are a helpful homework assistant. Analyze the following content from an uploaded document (image or PDF).

YOUR TASK:
1. First, carefully examine the content to identify if there is a question, problem, or exercise to solve.
2. If NO question or problem is found in the content, respond with EXACTLY: "NO_QUESTION_FOUND"
3. If a question or problem IS found:
   - Extract and clearly state the question/problem
   - Provide a detailed, step-by-step answer
   - Explain your reasoning thoroughly
   - Use clear formatting with markdown
   - Be thorough but concise
{$userInstructions}
CONTENT TO ANALYZE:
{$extractedText}

RESPONSE FORMAT:
- If no question found: respond ONLY with "NO_QUESTION_FOUND"
- If question found, use this format:

### Question:
[State the question or problem clearly]

### Answer:
[Provide your detailed answer with step-by-step explanation]

Now analyze and respond:
PROMPT;

        try {
            $response = $this->generateText($model, $prompt);
            
            // Check if no question was found
            if (stripos($response, 'NO_QUESTION_FOUND') !== false || 
                stripos($response, 'no question found') !== false ||
                (stripos($response, 'no question') !== false && stripos($response, 'no question') < 50)) {
                return [
                    'hasQuestion' => false,
                    'answer' => 'No question found in the uploaded document. Please upload a document that contains questions or problems to solve.',
                    'question' => null
                ];
            }
            
            // Extract question if possible (look for patterns like "Question:", "Problem:", etc.)
            $question = null;
            // Try to extract from AI response first
            if (preg_match('/###?\s*Question[:\s]*\n?(.+?)(?=###?\s*Answer|$)/is', $response, $matches)) {
                $question = trim($matches[1]);
            }
            // Fallback: try to extract from original text
            if (empty($question) && preg_match('/(?:Question|Problem|Solve|Find|Calculate|Determine)[:\s]+(.+?)(?:\n|$)/i', $extractedText, $matches)) {
                $question = trim($matches[1]);
            }
            
            return [
                'hasQuestion' => true,
                'answer' => $response,
                'question' => $question
            ];
        } catch (\Exception $e) {
            error_log('Gemini API - Error answering homework question: ' . $e->getMessage());
            throw new \RuntimeException('Failed to process homework question: ' . $e->getMessage(), 0, $e);
        }
    }
}