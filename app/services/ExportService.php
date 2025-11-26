<?php

namespace App\Services;

class ExportService
{
    /**
     * Generates PDF file from title and content using DomPDF or fallback methods
     */
    public function generatePdf($title, $content)
    {
        if (class_exists('\Dompdf\Dompdf')) {
            try {
                $dompdf = new \Dompdf\Dompdf();
                $options = $dompdf->getOptions();
                $options->set('defaultFont', 'Arial');
                $options->set('isHtml5ParserEnabled', true);
                $options->set('isRemoteEnabled', false);

                $html = $this->convertContentToHtml($title, $content);
                $dompdf->loadHtml($html);
                $dompdf->setPaper('A4', 'portrait');
                $dompdf->render();

                $filename = preg_replace('/[^a-zA-Z0-9_-]/', '_', $title) . '.pdf';

                header('Content-Type: application/pdf');
                header('Content-Disposition: attachment; filename="' . $filename . '"');
                header('Cache-Control: private, max-age=0, must-revalidate');
                header('Pragma: public');

                echo $dompdf->output();
                exit();
            } catch (\Exception $e) {
                $this->generatePdfWithPhpWord($title, $content);
            }
        } else {
            $this->generatePdfWithPhpWord($title, $content);
        }
    }

    /**
     * Generates PDF using PHPWord with DomPDF renderer as fallback
     */
    private function generatePdfWithPhpWord($title, $content)
    {
        $dompdfPath = __DIR__ . '/../../vendor/dompdf/dompdf';
        if (file_exists($dompdfPath)) {
            try {
                \PhpOffice\PhpWord\Settings::setPdfRendererName(\PhpOffice\PhpWord\Settings::PDF_RENDERER_DOMPDF);
                \PhpOffice\PhpWord\Settings::setPdfRendererPath($dompdfPath);

                $phpWord = new \PhpOffice\PhpWord\PhpWord();
                $section = $phpWord->addSection();

                $section->addTitle($title, 1);
                $section->addTextBreak(1);

                $paragraphs = preg_split('/\n\s*\n/', $content);
                foreach ($paragraphs as $paragraph) {
                    $paragraph = trim($paragraph);
                    if (!empty($paragraph)) {
                        $section->addText($paragraph);
                        $section->addTextBreak(1);
                    }
                }

                $writer = new \PhpOffice\PhpWord\Writer\PDF($phpWord);
                $filename = preg_replace('/[^a-zA-Z0-9_-]/', '_', $title) . '.pdf';

                header('Content-Type: application/pdf');
                header('Content-Disposition: attachment; filename="' . $filename . '"');
                $writer->save('php://output');
                exit();
            } catch (\Exception $e) {
                $this->generateSimplePdf($title, $content);
            }
        } else {
            $this->generateSimplePdf($title, $content);
        }
    }

