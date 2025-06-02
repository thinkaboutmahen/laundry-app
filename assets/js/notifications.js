class NotificationHandler {
    constructor() {
        this.socket = null;
        this.userId = null;
        this.initialize();
    }

    initialize() {
        // Get user ID from the page
        const userIdElement = document.getElementById('user-id');
        if (userIdElement) {
            this.userId = userIdElement.value;
            this.connectWebSocket();
        }
    }

    connectWebSocket() {
        this.socket = new WebSocket('ws://localhost:8080');

        this.socket.onopen = () => {
            console.log('WebSocket connection established');
            // Register user
            this.socket.send(JSON.stringify({
                type: 'register',
                userId: this.userId
            }));
        };

        this.socket.onmessage = (event) => {
            const data = JSON.parse(event.data);
            this.showNotification(data);
        };

        this.socket.onclose = () => {
            console.log('WebSocket connection closed');
            // Try to reconnect after 5 seconds
            setTimeout(() => this.connectWebSocket(), 5000);
        };
    }

    showNotification(data) {
        // Create notification element
        const notification = document.createElement('div');
        notification.className = 'toast show position-fixed bottom-0 end-0 m-3';
        notification.style.zIndex = '1050';
        
        notification.innerHTML = `
            <div class="toast-header ${data.type === 'success' ? 'bg-success' : 'bg-warning'} text-white">
                <strong class="me-auto">${data.title}</strong>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
            </div>
            <div class="toast-body">
                ${data.message}
            </div>
        `;

        // Add to document
        document.body.appendChild(notification);

        // Remove after 5 seconds
        setTimeout(() => {
            notification.remove();
        }, 5000);
    }
}

function sendNotification(transaksiId) {
    fetch('ajax/send_notification.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ transaksi_id: transaksiId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Open WhatsApp Web in a new window
            window.open(data.whatsapp_url, '_blank');
            showToast('success', 'Notifikasi berhasil disiapkan');
        } else {
            showToast('error', data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('error', 'Terjadi kesalahan saat mengirim notifikasi');
    });
}

// Initialize notification handler when document is ready
document.addEventListener('DOMContentLoaded', () => {
    window.notificationHandler = new NotificationHandler();
}); 