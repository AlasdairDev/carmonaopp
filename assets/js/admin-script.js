(function initNotificationStream() {
    const bellBadge = document.querySelector('.notification-badge');
    if (!bellBadge) return; 
    
    let eventSource = null;
    let reconnectAttempts = 0;
    const maxReconnectAttempts = 5;
    
    function connectSSE() {
        // Close existing connection if any
        if (eventSource) {
            eventSource.close();
        }
        
        // Create new SSE connection
        eventSource = new EventSource(BASE_URL + '/api/notification_stream.php');
        
        eventSource.onmessage = function(event) {
            try {
                const data = JSON.parse(event.data);
                
                if (data.error) {
                    console.error('Notification stream error:', data.error);
                    return;
                }
                
                const newCount = data.unread_count;
                
                // Update badge
                bellBadge.textContent = newCount;
                bellBadge.style.display = newCount > 0 ? 'flex' : 'none';
                
                // Add pulse animation
                bellBadge.style.animation = 'none';
                setTimeout(() => {
                    bellBadge.style.animation = 'pulse 0.5s ease-in-out';
                }, 10);
                
                // Reset reconnect attempts on successful message
                reconnectAttempts = 0;
                
            } catch (error) {
                console.error('Error parsing notification data:', error);
            }
        };
        
        eventSource.onerror = function(error) {
            console.error('SSE connection error:', error);
            eventSource.close();
            
            // Attempt to reconnect with exponential backoff
            if (reconnectAttempts < maxReconnectAttempts) {
                reconnectAttempts++;
                const delay = Math.min(1000 * Math.pow(2, reconnectAttempts), 30000);
                console.log(`Reconnecting in ${delay}ms... (Attempt ${reconnectAttempts}/${maxReconnectAttempts})`);
                setTimeout(connectSSE, delay);
            } else {
                console.error('Max reconnection attempts reached. Falling back to polling.');
                fallbackToPolling();
            }
        };
        
        eventSource.onopen = function() {
            console.log('Notification stream connected');
        };
    }
    
    // Fallback to polling if SSE fails
    function fallbackToPolling() {
        setInterval(function() {
            fetch(BASE_URL + '/api/get_notifications.php?only_count=true')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const newCount = data.unread_count;
                        bellBadge.textContent = newCount;
                        bellBadge.style.display = newCount > 0 ? 'flex' : 'none';
                    }
                })
                .catch(error => console.error('Polling error:', error));
        }, 2000);
    }
    
    // Start SSE connection
    connectSSE();
    
    // Close connection when page unloads
    window.addEventListener('beforeunload', function() {
        if (eventSource) {
            eventSource.close();
        }
    });
})();

// Add pulse animation CSS
if (!document.getElementById('notification-pulse-style')) {
    const style = document.createElement('style');
    style.id = 'notification-pulse-style';
    style.textContent = `
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.15); }
        }
    `;
    document.head.appendChild(style);
}