    /**
     * Converts markdown content to body HTML (without full HTML structure)
     * Used by generateSimplePdf
     */
    private function convertContentToBodyHtml($title, $content)
    {
        $html = '<h1>' . htmlspecialchars($title) . '</h1>';

        // Process markdown images first and convert URLs to base64
        $content = $this->processMarkdownImages($content);

        // Convert markdown content to HTML
        $lines = explode("\n", $content);
        $inList = false;
        $listType = '';

        foreach ($lines as $line) {
            $trimmedLine = trim($line);

            if (empty($trimmedLine)) {
                if ($inList) {
                    $html .= '</' . $listType . '>';
                    $inList = false;
                }
                continue;
            }

            // Check for markdown headings (# through ######)
            if (preg_match('/^(#{1,6})\s+(.+)$/', $trimmedLine, $matches)) {
                if ($inList) {
                    $html .= '</' . $listType . '>';
                    $inList = false;
                }
                $level = strlen($matches[1]);
                $headingText = $matches[2];
                $headingText = $this->processInlineMarkdown($headingText);
                $html .= '<h' . $level . '>' . $headingText . '</h' . $level . '>';
                continue;
            }

            // Check for markdown images (![alt](url))
            if (preg_match('/^!\[([^\]]*)\]\(([^)]+)\)$/', $trimmedLine, $matches)) {
                if ($inList) {
                    $html .= '</' . $listType . '>';
                    $inList = false;
                }
                $altText = htmlspecialchars($matches[1]);
                $imageUrl = $matches[2];
                $html .= '<img src="' . htmlspecialchars($imageUrl) . '" alt="' . $altText . '" />';
                continue;
            }

            // Check for unordered list
            if (preg_match('/^[-*]\s+(.+)$/', $trimmedLine, $matches)) {
                if (!$inList || $listType !== 'ul') {
                    if ($inList) $html .= '</' . $listType . '>';
                    $html .= '<ul>';
                    $inList = true;
                    $listType = 'ul';
                }
                $item = $matches[1];
                $item = $this->processInlineMarkdown($item);
                $html .= '<li>' . $item . '</li>';
                continue;
            }

            // Check for ordered list
            if (preg_match('/^\d+\.\s+(.+)$/', $trimmedLine, $matches)) {
                if (!$inList || $listType !== 'ol') {
                    if ($inList) $html .= '</' . $listType . '>';
                    $html .= '<ol>';
                    $inList = true;
                    $listType = 'ol';
                }
                $item = $matches[1];
                $item = $this->processInlineMarkdown($item);
                $html .= '<li>' . $item . '</li>';
                continue;
            }

            // Regular paragraph - check if it contains images or other markdown
            if ($inList) {
                $html .= '</' . $listType . '>';
                $inList = false;
            }

            // Process paragraph content (may contain inline images, formatting)
            $paragraph = $this->processParagraphMarkdown($trimmedLine);
            if (!empty($paragraph)) {
                $html .= '<p>' . $paragraph . '</p>';
            }
        }

        if ($inList) {
            $html .= '</' . $listType . '>';
        }

        return $html;
    }

    /**
     * Converts markdown content to HTML for PDF generation
     * Handles headings, images, lists, and inline formatting
     */
    private function convertContentToHtml($title, $content)
    {
        $html = '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>' . htmlspecialchars($title) . '</title>';
        $html .= '<style>
            body {
                font-family: Arial, sans-serif;
                padding: 20px;
                line-height: 1.6;
            }
            h1 {
                color: #333;
                border-bottom: 2px solid #A855F7;
                padding-bottom: 10px;
                margin-top: 0;
                font-size: 2em;
            }
            h2 {
                color: #333;
                border-bottom: 1px solid #A855F7;
                padding-bottom: 8px;
                margin-top: 24px;
                margin-bottom: 12px;
                font-size: 1.75em;
            }
            h3 {
                color: #555;
                margin-top: 20px;
                margin-bottom: 10px;
                font-size: 1.5em;
            }
            h4 {
                color: #666;
                margin-top: 16px;
                margin-bottom: 8px;
                font-size: 1.25em;
            }
            h5 {
                color: #777;
                margin-top: 12px;
                margin-bottom: 6px;
                font-size: 1.1em;
            }
            h6 {
                color: #888;
                margin-top: 10px;
                margin-bottom: 4px;
                font-size: 1em;
            }
            p {
                margin: 10px 0;
                text-align: justify;
            }
            strong {
                font-weight: bold;
            }
            em {
                font-style: italic;
            }
            ul, ol {
                margin: 10px 0;
                padding-left: 30px;
            }
            li {
                margin: 5px 0;
            }
            img {
                max-width: 100%;
                height: auto;
                display: block;
                margin: 16px auto;
                border: 1px solid #ddd;
                border-radius: 4px;
            }
        </style></head><body>';
        $html .= '<h1>' . htmlspecialchars($title) . '</h1>';

        // Process markdown images first and convert URLs to base64
        $content = $this->processMarkdownImages($content);

        // Convert markdown content to HTML
        $lines = explode("\n", $content);
        $inList = false;
        $listType = '';

        foreach ($lines as $line) {
            $trimmedLine = trim($line);

            if (empty($trimmedLine)) {
                if ($inList) {
                    $html .= '</' . $listType . '>';
                    $inList = false;
                }
                continue;
            }

            // Check for markdown headings (# through ######)
            if (preg_match('/^(#{1,6})\s+(.+)$/', $trimmedLine, $matches)) {
                if ($inList) {
                    $html .= '</' . $listType . '>';
                    $inList = false;
                }
                $level = strlen($matches[1]);
                $headingText = $matches[2];
                $headingText = $this->processInlineMarkdown($headingText);
                $html .= '<h' . $level . '>' . $headingText . '</h' . $level . '>';
                continue;
            }

            // Check for markdown images (![alt](url))
            if (preg_match('/^!\[([^\]]*)\]\(([^)]+)\)$/', $trimmedLine, $matches)) {
                if ($inList) {
                    $html .= '</' . $listType . '>';
                    $inList = false;
                }
                $altText = htmlspecialchars($matches[1]);
                $imageUrl = $matches[2];
                $html .= '<img src="' . htmlspecialchars($imageUrl) . '" alt="' . $altText . '" />';
                continue;
            }

            // Check for unordered list
            if (preg_match('/^[-*]\s+(.+)$/', $trimmedLine, $matches)) {
                if (!$inList || $listType !== 'ul') {
                    if ($inList) $html .= '</' . $listType . '>';
                    $html .= '<ul>';
                    $inList = true;
                    $listType = 'ul';
                }
                $item = $matches[1];
                $item = $this->processInlineMarkdown($item);
                $html .= '<li>' . $item . '</li>';
                continue;
            }

            // Check for ordered list
            if (preg_match('/^\d+\.\s+(.+)$/', $trimmedLine, $matches)) {
                if (!$inList || $listType !== 'ol') {
                    if ($inList) $html .= '</' . $listType . '>';
                    $html .= '<ol>';
                    $inList = true;
                    $listType = 'ol';
                }
                $item = $matches[1];
                $item = $this->processInlineMarkdown($item);
                $html .= '<li>' . $item . '</li>';
                continue;
            }

            // Regular paragraph - check if it contains images or other markdown
            if ($inList) {
                $html .= '</' . $listType . '>';
                $inList = false;
            }

            // Process paragraph content (may contain inline images, formatting)
            $paragraph = $this->processParagraphMarkdown($trimmedLine);
            if (!empty($paragraph)) {
                $html .= '<p>' . $paragraph . '</p>';
            }
        }

        if ($inList) {
            $html .= '</' . $listType . '>';
        }

        $html .= '</body></html>';
        return $html;
    }

