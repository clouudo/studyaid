<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chatbot - StudyAid</title>
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
            border: 1px solid #ede1ff;
            border-radius: 16px;
            box-shadow: 0 8px 24px rgba(111, 66, 193, 0.08);
        }

        .card-header {
            background: linear-gradient(135deg, #f6efff, #ffffff);
            border-bottom: 1px solid #ede1ff;
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
            box-shadow: 0 8px 18px rgba(111, 66, 193, 0.25);
        }

        .btn-primary:hover,
        .btn-primary:focus {
            background-color: var(--sa-primary-dark) !important;
            border-color: var(--sa-primary-dark) !important;
        }

        .chat-container {
            height: 650px;
            overflow-y: auto;
            border: 1px solid var(--sa-card-border);
            border-radius: 1rem;
            padding: 1.5rem;
            background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
            box-shadow: inset 0 2px 8px rgba(111, 66, 193, 0.05);
        }

        .chat-container::-webkit-scrollbar {
            width: 8px;
        }

        .chat-container::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }

        .chat-container::-webkit-scrollbar-thumb {
            background: var(--sa-primary);
            border-radius: 10px;
        }

        .chat-container::-webkit-scrollbar-thumb:hover {
            background: var(--sa-primary-dark);
        }

        .message {
            margin-bottom: 1.25rem;
            padding: 1rem 1.25rem;
            border-radius: 1rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            animation: fadeIn 0.3s ease-in;
            max-width: 75%;
            word-wrap: break-word;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .message.user {
            background: linear-gradient(135deg, var(--sa-primary) 0%, var(--sa-primary-dark) 100%);
            color: #fff;
            margin-left: auto;
            border-bottom-right-radius: 0.25rem;
        }

        .message.bot {
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
            border: 1px solid var(--sa-card-border);
            margin-right: auto;
            border-bottom-left-radius: 0.25rem;
            color: #212529;
        }

        .message-header {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 0.5rem;
            font-weight: 600;
            font-size: 0.9rem;
        }

        .message-content {
            line-height: 1.6;
            font-size: 0.95rem;
        }

        .message-time {
            font-size: 0.7rem;
            opacity: 0.7;
            margin-top: 0.5rem;
            display: block;
        }

        .input-group {
            box-shadow: 0 2px 8px rgba(111, 66, 193, 0.1);
            border-radius: 0.75rem;
            overflow: hidden;
        }

        .input-group .form-control {
            border: 2px solid var(--sa-card-border);
            border-right: none;
            padding: 0.75rem 1rem;
            font-size: 0.95rem;
        }

        .input-group .form-control:focus {
            border-color: var(--sa-primary);
            box-shadow: none;
        }

        .input-group .btn-primary {
            border-left: none;
            padding: 0.75rem 1.5rem;
        }

        /* Snackbar Styles */
        .snackbar {
            position: fixed;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%) translateY(100px);
            background-color: #333;
            color: white;
            padding: 16px 24px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            display: flex;
            align-items: center;
            gap: 12px;
            z-index: 9999;
            opacity: 0;
            transition: all 0.3s ease;
            min-width: 300px;
            max-width: 500px;
        }
        .snackbar.show {
            transform: translateX(-50%) translateY(0);
            opacity: 1;
        }
        .snackbar.success {
            background-color: #28a745;
        }
        .snackbar.error {
            background-color: #dc3545;
        }
        .snackbar-icon {
            font-size: 1.2rem;
        }
        .snackbar-message {
            flex: 1;
            font-size: 0.95rem;
        }
    </style>
</head>

