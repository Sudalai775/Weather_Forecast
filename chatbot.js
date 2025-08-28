document.addEventListener('DOMContentLoaded', () => {
    const chatInput = document.getElementById('chat-input');
    const sendButton = document.getElementById('send-message');
    const chatMessages = document.getElementById('chat-messages');

    const GEMINI_API_KEY = 'AIzaSyAREGg1WPvW9jDfgXrt3YuGwTrBmpFrDfc';
    const API_URL = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash-latest:generateContent';

    function getCurrentTime() {
        const now = new Date();
        return `${now.getHours().toString().padStart(2, '0')}:${now.getMinutes().toString().padStart(2, '0')}`;
    }

    function addMessage(content, isUser, isLoading = false) {
        const messageDiv = document.createElement('div');
        messageDiv.classList.add('chat-message', isUser ? 'user' : 'bot');
        if (!isLoading) {
            messageDiv.setAttribute('data-time', getCurrentTime());
        }
        messageDiv.innerHTML = isLoading ? '<i class="fas fa-spinner fa-spin"></i> Thinking...' : content;
        chatMessages.appendChild(messageDiv);
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }

    async function sendMessage() {
        const message = chatInput.value.trim();
        if (!message) return;

        addMessage(message, true);
        chatInput.value = '';

        addMessage('', false, true); // Loading spinner

        try {
            const response = await fetch(`${API_URL}?key=${GEMINI_API_KEY}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    contents: [{
                        parts: [{ text: message }]
                    }]
                })
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();
            const botResponse = data.candidates[0].content.parts[0].text;

            chatMessages.lastChild.remove(); // Remove spinner
            addMessage(botResponse, false);
        } catch (error) {
            chatMessages.lastChild.remove();
            addMessage(`Error: ${error.message}`, false);
        }
    }

    sendButton.addEventListener('click', sendMessage);
    chatInput.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') sendMessage();
    });
});