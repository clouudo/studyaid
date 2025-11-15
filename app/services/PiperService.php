<?php 

namespace App\Services;

class PiperService{
    private string $modelPath;
    private string $tempDir;
    
    public function __construct(){
        $this->modelPath = "C:\\Users\\manti\\piper-models\\en_US-amy-medium.onnx";
        $this->tempDir = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
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

        // Truncate very long text to avoid command line length issues
        $maxLength = 10000;
        if (strlen($text) > $maxLength) {
            $text = substr($text, 0, $maxLength) . '...';
            error_log("PiperService: Text truncated to {$maxLength} characters");
        }

        $filename = uniqid('piper_', true) . '.wav';
        $outputPath = $this->tempDir . $filename;
        $inputFile = $this->tempDir . uniqid('piper_input_', true) . '.txt';

        try {
            // Write text to temporary input file (more reliable than piping on Windows)
            if (file_put_contents($inputFile, $text) === false) {
                error_log("PiperService: Failed to create input file: {$inputFile}");
                return null;
            }

            // Build command: read from input file, specify model and output file
            // On Windows, use cmd /c with type command; on Unix, use cat
            $isWindows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
            
            if ($isWindows) {
                // Windows: Use cmd /c to properly handle pipes
                $command = sprintf(
                    'cmd /c type %s | piper --model %s --output_file %s',
                    escapeshellarg($inputFile),
                    escapeshellarg($this->modelPath),
                    escapeshellarg($outputPath)
                );
            } else {
                // Unix/Linux: Use cat
                $command = sprintf(
                    'cat %s | piper --model %s --output_file %s',
                    escapeshellarg($inputFile),
                    escapeshellarg($this->modelPath),
                    escapeshellarg($outputPath)
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

        $command = sprintf(
            'echo %s | piper --model %s --output_raw',
            escapeshellarg($text),
            escapeshellarg($this->modelPath)
        );

        header('Content-Type: audio/raw');
        passthru($command, $status);

        return $status === 0;
    }

}


?>