<body class="d-flex flex-column min-vh-100">
    <!-- Snackbar Container -->
    <div id="snackbar" class="snackbar">
        <i class="snackbar-icon" id="snackbarIcon"></i>
        <span class="snackbar-message" id="snackbarMessage"></span>
    </div>
    <?php
    $current_url = $_GET['url'] ?? 'lm/chatbot';
    ?>
    <div class="d-flex flex-grow-1">
        <?php include 'app/views/sidebar.php'; ?>
        <main class="flex-grow-1 p-3" style="background-color: #f8f9fa;">
            <div class="container-fluid upload-container">
                <h3 style="color: #212529; font-size: 1.5rem; font-weight: 600; margin-bottom: 30px;">Chatbot</h3>
                <form method="POST" action="<?= DISPLAY_DOCUMENT ?>" style="display: inline;">
                    <input type="hidden" name="file_id" value="<?php echo isset($file['fileID']) ? htmlspecialchars($file['fileID']) : ''; ?>">
                    <h4 style="color: #212529; font-size: 1.25rem; font-weight: 500; margin-bottom: 20px; cursor: pointer; display: inline-block;" onclick="this.closest('form').submit();"><?php echo htmlspecialchars($file['name'] ?? 'Document'); ?></h4>
                </form>
                <?php require_once VIEW_NAVBAR; ?>

                <div class="card">
                    <div class="card-header d-flex align-items-center">
                        <i class="bi bi-chat-dots me-3" style="font-size: 1.5rem;"></i>
                        <div class="flex-grow-1">
                            <h5 class="mb-0"><?php echo htmlspecialchars($chatbot['title'] ?? 'Chatbot'); ?></h5>
                            <small class="text-muted">Ask questions about your document</small>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Chat Messages Container -->
                        <div class="chat-container mb-3" id="chatContainer">
                            <div class="message bot">
                                <div class="message-header">
                                    <i class="bi bi-robot"></i>
                                    <span>StudyAid Bot</span>
                                </div>
                                <div class="message-content">Hello! I'm here to help you understand this document. Ask me anything!</div>
                                <div class="message-time"><?= date('H:i') ?></div>
                            </div>
                            <!-- Chat messages will be appended here -->
                        </div>

                        <!-- Chat Input Form -->
                        <form id="chatForm" action="<?= SEND_CHAT_MESSAGE ?>" method="POST">
                            <input type="hidden" name="file_id" value="<?php echo isset($file['fileID']) ? htmlspecialchars($file['fileID']) : ''; ?>">
                            <div class="input-group">
                                <input type="text" class="form-control" id="questionInput" name="question"
                                    placeholder="Type your question here..." required>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-send me-2"></i>Send
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
    <script>
        // Snackbar function
        function showSnackbar(message, type) {
            const snackbar = document.getElementById('snackbar');
            const snackbarMessage = document.getElementById('snackbarMessage');
            const snackbarIcon = document.getElementById('snackbarIcon');
            
            snackbarMessage.textContent = message;
            snackbar.className = 'snackbar ' + type;
            
            if (type === 'success') {
                snackbarIcon.className = 'snackbar-icon bi bi-check-circle-fill';
            } else if (type === 'error') {
                snackbarIcon.className = 'snackbar-icon bi bi-x-circle-fill';
            }
            
            snackbar.classList.add('show');
            
            setTimeout(function() {
                snackbar.classList.remove('show');
            }, 3000);
        }

        const chatContainer = document.getElementById('chatContainer');
        const chatForm = document.getElementById('chatForm');
        const questionInput = document.getElementById('questionInput');

        // Auto-scroll to bottom
        function scrollToBottom() {
            chatContainer.scrollTop = chatContainer.scrollHeight;
        }

        document.addEventListener('DOMContentLoaded', () => {
            const questionChats = <?php echo json_encode($questionChats); ?>;
            const responseChats = <?php echo json_encode($responseChats); ?>;
            if (Array.isArray(questionChats)) {
                questionChats.forEach((questionChat, index) => {
                    addMessage(questionChat.userQuestion, true);
                    addMessage(responseChats[index], false);
                });
            }
        });
        // Add message to chat
        function addMessage(text, isUser = false) {
            const messageDiv = document.createElement('div');
            messageDiv.className = `message ${isUser ? 'user' : 'bot'}`;

            const sender = isUser ? 'You' : 'StudyAid Bot';
            const icon = isUser ? 'bi-person-fill' : 'bi-robot';
            const time = new Date().toLocaleTimeString('en-US', {
                hour: '2-digit',
                minute: '2-digit'
            });

            let contentHTML;
            if (isUser) {
                const tempDiv = document.createElement('div');
                tempDiv.textContent = text;
                contentHTML = tempDiv.innerHTML;
            } else {
                contentHTML = marked.parse(text);
            }

            const parsedContent = marked.parse(text);

            messageDiv.innerHTML = `
                <div class="message-header">
                    <i class="bi ${icon}"></i>
                    <span>${sender}</span>
                </div>
                <div class="message-content">${parsedContent}</div>
                <div class="message-time">${time}</div>
            `;

            chatContainer.appendChild(messageDiv);
            scrollToBottom();
        }

        // Handle form submission
        chatForm.addEventListener('submit', async (e) => {
            e.preventDefault();

            const question = questionInput.value.trim();
            if (!question) return;

            // Add user message
            addMessage(question, true);
            questionInput.value = '';

            // Show loading indicator
            const loadingDiv = document.createElement('div');
            loadingDiv.className = 'message bot';
            loadingDiv.innerHTML = '<div class="message-header"><i class="bi bi-robot"></i><span>StudyAid Bot</span></div><div class="message-content"><i class="bi bi-hourglass-split me-2"></i>Thinking...</div>';
            chatContainer.appendChild(loadingDiv);
            scrollToBottom();

            try {
                const formData = new FormData(chatForm);
                formData.append('file_id', <?php echo $file['fileID']; ?>);
                formData.append('question', question);
                const response = await fetch(chatForm.action, {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                // Remove loading indicator
                chatContainer.removeChild(loadingDiv);

                if (data.success) {
                    addMessage(data.response || data.message);
                } else {
                    showSnackbar(data.message || 'Failed to get response. Please try again.', 'error');
                    addMessage('Sorry, I encountered an error. Please try asking your question again.');
                }
            } catch (error) {
                if (chatContainer.contains(loadingDiv)) {
                    chatContainer.removeChild(loadingDiv);
                }
                showSnackbar('Network error. Please check your connection and try again.', 'error');
                addMessage('Sorry, there was a network error. Please try again.');
                console.error('Error:', error);
            }
        });

        // Initial scroll
        scrollToBottom();
    </script>
</body>

</html>