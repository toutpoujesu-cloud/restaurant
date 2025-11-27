/**
 * Frontend AI Chat Widget JavaScript
 * Handles user interactions with AI chat
 */

jQuery(document).ready(function($) {
    const $chatWidget = $('.restaurant-ai-chat-widget');
    const $chatMessages = $('.ai-chat-messages');
    const $chatInput = $('.ai-chat-input');
    const $sendBtn = $('.ai-chat-send');
    const $closeBtn = $('.ai-chat-close');
    const $loadingIndicator = $('.ai-chat-loading');

    let conversationHistory = [];
    let isLoading = false;

    // Close button
    $closeBtn.on('click', function() {
        $chatWidget.slideUp(300);
    });

    // Send message on button click
    $sendBtn.on('click', sendMessage);

    // Send message on Enter key
    $chatInput.on('keypress', function(e) {
        if (e.which === 13 && !e.shiftKey) {
            e.preventDefault();
            sendMessage();
        }
    });

    function sendMessage() {
        const userMessage = $chatInput.val().trim();

        if (!userMessage || isLoading) {
            return;
        }

        // Add user message to chat
        addMessageToChat(userMessage, 'user');

        // Clear input
        $chatInput.val('');

        // Show loading indicator
        showLoading(true);
        isLoading = true;

        // Send to backend
        $.ajax({
            url: RestaurantAIChat.ajaxUrl,
            type: 'POST',
            data: {
                action: 'restaurant_ai_chat',
                nonce: RestaurantAIChat.nonce,
                message: userMessage,
                history: JSON.stringify(conversationHistory)
            },
            success: function(response) {
                if (response.success) {
                    const aiMessage = response.data.message;
                    addMessageToChat(aiMessage, 'ai');

                    // Update conversation history
                    conversationHistory.push({
                        role: 'user',
                        content: userMessage
                    });
                    conversationHistory.push({
                        role: 'assistant',
                        content: aiMessage
                    });

                    // Keep only last 10 messages for memory
                    if (conversationHistory.length > 20) {
                        conversationHistory = conversationHistory.slice(-20);
                    }
                } else {
                    addMessageToChat('Sorry, I encountered an error. Please try again.', 'ai');
                    console.error('AI Error:', response.data.message);
                }
            },
            error: function(xhr, status, error) {
                addMessageToChat('Connection error. Please check your internet and try again.', 'ai');
                console.error('AJAX Error:', error);
            },
            complete: function() {
                showLoading(false);
                isLoading = false;
                $chatInput.focus();
            }
        });
    }

    function addMessageToChat(message, sender) {
        const messageClass = sender === 'user' ? 'user-message' : 'ai-message';
        const avatar = sender === 'user' ? '👤' : '🤖';

        const messageHTML = `
            <div class="${messageClass}">
                <div class="ai-message-avatar">${avatar}</div>
                <div class="ai-message-content">
                    <p>${escapeHtml(message)}</p>
                </div>
            </div>
        `;

        $chatMessages.append(messageHTML);
        $chatMessages.scrollTop($chatMessages[0].scrollHeight);
    }

    function showLoading(show) {
        if (show) {
            $loadingIndicator.fadeIn(200);
        } else {
            $loadingIndicator.fadeOut(200);
        }
    }

    function escapeHtml(text) {
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.replace(/[&<>"']/g, m => map[m]);
    }

    // Auto-focus input when widget loads
    setTimeout(() => {
        $chatInput.focus();
    }, 500);

    // Make widget draggable (optional enhancement)
    makeWidgetDraggable();

    function makeWidgetDraggable() {
        let isDragging = false;
        let currentX;
        let currentY;
        let initialX;
        let initialY;

        const $header = $chatWidget.find('.ai-chat-header');

        $header.on('mousedown', function(e) {
            isDragging = true;
            initialX = e.clientX - $chatWidget.offsetLeft;
            initialY = e.clientY - $chatWidget.offsetTop;
            $header.style.cursor = 'grabbing';
        });

        $(document).on('mousemove', function(e) {
            if (!isDragging) return;

            currentX = e.clientX - initialX;
            currentY = e.clientY - initialY;

            $chatWidget.css({
                'right': 'auto',
                'bottom': 'auto',
                'left': currentX + 'px',
                'top': currentY + 'px'
            });
        });

        $(document).on('mouseup', function() {
            isDragging = false;
            $header.css('cursor', 'grab');
        });
    }
});
