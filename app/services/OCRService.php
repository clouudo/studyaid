<?php

namespace App\Services;

use Google\Cloud\Vision\V1\Client\ImageAnnotatorClient;
use Google\Cloud\Vision\V1\Image;
use Google\Cloud\Vision\V1\Feature;
use Google\Cloud\Vision\V1\AnnotateImageRequest;
use Google\Cloud\Vision\V1\BatchAnnotateImagesRequest;

class OCRService
{
    private $tesseractPath;
    private $googleVisionClient;
    private $primaryEngine = 'tesseract';
    private $fallbackEnabled = true;
    private $confidenceThreshold = 0.7;
    private $processingStartTime = 0;

    public function __construct()
    {
        $this->tesseractPath = $this->detectTesseractPath();

        $this->initGoogleVision();
    }

    /**
     * Get Google Cloud credentials file path
     */
    private function getGoogleCredentialsPath(): ?string
    {
        try {
            $configPath = __DIR__ . '/../config/cloud_storage.php';
            if (file_exists($configPath)) {
                $config = require $configPath;
                if (isset($config['key_file_path'])) {
                    $keyFilePath = $config['key_file_path'];
                    
                    // Resolve relative paths
                    if (!file_exists($keyFilePath)) {
                        // Try resolving relative to config file directory
                        $configDir = dirname($configPath);
                        $resolvedPath = realpath($configDir . '/' . $keyFilePath);
                        if ($resolvedPath && file_exists($resolvedPath)) {
                            $keyFilePath = $resolvedPath;
                        } else {
                            // Try resolving relative to project root
                            $projectRoot = dirname(dirname(__DIR__));
                            $resolvedPath = realpath($projectRoot . '/' . ltrim($keyFilePath, '/'));
                            if ($resolvedPath && file_exists($resolvedPath)) {
                                $keyFilePath = $resolvedPath;
                            }
                        }
                    }
                    
                    // Convert to absolute path
                    $keyFilePath = realpath($keyFilePath);
                    
                    if ($keyFilePath && file_exists($keyFilePath)) {
                        error_log("[OCR] Found credentials file: " . $keyFilePath);
                        return $keyFilePath;
                    } else {
                        error_log("[OCR] Credentials file not found. Original path: " . ($config['key_file_path'] ?? 'unknown'));
                    }
                }
            } else {
                error_log("[OCR] Config file not found at: " . $configPath);
            }
        } catch (\Exception $e) {
            error_log("[OCR] Failed to load Google credentials config: " . $e->getMessage());
        }
        return null;
    }

    public function recognizeText(string $imagePath, array $options = []): array
    {
        $this->processingStartTime = microtime(true);
        
        $language = $options['language'] ?? 'eng';
        $psm = $options['page_segmentation_mode'] ?? 6;
        $oem = $options['engine_mode'] ?? 1;

        $result = $this->recognizeTextWithTesseract($imagePath, $language, $psm, $oem);

        if (!$result['success'] || $result['confidence'] < $this->confidenceThreshold) {
            if ($this->fallbackEnabled) {
                $result = $this->recognizeTextWithGoogleVision($imagePath);
            }
        }

        $this->logOCRUsage($result, $imagePath);

        return $result;
    }

