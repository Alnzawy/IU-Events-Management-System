<!-- Chatbot Toggle Button -->
<div class="chat-toggle" id="chatToggle" title="Chat with IU Assistant">
    <i class="bi bi-chat-dots-fill"></i>
</div>

<!-- Chatbot Window -->
<div class="chat-window d-none" id="chatWindow">
    <div class="chat-header">
        <div class="d-flex align-items-center">
            <div class="bot-avatar me-2">
                <i class="bi bi-robot"></i>
            </div>
            <div>
                <h5 class="mb-0 fs-6">IU Assistant</h5>
                <small class="text-white-50" style="font-size: 0.75rem;">Powered by Gemini</small>
            </div>
        </div>
        <button class="btn-close btn-close-white" id="chatClose"></button>
    </div>
    
    <div class="chat-body" id="chatBody">
        <div class="chat-message bot">
            <div class="msg-bubble">
                Hello! 👋 I am the IU Assistant. How can I help you regarding events today?
            </div>
        </div>
    </div>
    
    <div class="chat-footer">
        <form id="chatForm" class="d-flex w-100 m-0">
            <input type="text" id="chatInput" placeholder="Ask me something..." required autocomplete="off">
            <button type="submit" id="chatSubmit" title="Send"><i class="bi bi-send-fill"></i></button>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const chatToggle = document.getElementById('chatToggle');
    const chatWindow = document.getElementById('chatWindow');
    const chatClose = document.getElementById('chatClose');
    const chatForm = document.getElementById('chatForm');
    const chatInput = document.getElementById('chatInput');
    const chatBody = document.getElementById('chatBody');

    // Toggle Chat Window
    chatToggle.addEventListener('click', () => {
        chatWindow.classList.toggle('d-none');
        if(!chatWindow.classList.contains('d-none')) {
            chatInput.focus();
        }
    });

    // Close Chat Window
    chatClose.addEventListener('click', () => {
        chatWindow.classList.add('d-none');
    });

    // Format Markdown to very basic HTML
    function simpleMarkdownToHtml(text) {
        let html = text.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>'); // Bold
        html = html.replace(/\*(.*?)\*/g, '<em>$1</em>'); // Italic
        html = html.replace(/\n/g, '<br>'); // Newlines
        return html;
    }

    // Append Message to UI
    function appendMessage(sender, text) {
        const msgDiv = document.createElement('div');
        msgDiv.classList.add('chat-message', sender);
        
        let formattedText = sender === 'bot' ? simpleMarkdownToHtml(text) : text;
        
        const bubble = document.createElement('div');
        bubble.classList.add('msg-bubble');
        bubble.innerHTML = formattedText;
        
        msgDiv.appendChild(bubble);
        chatBody.appendChild(msgDiv);
        chatBody.scrollTop = chatBody.scrollHeight; // Auto-scroll
    }
    
    // Show Loading Dots
    function showLoading() {
        const msgDiv = document.createElement('div');
        msgDiv.classList.add('chat-message', 'bot', 'loading-indicator');
        msgDiv.innerHTML = '<div class="msg-bubble"><div class="typing"><span class="dot"></span><span class="dot"></span><span class="dot"></span></div></div>';
        chatBody.appendChild(msgDiv);
        chatBody.scrollTop = chatBody.scrollHeight;
    }
    
    // Remove Loading Dots
    function hideLoading() {
        const loading = chatBody.querySelector('.loading-indicator');
        if(loading) loading.remove();
    }

    // Handle Form Submit
    chatForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const message = chatInput.value.trim();
        if(!message) return;
        
        // Add User Message
        appendMessage('user', message);
        chatInput.value = '';
        
        // Show Typing Indicator
        showLoading();
        
        try {
            const response = await fetch('chatbot_api.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ message: message })
            });
            
            const data = await response.json();
            hideLoading();
            
            if(data.reply) {
                appendMessage('bot', data.reply);
            } else if (data.error) {
                appendMessage('bot', `⚠️ Error: ${data.error}`);
            } else {
                appendMessage('bot', '⚠️ Something went wrong connecting to the AI.');
            }
        } catch(error) {
            hideLoading();
            appendMessage('bot', '⚠️ Network error. Could not reach the server.');
        }
    });
});
</script>
