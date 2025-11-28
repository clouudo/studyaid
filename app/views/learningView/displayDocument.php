<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Document - StudyAid</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <link rel="stylesheet" href="<?= CSS_PATH ?>style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
    <style>
        :root {
            --sa-primary: #6f42c1;
            --sa-primary-dark: #593093;
            --sa-accent: #e7d5ff;
            --sa-accent-strong: #d4b5ff;
            --sa-muted: #6c757d;
            --sa-card-border: #ede1ff;
        }

        .upload-container {
            background-color: #f8f9fa;
            padding: 30px;
        }

        h4[onclick] {
            transition: color 0.2s;
        }

        h4[onclick]:hover {
            color: var(--sa-primary) !important;
            text-decoration: underline;
        }

        .card {
            border: 1px solid var(--sa-card-border);
            border-radius: 16px;
            box-shadow: 0 8px 24px rgba(111, 66, 193, 0.08);
        }

        .card-header {
            background: linear-gradient(135deg, #f6efff, #ffffff);
            border-bottom: 1px solid var(--sa-card-border);
            color: var(--sa-primary);
            font-weight: 600;
        }

        .card-header h5 {
            color: inherit;
            font-weight: 600;
        }

        .btn-primary {
            background-color: var(--sa-primary) !important;
            border-color: var(--sa-primary) !important;
            box-shadow: 0 8px 18px rgba(111, 66, 193, 0.2);
        }

        .btn-primary:hover,
        .btn-primary:focus {
            background-color: var(--sa-primary-dark) !important;
            border-color: var(--sa-primary-dark) !important;
        }

        .highlight-active {
            background-color: #fff3cd;
            padding: 2px 4px;
            border-radius: 3px;
            transition: background-color 0.3s ease;
        }

        /* Audio word highlighting styles */
        .audio-word {
            transition: background-color 0.2s ease, color 0.2s ease;
            padding: 2px 1px;
            border-radius: 3px;
        }
        
        .audio-word.highlighted {
            background-color: #fff3cd;
            color: #856404;
            font-weight: 600;
        }
        
        .audio-word.current {
            background-color: #ffc107;
            color: #000;
            font-weight: 700;
        }

        .audio-controls {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
    </style>
</head>

<body class="d-flex flex-column min-vh-100">
    <div class="d-flex flex-grow-1">
        <?php include 'app/views/sidebar.php'; ?>
        <main class="flex-grow-1 p-3" style="background-color: #f8f9fa;">
            <div class="container-fluid upload-container">
                <?php if (isset($documentData)): ?>
                    <?php require_once VIEW_NAVBAR; ?>

                    <div class="card mb-3 mt-3">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><?php echo htmlspecialchars($file['name'] ?? 'Document'); ?></h5>
                            <?php if (!empty($documentData['extracted_text'])): ?>
                                <div class="audio-controls">
                                    <button id="playAudioBtn" class="btn btn-sm btn-primary" data-file-id="<?= htmlspecialchars($file['fileID']) ?>">
                                        <i class="bi bi-volume-up-fill me-2"></i>Play Audio
                                    </button>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="card-body" style="padding: 3rem 5rem;">
                            <?php if ($documentData['type'] === 'image'): ?>
                                <img src="<?php echo htmlspecialchars($documentData['content']); ?>" class="img-fluid" alt="Document Image">
                            <?php endif; ?>

                            <?php if (!empty($documentData['extracted_text'])): ?>
                                <div id="document-content"><?php echo htmlspecialchars($documentData['extracted_text']); ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="alert alert-warning">
                        <p>No document content to display.</p>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
</body>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const content = document.getElementById('document-content');
        if (content) {
            content.innerHTML = marked.parse(content.textContent);
        }

        // Audio playback and highlighting functionality
        const playAudioBtn = document.getElementById('playAudioBtn');
        if (playAudioBtn) {
            let audio = null;
            let wordSpans = [];
            let currentWordIndex = -1;

            playAudioBtn.addEventListener('click', async function() {
                const fileId = this.dataset.fileId;
                const originalText = this.innerHTML;
                
                // If audio already exists, just toggle play/pause
                if (audio) {
                    if (audio.paused) {
                        audio.play().catch(err => {
                            console.error('Error playing audio:', err);
                            alert('Error playing audio. Please check your browser settings.');
                        });
                    } else {
                        audio.pause();
                    }
                    return;
                }
                
                // Show loading state
                this.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Generating...';
                this.style.pointerEvents = 'none';

                try {
                    const formData = new FormData();
                    formData.append('file_id', fileId);

                    const response = await fetch('<?= AUDIO_DOCUMENT ?>', {
                        method: 'POST',
                        body: formData
                    });

                    const json = await response.json();

                    if (!json.success) {
                        throw new Error(json.message || 'Failed to generate audio');
                    }

                    // Get document content element
                    const content = document.getElementById('document-content');
                    if (!content) {
                        throw new Error('Document content not found');
                    }

                    // Wrap words in spans for highlighting (only if not already wrapped)
                    if (!content.querySelector('.audio-word')) {
                        wrapWordsForHighlighting(content);
                    }

                    // Create audio element
                    audio = new Audio(json.audioUrl);
                    
                    // Get word spans after wrapping
                    wordSpans = content.querySelectorAll('.audio-word');
                    currentWordIndex = -1;

                    // Handle playback
                    audio.addEventListener('play', function() {
                        playAudioBtn.innerHTML = '<i class="bi bi-pause-fill me-2"></i>Pause';
                        playAudioBtn.style.pointerEvents = 'auto';
                    });

                    audio.addEventListener('pause', function() {
                        playAudioBtn.innerHTML = '<i class="bi bi-play-fill me-2"></i>Resume';
                    });

                    audio.addEventListener('ended', function() {
                        playAudioBtn.innerHTML = originalText;
                        playAudioBtn.style.pointerEvents = 'auto';
                        removeAllHighlights();
                        currentWordIndex = -1;
                        // Reset audio so it can be played again
                        audio = null;
                    });

                    audio.addEventListener('error', function() {
                        playAudioBtn.innerHTML = originalText;
                        playAudioBtn.style.pointerEvents = 'auto';
                        alert('Error loading audio file.');
                        audio = null;
                    });

                    // Track time and highlight words
                    audio.addEventListener('timeupdate', function() {
                        updateHighlight();
                    });

                    // Start playing
                    audio.play().catch(err => {
                        console.error('Error playing audio:', err);
                        alert('Error playing audio. Please check your browser settings.');
                        playAudioBtn.innerHTML = originalText;
                        playAudioBtn.style.pointerEvents = 'auto';
                        audio = null;
                    });

                } catch (error) {
                    console.error('Error:', error);
                    playAudioBtn.innerHTML = originalText;
                    playAudioBtn.style.pointerEvents = 'auto';
                    alert('Error: ' + error.message);
                    audio = null;
                }
            });

            /**
             * Wraps words in spans for audio highlighting
             * Preserves HTML structure and markdown formatting
             */
            function wrapWordsForHighlighting(element) {
                let wordIndex = 0;
                
                // Recursively process all text nodes
                function processTextNode(textNode) {
                    const text = textNode.textContent;
                    if (!text.trim()) {
                        return; // Skip empty text nodes
                    }
                    
                    // Split text into words while preserving whitespace
                    const words = text.split(/(\s+)/);
                    const fragment = document.createDocumentFragment();
                    
                    words.forEach(word => {
                        if (word.trim() === '') {
                            // Preserve whitespace as text node
                            fragment.appendChild(document.createTextNode(word));
                        } else {
                            // Wrap word in span
                            const span = document.createElement('span');
                            span.className = 'audio-word';
                            span.setAttribute('data-word-index', wordIndex++);
                            span.textContent = word;
                            fragment.appendChild(span);
                        }
                    });
                    
                    // Replace the text node with the fragment
                    if (textNode.parentNode) {
                        textNode.parentNode.replaceChild(fragment, textNode);
                    }
                }
                
                // Traverse DOM tree and process all text nodes
                function traverse(node) {
                    const children = Array.from(node.childNodes);
                    
                    children.forEach(child => {
                        if (child.nodeType === Node.TEXT_NODE) {
                            // Process text node
                            processTextNode(child);
                        } else if (child.nodeType === Node.ELEMENT_NODE) {
                            // Skip script, style, and code/pre elements
                            const tagName = child.tagName.toLowerCase();
                            if (tagName === 'script' || tagName === 'style' || 
                                tagName === 'code' || tagName === 'pre') {
                                return;
                            }
                            // Recursively process child elements
                            traverse(child);
                        }
                    });
                }
                
                traverse(element);
            }

            /**
             * Updates word highlighting based on audio progress
             * Uses 1.25 second delay to sync with audio stream
             */
            function updateHighlight() {
                if (!audio || !audio.duration || wordSpans.length === 0) return;
                
                // Delay highlighting by 1.25 seconds to better sync with audio stream
                const delayedTime = Math.max(0, audio.currentTime - 1.25);
                const progress = delayedTime / audio.duration;
                const targetIndex = Math.floor(progress * wordSpans.length);
                
                if (targetIndex !== currentWordIndex && targetIndex < wordSpans.length) {
                    // Remove previous highlights
                    wordSpans.forEach(span => {
                        span.classList.remove('current', 'highlighted');
                    });
                    
                    // Highlight current word
                    if (targetIndex >= 0) {
                        wordSpans[targetIndex].classList.add('current');
                        
                        // Highlight previous words (fade effect)
                        for (let i = Math.max(0, targetIndex - 5); i < targetIndex; i++) {
                            wordSpans[i].classList.add('highlighted');
                        }
                        
                        // Scroll to current word
                        wordSpans[targetIndex].scrollIntoView({
                            behavior: 'smooth',
                            block: 'center'
                        });
                    }
                    
                    currentWordIndex = targetIndex;
                }
            }

            function removeAllHighlights() {
                wordSpans.forEach(span => {
                    span.classList.remove('current', 'highlighted');
                });
            }
        }
    });
</script>

</html>