    public function recognizeTextWithTesseract(string $imagePath, string $language, int $psm, int $oem): array
    {
        try {
            if (!file_exists($imagePath)) {
                throw new \Exception("Image file not found: {$imagePath}");
            }

            if (!$this->isTesseractAvailable()) {
                throw new \Exception("Tesseract is not available. Please install Tesseract OCR.");
            }

            $processedImagePath = $this->preprocessImage($imagePath);

            try {
                $outputBase = $this->getTempFile('tesseract', '');
                $command = $this->buildTesseractCommand($processedImagePath, $language, $psm, $oem, $outputBase);
                $output = shell_exec($command . ' 2>&1');
                $exitCode = 0;

                // Tesseract outputs to {outputBase}.txt
                $textFilePath = $outputBase . '.txt';
                
                // Wait a moment for file to be written
                $attempts = 0;
                while (!file_exists($textFilePath) && $attempts < 10) {
                    usleep(100000); // Wait 100ms
                    $attempts++;
                }
                
                if (!file_exists($textFilePath)) {
                    throw new \Exception("Tesseract output file not created: {$textFilePath}. Command output: {$output}");
                }
                
                $recognizedText = file_get_contents($textFilePath);

                $confidence = $this->calculateConfidence($recognizedText);

                @unlink($textFilePath);
                @unlink($processedImagePath);

                return [
                    'success' => true,
                    'text' => trim($recognizedText),
                    'confidence' => $confidence,
                    'engine' => 'tesseract',
                    'processing_time' => $this->getProcessingTime(),
                    'lines' => $this->extractTextLines($recognizedText)
                ];
            } finally {
                if (isset($processedImagePath) && file_exists($processedImagePath)) {
                    @unlink($processedImagePath);
                }
            }
        } catch (\Exception $e) {
            error_log("[OCR] Tesseract Error: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'engine' => 'tesseract',
            ];
        }
    }

    public function recognizeTextWithGoogleVision(string $imagePath): array
    {
        try {
            if (!$this->googleVisionClient) {
                throw new \Exception("Google Vision client not initialized. Please check your credentials.");
            }

            $imageData = file_get_contents($imagePath);
            if ($imageData === false) {
                throw new \Exception("Failed to read image file: {$imagePath}");
            }

            // Create Image object
            $image = new Image();
            $image->setContent($imageData);

            // Create Feature for text detection
            $feature = new Feature();
            $feature->setType(\Google\Cloud\Vision\V1\Feature\Type::TEXT_DETECTION);

            // Create AnnotateImageRequest
            $request = new AnnotateImageRequest();
            $request->setImage($image);
            $request->setFeatures([$feature]);

            // Create BatchAnnotateImagesRequest
            $batchRequest = new BatchAnnotateImagesRequest();
            $batchRequest->setRequests([$request]);

            // Call the API
            $response = $this->googleVisionClient->batchAnnotateImages($batchRequest);
            $responses = $response->getResponses();

            if (empty($responses)) {
                throw new \Exception("No response from Google Vision API.");
            }

            $annotateResponse = $responses[0];
            $textAnnotations = $annotateResponse->getTextAnnotations();

            if (empty($textAnnotations)) {
                throw new \Exception("No text detected by Google Vision.");
            }

            // First annotation contains the full text
            $fullText = $textAnnotations[0]->getDescription();
            $lines = [];

            // Process individual text annotations (skip first as it's the full text)
            foreach ($textAnnotations as $index => $textAnnotation) {
                if ($index === 0) continue;

                $boundingPoly = $textAnnotation->getBoundingPoly();
                if ($boundingPoly) {
                    $vertices = $boundingPoly->getVertices();
                    if (count($vertices) >= 4) {
                        $lines[] = [
                            'text' => $textAnnotation->getDescription(),
                            'confidence' => 1.0,
                            'bbox' => [
                                'x' => $vertices[0]->getX(),
                                'y' => $vertices[0]->getY(),
                                'width' => $vertices[2]->getX() - $vertices[0]->getX(),
                                'height' => $vertices[2]->getY() - $vertices[0]->getY(),
                            ]
                        ];
                    }
                }
            }

            return [
                'success' => true,
                'text' => $fullText,
                'confidence' => 0.95,
                'engine' => 'google_vision',
                'processing_time' => $this->getProcessingTime(),
                'lines' => $lines
            ];
        } catch (\Exception $e) {
            error_log("[OCR] Google Vision Error: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'engine' => 'google_vision',
            ];
        }
    }

    public function batchRecognizeText(array $imagePaths, array $options = []): array{
        $results = [];
        $successful = 0;
        $failed = 0;

        foreach($imagePaths as $index => $imagePath){
            $result = $this->recognizeText($imagePath, $options);
            
            $results[$index] = [
                'success' => $result['success'],
                'result' => $result,
                'image_path' => $imagePath,
            ];

            if($result['success']){
                $successful++;
            }else{
                $failed++;
            }
        }

        return [
            'results' => $results,
            'successful' => $successful,
            'failed' => $failed,
            'total' => count($imagePaths),
        ];
    }

