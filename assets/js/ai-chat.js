// 🤖 Uncle Chan's AI Chat Assistant - Sleek & Modern
// Nobel Prize-Grade Design Implementation

jQuery(document).ready(function($) {
    let chatHistory = [];
    
    // Initialize AI Chat System
    function initializeAIChat() {
        // Create stunning chat widget button
        const chatWidget = $('<div>')
            .attr('id', 'ai-chat-widget')
            .addClass('ai-chat-widget')
            .html('<i class="fas fa-robot"></i>')
            .attr('aria-label', 'Open AI Chat Assistant')
            .attr('role', 'button')
            .attr('tabindex', '0');
        
        $('body').append(chatWidget);
        
        // Create beautiful chat window
        const chatWindow = $('<div>')
            .attr('id', 'ai-chat-window')
            .addClass('ai-chat-window')
            .attr('role', 'dialog')
            .attr('aria-labelledby', 'chat-title')
            .html(
                '<div class="chat-header">' +
                    '<h3 id="chat-title">Uncle Chan\'s AI Assistant</h3>' +
                    '<button class="close-chat" aria-label="Close chat"><i class="fas fa-times"></i></button>' +
                '</div>' +
                '<div class="chat-messages" id="chatMessages" role="log" aria-live="polite">' +
                    '<div class="chat-message bot-message">' +
                        'Hey there! 👋 I\'m Uncle Chan\'s AI assistant. I can help you with:<br>' +
                        '• Menu recommendations<br>' +
                        '• Order placement<br>' +
                        '• Store hours & locations<br>' +
                        '• Nutritional info<br>' +
                        '• Military discounts<br><br>' +
                        'What can I help you with today?' +
                    '</div>' +
                '</div>' +
                '<div class="chat-input-container">' +
                    '<input type="text" id="chatInput" placeholder="Type your message..." ' +
                    'aria-label="Chat message input" autocomplete="off" />' +
                    '<button id="sendMessage" aria-label="Send message">' +
                        '<i class="fas fa-paper-plane"></i>' +
                    '</button>' +
                '</div>'
            );
        
        $('body').append(chatWindow);
        
        // Smooth animation on load
        setTimeout(() => {
            chatWidget.css('opacity', '1');
        }, 500);
        
        // Event listeners with smooth interactions
        chatWidget.on('click', function() {
            openChat();
        });
        
        // Keyboard accessibility
        chatWidget.on('keypress', function(e) {
            if (e.which === 13 || e.which === 32) { // Enter or Space
                e.preventDefault();
                openChat();
            }
        });
        
        $('.close-chat').on('click', function() {
            closeChat();
        });
        
        $('#sendMessage').on('click', sendMessage);
        
        $('#chatInput').on('keypress', function(e) {
            if (e.which === 13 && !e.shiftKey) {
                e.preventDefault();
                sendMessage();
            }
        });
        
        // Auto-focus input when chat opens
        chatWindow.on('transitionend', function() {
            if (chatWindow.hasClass('active')) {
                $('#chatInput').focus();
            }
        });
    }
    
    // Open chat with smooth animation
    function openChat() {
        const chatWindow = $('#ai-chat-window');
        const chatWidget = $('.ai-chat-widget');
        
        chatWindow.addClass('active');
        chatWidget.fadeOut(300);
        
        // Smooth scroll to bottom
        setTimeout(() => {
            scrollToBottom();
        }, 100);
    }
    
    // Close chat with smooth animation
    function closeChat() {
        const chatWindow = $('#ai-chat-window');
        const chatWidget = $('.ai-chat-widget');
        
        chatWindow.removeClass('active');
        setTimeout(() => {
            chatWidget.fadeIn(300);
        }, 200);
    }
    
    // Send message with enhanced UX
    function sendMessage() {
        const message = $('#chatInput').val().trim();
        
        if (!message) {
            // Shake input if empty
            $('#chatInput').addClass('shake');
            setTimeout(() => {
                $('#chatInput').removeClass('shake');
            }, 500);
            return;
        }
        
        // Add user message to chat with animation
        addMessage(message, 'user');
        
        // Clear input with smooth transition
        $('#chatInput').val('').focus();
        
        // Show beautiful typing indicator
        showTypingIndicator();
        
        // Simulate AI thinking time (remove in production with real API)
        setTimeout(() => {
            // Send to backend
            $.ajax({
                url: uncle_chans_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'ucfc_ai_chat',
                    message: message,
                    history: chatHistory,
                    nonce: uncle_chans_ai.nonce
                },
                success: function(response) {
                    hideTypingIndicator();
                    
                    if (response.success) {
                        const reply = response.data.reply;
                        addMessage(reply, 'bot');
                        chatHistory.push({
                            role: 'user',
                            content: message
                        });
                        chatHistory.push({
                            role: 'assistant',
                            content: reply
                        });
                    } else {
                        const errorMsg = response.data.message || 'Oops! Something went wrong. 😅 Please try again or call us directly.';
                        addMessage(errorMsg, 'bot');
                    }
                },
                error: function() {
                    hideTypingIndicator();
                    addMessage('Connection lost. 📡 Please check your internet and try again.', 'bot');
                }
            });
        }, 800); // Realistic thinking delay
    }
    
    // Add message with beautiful animation
    function addMessage(message, sender) {
        const messageClass = sender === 'user' ? 'user-message' : 'bot-message';
        
        // Create animated message
        const messageEl = $('<div>')
            .addClass('chat-message ' + messageClass)
            .html(escapeHtml(message))
            .css({
                opacity: 0,
                transform: 'translateY(10px)'
            });
        
        $('#chatMessages').append(messageEl);
        
        // Smooth fade-in animation
        setTimeout(() => {
            messageEl.css({
                opacity: 1,
                transform: 'translateY(0)',
                transition: 'all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275)'
            });
        }, 50);
        
        scrollToBottom();
    }
    
    // Show elegant typing indicator
    function showTypingIndicator() {
        const indicator = $('<div>')
            .attr('id', 'typingIndicator')
            .addClass('typing-indicator')
            .css({
                opacity: 0,
                transform: 'translateY(10px)'
            })
            .html(
                '<div class="typing-dot"></div>' +
                '<div class="typing-dot"></div>' +
                '<div class="typing-dot"></div>'
            );
        
        $('#chatMessages').append(indicator);
        
        // Animate in
        setTimeout(() => {
            indicator.css({
                opacity: 1,
                transform: 'translateY(0)',
                transition: 'all 0.3s ease'
            });
        }, 50);
        
        scrollToBottom();
    }
    
    // Hide typing indicator smoothly
    function hideTypingIndicator() {
        const indicator = $('#typingIndicator');
        
        indicator.css({
            opacity: 0,
            transform: 'translateY(-10px)'
        });
        
        setTimeout(() => {
            indicator.remove();
        }, 300);
    }
    
    // Smooth scroll with easing
    function scrollToBottom() {
        const chatMessages = $('#chatMessages');
        chatMessages.animate({
            scrollTop: chatMessages[0].scrollHeight
        }, 400, 'swing');
    }
    
    // Escape HTML for security
    function escapeHtml(text) {
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.replace(/[&<>"']/g, function(m) { return map[m]; });
    }
    
    // Add shake animation for empty input
    const style = $('<style>').text(`
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
            20%, 40%, 60%, 80% { transform: translateX(5px); }
        }
        
        #chatInput.shake {
            animation: shake 0.5s;
            border-color: var(--color-primary) !important;
        }
    `);
    
    $('head').append(style);
    
    // Initialize the stunning AI chat
    initializeAIChat();
    
    // Console greeting
    console.log('%c🤖 Uncle Chan\'s AI Assistant Loaded!', 'color: #C92A2A; font-size: 14px; font-weight: bold;');
});
