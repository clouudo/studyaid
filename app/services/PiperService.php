<?php 

namespace App\Services;

class PiperService{
    private string $modelPath;
    private string $tempDir;
    private string $piperPath;
    
    public function __construct(){
        $this->modelPath = $this->getModelPath();
        $this->tempDir = $this->getTempDir();
        $this->piperPath = $this->getPiperPath();
    }

    /**
     * Get the model path based on the operating system
     */
    private function getModelPath(): string
    {
        $envPath = getenv('PIPER_MODEL_PATH');
        if ($envPath) {
            return $envPath;
        }

        $os = strtoupper(substr(PHP_OS, 0, 3));
        $username = ($os === 'WIN') ? (getenv('USERNAME') ?: 'manti') : (getenv('USER') ?: 'yeohmantik');
        
        return ($os === 'WIN') 
            ? "C:\\Users\\{$username}\\piper-models\\en_US-amy-medium.onnx"
            : "/Applications/XAMPP/xamppfiles/htdocs/studyaid/models-piper/en_US-amy-medium.onnx";
    }

    /**
     * Get a writable temporary directory based on OS
     */
    private function getTempDir(): string
    {
        $systemTempDir = sys_get_temp_dir();
        if (is_writable($systemTempDir)) {
            return rtrim($systemTempDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        }

        // Fallback to application directory
        $appTempDir = __DIR__ . '/../../temp/piper/';
        if (!is_dir($appTempDir)) {
            @mkdir($appTempDir, 0755, true);
        }
        if (is_dir($appTempDir) && is_writable($appTempDir)) {
            return $appTempDir;
        }

        return rtrim($systemTempDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
    }

    /**
     * Get the piper executable path based on OS
     */
    private function getPiperPath(): string
    {
        $envPath = getenv('PIPER_PATH');
        if ($envPath && file_exists($envPath) && is_executable($envPath)) {
            return $envPath;
        }

        $os = strtoupper(substr(PHP_OS, 0, 3));
        $commonPaths = ($os === 'WIN') 
            ? ['C:\\piper\\piper.exe', 'C:\\Program Files\\piper\\piper.exe']
            : ['/opt/anaconda3/bin/piper', '/usr/local/bin/piper', '/opt/homebrew/bin/piper', '/usr/bin/piper'];

        foreach ($commonPaths as $path) {
            if (file_exists($path) && is_executable($path)) {
                return $path;
            }
        }

        return 'piper';
    }

    /**
     * Sanitize text by removing invalid Unicode surrogate characters and control characters
     * @param string $text The text to sanitize
     * @return string Sanitized text
     */
    private function sanitizeUnicode(string $text): string
    {
        // Ensure input is valid UTF-8 first
        // 'UTF-8//IGNORE' drops invalid sequences (like surrogate halves)
        $text = iconv('UTF-8', 'UTF-8//IGNORE', $text);
        
        if ($text === false) {
            // Fallback if iconv fails
            return '';
        }

        // Remove control characters but keep newlines (\n, \r) and tabs (\t)
        // We remove:
        // \x00-\x08 (NULL to Backspace)
        // \x0B-\x0C (Vertical Tab, Form Feed)
        // \x0E-\x1F (Shift Out to Unit Separator)
        // \x7F (Delete)
        // We verify UTF-8 validity implicitly via iconv above, so /u should be safe if the string is valid utf-8
        $text = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]+/u', '', $text);
        
        return $text ?: '';
    }

    /**
     * Synthesize text to speech and save as WAV file
     * @param string $text The text to convert to speech
     * @return string|null Path to generated audio file, or null on failure
     */
    public function synthesizeText(string $text): ?string
    {
        if (empty($text)) {
            error_log("PiperService: Empty text provided");
            return null;
        }

        // Sanitize Unicode to remove invalid surrogate characters
        $text = $this->sanitizeUnicode($text);
        
        if (empty(trim($text))) {
            error_log("PiperService: Text became empty after sanitization");
            return null;
        }

        // Truncate very long text to avoid command line length issues
        $maxLength = 10000;
        if (mb_strlen($text, 'UTF-8') > $maxLength) {
            $text = mb_substr($text, 0, $maxLength, 'UTF-8') . '...';
            error_log("PiperService: Text truncated to {$maxLength} characters");
        }

        $filename = uniqid('piper_', true) . '.wav';
        $outputPath = $this->tempDir . $filename;
        $inputFile = $this->tempDir . uniqid('piper_input_', true) . '.txt';

        try {
            // Write text to temporary input file with UTF-8 encoding
            // Use mb_convert_encoding to ensure proper UTF-8 encoding
            $text = mb_convert_encoding($text, 'UTF-8', 'UTF-8');
            if (file_put_contents($inputFile, $text, LOCK_EX) === false) {
                error_log("PiperService: Failed to create input file: {$inputFile}");
                return null;
            }

            // Build command: read from input file, specify model and output file
            // On Windows, use cmd /c with type command; on Unix, use cat
            $isWindows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
            
            if ($isWindows) {
                // Windows: Use cmd /c to properly handle pipes
                $command = sprintf(
                    'cmd /c type %s | %s --model %s --output_file %s',
                    escapeshellarg($inputFile),
                    escapeshellarg($this->piperPath),
                    escapeshellarg($this->modelPath),
                    escapeshellarg($outputPath)
                );
            } else {
                // macOS/Unix: Use sh -c to ensure pipe works correctly
                $command = sprintf(
                    '/bin/sh -c %s',
                    escapeshellarg(sprintf(
                        'cat %s | %s --model %s --output_file %s',
                        escapeshellarg($inputFile),
                        escapeshellarg($this->piperPath),
                        escapeshellarg($this->modelPath),
                        escapeshellarg($outputPath)
                    ))
                );
            }

            error_log("PiperService: Executing command: " . $command);
            exec($command . ' 2>&1', $output, $returnCode);

            // Clean up input file
            @unlink($inputFile);

            // Check if command succeeded (returnCode 0) and file was created
            if ($returnCode === 0 && file_exists($outputPath) && filesize($outputPath) > 0) {
                error_log("PiperService: Audio generated successfully: {$outputPath} (" . filesize($outputPath) . " bytes)");
                return $outputPath;
            }

            // Clean up if file was created but is empty or invalid
            if (file_exists($outputPath)) {
                @unlink($outputPath);
            }

            $errorOutput = implode("\n", $output);
            error_log("PiperService: Failed to generate audio. Return code: {$returnCode}, Output: {$errorOutput}");
            return null;

        } catch (\Exception $e) {
            // Clean up files on exception
            @unlink($inputFile);
            @unlink($outputPath);
            error_log("PiperService: Exception: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Stream audio directly (for real-time streaming)
     * @param string $text The text to convert to speech
     * @return bool True if streaming succeeded, false otherwise
     */
    public function streamAudio(string $text): bool
    {
        if (empty($text)) {
            return false;
        }

        // Sanitize Unicode to remove invalid surrogate characters
        $text = $this->sanitizeUnicode($text);
        
        if (empty(trim($text))) {
            return false;
        }

        $command = sprintf(
            'echo %s | %s --model %s --output_raw',
            escapeshellarg($text),
            escapeshellarg($this->piperPath),
            escapeshellarg($this->modelPath)
        );

        header('Content-Type: audio/raw');
        passthru($command, $status);

        return $status === 0;
    }

}

?>