    public function getOCRStats(): array{
        return [
            'tesseract_available' => $this->isTesseractAvailable(),
            'tesseract_path' => $this->tesseractPath,
            'google_vision_available' => !is_null($this->googleVisionClient),
            'primary_engine' => $this->primaryEngine,
            'fallback_enabled' => $this->fallbackEnabled,
            'confidence_threshold' => $this->confidenceThreshold,
        ];
    }

    private function detectTesseractPath(): string{
        $possiblePaths = [
            //macOS
            '/usr/local/bin/tesseract',
            '/opt/homebrew/bin/tesseract',
            //Windows
            'C:\\Program Files\\Tesseract-OCR\\tesseract.exe',
            'C:\\Program Files (x86)\\Tesseract-OCR\\tesseract.exe',
            //Linux
            'tesseract',
        ];

        foreach($possiblePaths as $path){
            if($this->isTesseractPathValid($path))
            {
                return $path;
            }
        }
        
        return 'tesseract';
    }

    private function isTesseractPathValid(string $path): bool{
        if($path === 'tesseract'){
            return $this->isTesseractAvailable();
        }
        
        if(!file_exists($path)){
            return false;
        }
        
        if(PHP_OS_FAMILY === 'Windows' && !str_ends_with($path, '.exe')){
            return false;
        }
        
        return is_executable($path);
    }

    private function isTesseractAvailable(): bool{
        try{
            // Use the detected path, or try 'tesseract' directly
            $pathToCheck = $this->tesseractPath ?: 'tesseract';
            $command = escapeshellarg($pathToCheck) . ' --version 2>&1';
            $version = shell_exec($command);
            $isAvailable = !empty($version) && stripos($version, 'tesseract') !== false;
            
            if ($isAvailable) {
                error_log("[OCR] Tesseract detected: " . trim($version));
            } else {
                error_log("[OCR] Tesseract not available. Command output: " . ($version ?: 'empty'));
            }
            
            return $isAvailable;
        } catch(\Exception $e){
            error_log("[OCR] Tesseract check exception: " . $e->getMessage());
            return false;
        }
    }

    private function preprocessImage(string $imagePath): string{
        if(!extension_loaded('gd') && !extension_loaded('imagick')){
            return $imagePath;
        }

        $tempPath = $this->getTempFile('preprocessed', 'jpg');
        
        try{
            if(extension_loaded('imagick') && class_exists('Imagick')){
                // Imagick is provided by PHP extension - safe after extension check
                // Using dynamic instantiation to avoid static analysis false positives
                $imagickClass = 'Imagick';
                /** @var \Imagick $image */
                $image = new $imagickClass($imagePath);
                
                $image->setImageFormat('jpg');
                $image->setImageCompressionQuality(95);
                
                $image->enhanceImage();
                $image->normalizeImage();
                $image->sharpenImage(0, 1);
                
                $image->writeImage($tempPath);
                $image->clear();
                $image->destroy();
            } elseif(extension_loaded('gd')){
                $imageInfo = getimagesize($imagePath);
                if($imageInfo === false){
                    copy($imagePath, $tempPath);
                    return $tempPath;
                }
                
                $sourceImage = null;
                switch($imageInfo[2]){
                    case IMAGETYPE_JPEG:
                        $sourceImage = imagecreatefromjpeg($imagePath);
                        break;
                    case IMAGETYPE_PNG:
                        $sourceImage = imagecreatefrompng($imagePath);
                        break;
                    case IMAGETYPE_GIF:
                        $sourceImage = imagecreatefromgif($imagePath);
                        break;
                    default:
                        copy($imagePath, $tempPath);
                        return $tempPath;
                }
                
                if($sourceImage){
                    imagejpeg($sourceImage, $tempPath, 95);
                    imagedestroy($sourceImage);
                } else {
                    copy($imagePath, $tempPath);
                }
            } else {
                copy($imagePath, $tempPath);
            }
        } catch(\Exception $e){
            error_log("[OCR] Image preprocessing failed: " . $e->getMessage());
            copy($imagePath, $tempPath);
        }

        return $tempPath;
    }

