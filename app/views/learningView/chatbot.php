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
        .chat-container {
            height: 650px;
            overflow-y: auto;
            border: 1px solid #dee2e6;
            border-radius: 0.375rem;
            padding: 1rem;
            background-color: #f8f9fa;
        }

        .message {
            margin-bottom: 1rem;
            padding: 0.75rem;
            border-radius: 0.5rem;
        }

        .message.user {
            background-color: #A855F7;
            color: white;
            margin-left: 20%;
        }

        .message.bot {
            background-color: white;
            border: 1px solid #dee2e6;
            margin-right: 20%;
        }

        .message-time {
            font-size: 0.75rem;
            opacity: 0.7;
            margin-top: 0.25rem;
        }
    </style>
</head>

<body class="d-flex flex-column min-vh-100">
    <?php
    $current_url = $_GET['url'] ?? 'lm/chatbot';
    ?>
    <div class="d-flex flex-grow-1">
        <?php include VIEW_SIDEBAR; ?>
        <main class="flex-grow-1 p-3" style="background-color: #f8f9fa;">
            <div class="container">
                <h3 class="mb-4" style="color: #A855F7;">Chatbot</h3>
                <h4 class="mb-4"><?php echo htmlspecialchars($file['name'] ?? 'Document'); ?></h4>
                <?php require_once VIEW_NAVBAR; ?>

                <div class="card">
                    <div class="card-header" style="background-color: #A855F7; color: white;">
                        <h5 class="mb-0"><i class="bi bi-chat-dots me-2"></i><?php echo $chatbot['title'] ?? 'Chatbot'; ?></h5>
                    </div>
                    <div class="card-body">
                        <!-- Chat Messages Container -->
                        <div class="chat-container mb-3" id="chatContainer">
                            <div class="message bot">
                                <div class="fw-bold">StudyAid Bot</div>
                                <div>Hello! I'm here to help you understand this document. Ask me anything!</div>
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
                                <button type="submit" class="btn btn-primary" style="background-color: #A855F7; border: none;">
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
            const time = new Date().toLocaleTimeString('en-US', {
                hour: '2-digit',
                minute: '2-digit'
            });

            messageDiv.innerHTML = `
                <div class="fw-bold">${sender}</div>
                <div>${text}</div>
                <div class="message-time">${time}</div>
            `;

            messageDiv.innerHTML = marked.parse(text);

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
            loadingDiv.innerHTML = '<div class="fw-bold">StudyAid Bot</div><div><i class="bi bi-hourglass-split"></i> Thinking...</div>';
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
                    addMessage('Sorry, I encountered an error: ' + (data.message || 'Unknown error'));
                }
            } catch (error) {
                chatContainer.removeChild(loadingDiv);
                addMessage('Sorry, there was a network error. Please try again.');
            }
        });

        // Initial scroll
        scrollToBottom();
    </script>
</body>

</html>