    /**
     * Processes markdown images in content and converts URLs to base64 data URIs
     * This ensures images are embedded in the PDF even when remote URLs are disabled
     */
    private function processMarkdownImages($content)
    {
        // Find all markdown image syntax: ![alt](url)
        return preg_replace_callback('/!\[([^\]]*)\]\(([^)]+)\)/', function($matches) {
            $altText = $matches[1];
            $imageUrl = $matches[2];
            
            // Check if it's already a data URI
            if (preg_match('/^data:image\//', $imageUrl)) {
                return $matches[0]; // Keep as is
            }
            
            // Try to convert URL to base64 data URI
            $base64Image = $this->urlToBase64($imageUrl);
            if ($base64Image !== false) {
                return '![' . $altText . '](' . $base64Image . ')';
            }
            
            // If conversion fails, return original
            return $matches[0];
        }, $content);
    }

    /**
     * Converts an image URL to a base64 data URI
     * Downloads the image and converts it to base64 for PDF embedding
     */
    private function urlToBase64($url)
    {
        try {
            // Initialize cURL
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
            
            $imageData = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
            curl_close($ch);
            
            if ($httpCode !== 200 || $imageData === false) {
                return false;
            }
            
            // Determine MIME type
            $mimeType = 'image/jpeg'; // default
            if ($contentType) {
                $mimeType = explode(';', $contentType)[0];
            } else {
                // Try to detect from URL extension
                $extension = strtolower(pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION));
                $mimeTypes = [
                    'jpg' => 'image/jpeg',
                    'jpeg' => 'image/jpeg',
                    'png' => 'image/png',
                    'gif' => 'image/gif',
                    'webp' => 'image/webp',
                    'bmp' => 'image/bmp'
                ];
                if (isset($mimeTypes[$extension])) {
                    $mimeType = $mimeTypes[$extension];
                }
            }
            
            // Convert to base64 data URI
            $base64 = base64_encode($imageData);
            return 'data:' . $mimeType . ';base64,' . $base64;
            
        } catch (\Exception $e) {
            error_log('Error converting image URL to base64: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Processes inline markdown formatting (bold, italic) in text
     */
    private function processInlineMarkdown($text)
    {
        // Escape HTML first
        $text = htmlspecialchars($text);
        
        // Process bold (**text** or __text__)
        $text = preg_replace('/\*\*(.+?)\*\*/', '<strong>$1</strong>', $text);
        $text = preg_replace('/__(.+?)__/', '<strong>$1</strong>', $text);
        
        // Process italic (*text* or _text_)
        $text = preg_replace('/\*(.+?)\*/', '<em>$1</em>', $text);
        $text = preg_replace('/_(.+?)_/', '<em>$1</em>', $text);
        
        return $text;
    }

    /**
     * Processes paragraph content including inline images and formatting
     */
    private function processParagraphMarkdown($text)
    {
        // Process inline images first
        $text = preg_replace_callback('/!\[([^\]]*)\]\(([^)]+)\)/', function($matches) {
            $altText = htmlspecialchars($matches[1]);
            $imageUrl = htmlspecialchars($matches[2]);
            return '<img src="' . $imageUrl . '" alt="' . $altText . '" />';
        }, $text);
        
        // Process inline formatting
        $text = $this->processInlineMarkdown($text);
        
        return $text;
    }

    /**
     * Generates simple HTML-based PDF fallback with print dialog instructions
     */
    private function generateSimplePdf($title, $content)
    {
        // Convert markdown to HTML with better formatting
        $html = '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>' . htmlspecialchars($title) . '</title>';
        $html .= '<style>
            @media print {
                body { margin: 0; padding: 15mm; }
                .info-box { display: none; }
            }
            body {
                font-family: Arial, sans-serif;
                padding: 20px;
                line-height: 1.6;
                max-width: 800px;
                margin: 0 auto;
            }
            .info-box {
                background-color: #fff3cd;
                border: 1px solid #ffc107;
                border-radius: 5px;
                padding: 15px;
                margin-bottom: 20px;
            }
            h1 {
                color: #333;
                border-bottom: 2px solid #A855F7;
                padding-bottom: 10px;
                margin-top: 0;
            }
            p {
                margin: 10px 0;
                text-align: justify;
            }
            strong {
                font-weight: bold;
            }
            em {
                font-style: italic;
            }
            ul, ol {
                margin: 10px 0;
                padding-left: 30px;
            }
            li {
                margin: 5px 0;
            }
        </style></head><body>';
        $html .= '<div class="info-box"><strong>Note:</strong> Your document is ready. Please use your browser\'s print function (Ctrl+P or Cmd+P) and select "Save as PDF" to download.</div>';
        
        // Use the improved markdown conversion - get just the body content
        $bodyContent = $this->convertContentToBodyHtml($title, $content);
        $html .= $bodyContent;
        
        $html .= '</body></html>';

        echo $html;
        exit();
    }

    /**
     * Generates DOCX file from title and content using PHPWord
     */
    public function generateDocx($title, $content)
    {
        $phpWord = new \PhpOffice\PhpWord\PhpWord();
        $section = $phpWord->addSection();

        $section->addTitle($title, 1);
        $section->addTextBreak(1);

        $paragraphs = preg_split('/\n\s*\n/', $content);
        foreach ($paragraphs as $paragraph) {
            $paragraph = trim($paragraph);
            if (!empty($paragraph)) {
                $section->addText($paragraph);
                $section->addTextBreak(1);
            }
        }

        $writer = new \PhpOffice\PhpWord\Writer\Word2007($phpWord);
        $filename = preg_replace('/[^a-zA-Z0-9_-]/', '_', $title) . '.docx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        $writer->save('php://output');
        exit();
    }

    /**
     * Generates TXT file from title and content
     */
    public function generateTxt($title, $content)
    {
        $filename = preg_replace('/[^a-zA-Z0-9_-]/', '_', $title) . '.txt';
        $textContent = $title . "\n\n" . $content;

        header('Content-Type: text/plain');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        echo $textContent;
        exit();
    }
}