    private function buildTesseractCommand(string $imagePath, string $language, int $psm, int $oem, string $outputBase): string{
        $command = escapeshellarg($this->tesseractPath);
        $command .= ' ' . escapeshellarg($imagePath);
        $command .= ' ' . escapeshellarg($outputBase);
        $command .= ' -l ' . escapeshellarg($language);
        $command .= ' --psm ' . $psm;
        $command .= ' --oem ' . $oem;
        $command .= ' -c preserve_interword_spaces=1';

        return $command;
    }

    public function calculateConfidence(string $text): float{
        if(empty(trim($text))){
            return 0.0;
        }

        $length = strlen($text);
        $wordCount = str_word_count($text);
        $avgWordLength = $length / $wordCount;

        $baseConfidence = 0.5;

        if($avgWordLength >= 3 && $avgWordLength <= 10){
            $baseConfidence += 0.2;
        }

        if($wordCount > 5){
            $baseConfidence += 0.2;
        }

        $errorPatterns = [' 1 ' , ' 0 ', ' rn ', ' cl '];
        $errorCount = 0;

        foreach($errorPatterns as $pattern){
            $errorCount += substr_count($text, $pattern);
        }

        $baseConfidence -= ($errorCount * 0.05);

        return max(0.0, min(1.0, $baseConfidence));
    }

    private function extractTextLines(string $text): array{
        $lines = explode("\n", $text);
        $textLines = [];

        foreach($lines as $index => $line){
            $line = trim($line);
            if(!empty($line)){
                $textLines[] = [
                    'text' => $line,
                    'confidence' => 0.8,
                    'line_number' => $index + 1,
                ];
            }
        }
        
        return $textLines;
    }

    private function getProcessingTime(): float
    {
        if($this->processingStartTime > 0){
            return microtime(true) - $this->processingStartTime;
        }
        return 0.0;
    }

    private function getTempFile(string $prefix, string $extension): string{
        $tempDir = sys_get_temp_dir();
        $filename = $prefix . '_' . uniqid() . '.' . $extension;
        return $tempDir . '/' . $filename;
    }

    private function initGoogleVision(): void{
        try{
            $keyFilePath = $this->getGoogleCredentialsPath();
            
            if ($keyFilePath) {
                // Set environment variable for Google Auth library
                putenv('GOOGLE_APPLICATION_CREDENTIALS=' . $keyFilePath);
                
                // Initialize with credentialsConfig
                $options = [
                    'credentialsConfig' => [
                        'keyFile' => $keyFilePath
                    ]
                ];
                
                $this->googleVisionClient = new ImageAnnotatorClient($options);
                error_log("[OCR] Google Vision client initialized successfully with credentials from: " . basename($keyFilePath));
            } else {
                // Try without explicit credentials (uses environment variable or default)
                $this->googleVisionClient = new ImageAnnotatorClient();
                error_log("[OCR] Google Vision client initialized without explicit credentials (using environment/default)");
            }
        } catch(\Exception $e){
            error_log("[OCR] Google Vision client initialization failed: " . $e->getMessage());
            error_log("[OCR] Google Vision will not be available as fallback. Tesseract will be used exclusively.");
            $this->googleVisionClient = null;
        }
    }

    private function logOCRUsage(array $result, string $imagePath): void
    {
        // Log OCR usage for analytics
        $logData = [
            'timestamp' => date('Y-m-d H:i:s'),
            'image_path' => basename($imagePath),
            'success' => $result['success'],
            'engine' => $result['engine'] ?? 'unknown',
            'confidence' => $result['confidence'] ?? 0,
            'text_length' => strlen($result['text'] ?? ''),
            'processing_time' => $result['processing_time'] ?? 0
        ];
        
        error_log('[OCR] Usage: ' . json_encode($logData));
    }


}
