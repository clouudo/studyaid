<?php

require __DIR__ . '/vendor/autoload.php';

use Google\Cloud\Vision\V1\Client\ImageAnnotatorClient;

class GoogleVisionOCR
{
    private $imageAnnotator;
    
    public function __construct()
    {
        $this->imageAnnotator = new ImageAnnotatorClient();
    }
    
    public function recognizeImage(string $imagePath): array
    {
        try {
            $image = file_get_contents($imagePath);
            
            $response = $this->imageAnnotator->textDetection($image);
            $texts = $response->getTextAnnotations();
            
            if (!$texts) {
                return [
                    'success' => false,
                    'error' => 'No text found in image'
                ];
            }
            
            $fullText = $texts[0]->getDescription();
            $lines = [];
            
            foreach ($texts as $index => $text) {
                if ($index === 0) continue; // Skip first (full text)
                
                $vertex = $text->getBoundingPoly()->getVertices();
                $lines[] = [
                    'text' => $text->getDescription(),
                    'confidence' => 1.0, // Google doesn't provide confidence scores
                    'bbox' => [
                        'x' => $vertex[0]->getX(),
                        'y' => $vertex[0]->getY(),
                        'width' => $vertex[2]->getX() - $vertex[0]->getX(),
                        'height' => $vertex[2]->getY() - $vertex[0]->getY()
                    ]
                ];
            }
            
            return [
                'success' => true,
                'text' => $fullText,
                'lines' => $lines,
                'language' => 'auto-detected'
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    public function recognizeDocument(string $imagePath): array
    {
        try {
            $image = file_get_contents($imagePath);
            
            $response = $this->imageAnnotator->documentTextDetection($image);
            $document = $response->getFullTextAnnotation();
            
            return [
                'success' => true,
                'text' => $document->getText(),
                'pages' => $this->extractPages($document),
                'blocks' => $this->extractBlocks($document)
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    private function extractPages($document): array
    {
        $pages = [];
        foreach ($document->getPages() as $page) {
            $pageData = [
                'width' => $page->getWidth(),
                'height' => $page->getHeight(),
                'blocks' => []
            ];
            
            foreach ($page->getBlocks() as $block) {
                $pageData['blocks'][] = [
                    'boundingBox' => $this->getBoundingBox($block->getBoundingBox()),
                    'paragraphs' => count($block->getParagraphs())
                ];
            }
            
            $pages[] = $pageData;
        }
        
        return $pages;
    }
    
    private function extractBlocks($document): array
    {
        $blocks = [];
        
        foreach ($document->getPages() as $page) {
            foreach ($page->getBlocks() as $block) {
                $blockData = [
                    'boundingBox' => $this->getBoundingBox($block->getBoundingBox()),
                    'blockType' => $this->getBlockType($block->getBlockType()),
                    'paragraphs' => []
                ];
                
                foreach ($block->getParagraphs() as $paragraph) {
                    $paragraphData = [
                        'boundingBox' => $this->getBoundingBox($paragraph->getBoundingBox()),
                        'words' => []
                    ];
                    
                    foreach ($paragraph->getWords() as $word) {
                        $wordText = '';
                        foreach ($word->getSymbols() as $symbol) {
                            $wordText .= $symbol->getText();
                        }
                        
                        $paragraphData['words'][] = [
                            'text' => $wordText,
                            'boundingBox' => $this->getBoundingBox($word->getBoundingBox()),
                            'confidence' => $word->getConfidence()
                        ];
                    }
                    
                    $blockData['paragraphs'][] = $paragraphData;
                }
                
                $blocks[] = $blockData;
            }
        }
        
        return $blocks;
    }
    
    private function getBlockType($type): string
    {
        $types = [
            0 => 'UNKNOWN',
            1 => 'TEXT',
            2 => 'TABLE',
            3 => 'PICTURE',
            4 => 'RULER',
            5 => 'BARCODE'
        ];
        
        return $types[$type] ?? 'UNKNOWN';
    }
    
    private function getBoundingBox($boundingBox): array
    {
        $vertices = $boundingBox->getVertices();
        return [
            'x' => $vertices[0]->getX(),
            'y' => $vertices[0]->getY(),
            'width' => $vertices[2]->getX() - $vertices[0]->getX(),
            'height' => $vertices[2]->getY() - $vertices[0]->getY()
        ];
    }
}