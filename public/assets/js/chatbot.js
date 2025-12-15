(function() {
    'use strict';
    
    const baseUrl = window.baseUrl || '/Proyecto_VentAlqui/public';
    let chatOpen = false;
    
    // Crear estructura del chat
    function createChatWidget() {
        const chatHTML = `
            <div id="chatbot-widget" class="chatbot-widget">
                <div id="chatbot-toggle" class="chatbot-toggle">
                    <i class="fas fa-comments"></i>
                    <span class="chatbot-badge">1</span>
                </div>
                <div id="chatbot-container" class="chatbot-container">
                    <div class="chatbot-header">
                        <div class="chatbot-header-content">
                            <div class="chatbot-avatar">
                                <i class="fas fa-robot"></i>
                            </div>
                            <div class="chatbot-header-text">
                                <h5>Asistente Virtual</h5>
                                <span class="chatbot-status">En línea</span>
                            </div>
                        </div>
                        <button id="chatbot-close" class="chatbot-close">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div id="chatbot-messages" class="chatbot-messages">
                        <div class="chatbot-message chatbot-message-bot">
                            <div class="chatbot-message-avatar">
                                <i class="fas fa-robot"></i>
                            </div>
                            <div class="chatbot-message-content">
                                <p>¡Hola! Soy el asistente virtual de AlquiVenta. ¿En qué puedo ayudarte hoy?</p>
                            </div>
                        </div>
                    </div>
                    <div class="chatbot-input-container">
                        <input type="text" id="chatbot-input" class="chatbot-input" placeholder="Escribe tu mensaje..." autocomplete="off">
                        <button id="chatbot-send" class="chatbot-send">
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </div>
                    <div class="chatbot-suggestions">
                        <button class="chatbot-suggestion-btn" data-message="buscar excavadora">Buscar productos</button>
                        <button class="chatbot-suggestion-btn" data-message="precio de grava">Consultar precios</button>
                        <button class="chatbot-suggestion-btn" data-message="contacto">Información de contacto</button>
                    </div>
                </div>
            </div>
        `;
        
        document.body.insertAdjacentHTML('beforeend', chatHTML);
        
        // Event listeners
        document.getElementById('chatbot-toggle').addEventListener('click', toggleChat);
        document.getElementById('chatbot-close').addEventListener('click', toggleChat);
        document.getElementById('chatbot-send').addEventListener('click', sendMessage);
        document.getElementById('chatbot-input').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                sendMessage();
            }
        });
        
        // Sugerencias rápidas
        document.querySelectorAll('.chatbot-suggestion-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const message = this.getAttribute('data-message');
                document.getElementById('chatbot-input').value = message;
                sendMessage();
            });
        });
    }
    
    // Toggle chat
    function toggleChat() {
        chatOpen = !chatOpen;
        const container = document.getElementById('chatbot-container');
        const toggle = document.getElementById('chatbot-toggle');
        
        if (chatOpen) {
            container.classList.add('chatbot-container-open');
            toggle.classList.add('chatbot-toggle-hidden');
            document.getElementById('chatbot-input').focus();
        } else {
            container.classList.remove('chatbot-container-open');
            toggle.classList.remove('chatbot-toggle-hidden');
        }
    }
    
    // Enviar mensaje
    function sendMessage() {
        const input = document.getElementById('chatbot-input');
        const message = input.value.trim();
        
        if (!message) return;
        
        // Agregar mensaje del usuario
        addMessage(message, 'user');
        input.value = '';
        
        // Mostrar indicador de escritura
        showTypingIndicator();
        
        // Enviar al servidor
        fetch(baseUrl + '/chat/message', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'message=' + encodeURIComponent(message)
        })
        .then(response => response.json())
        .then(data => {
            hideTypingIndicator();
            
            if (data.success) {
                addMessage(data.response, 'bot', data.type, data.data);
            } else {
                addMessage('Lo siento, ocurrió un error. Por favor, intenta de nuevo.', 'bot');
            }
        })
        .catch(error => {
            hideTypingIndicator();
            addMessage('Lo siento, no pude procesar tu mensaje. Por favor, intenta de nuevo más tarde.', 'bot');
            console.error('Error:', error);
        });
    }
    
    // Agregar mensaje al chat
    function addMessage(text, type, messageType = 'text', data = null) {
        const messagesContainer = document.getElementById('chatbot-messages');
        const messageDiv = document.createElement('div');
        messageDiv.className = `chatbot-message chatbot-message-${type}`;
        
        let contentHTML = '';
        
        if (type === 'bot') {
            contentHTML = `
                <div class="chatbot-message-avatar">
                    <i class="fas fa-robot"></i>
                </div>
                <div class="chatbot-message-content">
                    ${formatMessage(text, messageType, data)}
                </div>
            `;
        } else {
            contentHTML = `
                <div class="chatbot-message-content">
                    <p>${escapeHtml(text)}</p>
                </div>
                <div class="chatbot-message-avatar">
                    <i class="fas fa-user"></i>
                </div>
            `;
        }
        
        messageDiv.innerHTML = contentHTML;
        messagesContainer.appendChild(messageDiv);
        
        // Scroll al final
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }
    
    // Formatear mensaje
    function formatMessage(text, type, data) {
        // Convertir saltos de línea y markdown básico
        let formatted = escapeHtml(text);
        formatted = formatted.replace(/\n/g, '<br>');
        formatted = formatted.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
        formatted = formatted.replace(/\*(.*?)\*/g, '<em>$1</em>');
        
        let html = `<p>${formatted}</p>`;
        
        // Agregar enlaces si existen
        if (data) {
            if (data.link) {
                html += `<a href="${baseUrl}${data.link}" class="chatbot-link" target="_blank">${data.linkText || 'Ver más'}</a>`;
            }
            
            if (data.links && Array.isArray(data.links)) {
                data.links.forEach(link => {
                    html += `<a href="${baseUrl}${link.url}" class="chatbot-link" target="_blank">${link.text}</a>`;
                });
            }
            
            // Mostrar productos si es tipo products
            if (type === 'products' && data.products && Array.isArray(data.products)) {
                html += '<div class="chatbot-products">';
                data.products.forEach(product => {
                    html += `
                        <div class="chatbot-product-item">
                            <h6>${escapeHtml(product.nombre)}</h6>
                            ${product.precio_alquiler_dia ? `<p>Alquiler: $${parseFloat(product.precio_alquiler_dia).toFixed(2)}/día</p>` : ''}
                            ${product.precio_venta ? `<p>Venta: $${parseFloat(product.precio_venta).toFixed(2)}</p>` : ''}
                            <p>Stock: ${product.stock_disponible}</p>
                            <a href="${baseUrl}/producto/${product.id}" class="chatbot-link" target="_blank">Ver producto</a>
                        </div>
                    `;
                });
                html += '</div>';
            }
        }
        
        return html;
    }
    
    // Mostrar indicador de escritura
    function showTypingIndicator() {
        const messagesContainer = document.getElementById('chatbot-messages');
        const typingDiv = document.createElement('div');
        typingDiv.id = 'chatbot-typing';
        typingDiv.className = 'chatbot-message chatbot-message-bot chatbot-typing';
        typingDiv.innerHTML = `
            <div class="chatbot-message-avatar">
                <i class="fas fa-robot"></i>
            </div>
            <div class="chatbot-message-content">
                <div class="chatbot-typing-indicator">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
            </div>
        `;
        messagesContainer.appendChild(typingDiv);
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }
    
    // Ocultar indicador de escritura
    function hideTypingIndicator() {
        const typing = document.getElementById('chatbot-typing');
        if (typing) {
            typing.remove();
        }
    }
    
    // Escapar HTML
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    // Inicializar cuando el DOM esté listo
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', createChatWidget);
    } else {
        createChatWidget();
    }
    
    // Exponer toggleChat globalmente si es necesario
    window.toggleChatbot = toggleChat;
})();

