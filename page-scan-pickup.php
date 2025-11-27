<?php
/**
 * Template Name: Scan Pickup
 * 
 * Admin interface for scanning QR codes and verifying order pickups
 */

get_header();

// Check if user is admin
if (!current_user_can('manage_options')) {
    wp_redirect(home_url());
    exit;
}
?>

<style>
body {
    background: #1a1a1a;
    color: #ffffff;
}

.scanner-container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 20px;
}

.scanner-header {
    text-align: center;
    margin-bottom: 30px;
    padding: 30px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 16px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
}

.scanner-header h1 {
    margin: 0 0 10px 0;
    font-size: 36px;
    color: white;
}

.scanner-header p {
    margin: 0;
    font-size: 16px;
    color: rgba(255, 255, 255, 0.9);
}

.scanner-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 30px;
    margin-bottom: 30px;
}

.scanner-card {
    background: #2a2a2a;
    border-radius: 16px;
    padding: 30px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
}

.scanner-card h2 {
    margin: 0 0 20px 0;
    font-size: 24px;
    color: #ffffff;
    display: flex;
    align-items: center;
    gap: 10px;
}

.scanner-card h2 i {
    color: #d92027;
}

/* Camera Scanner */
#camera-preview {
    width: 100%;
    height: 400px;
    background: #000;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    overflow: hidden;
}

#camera-preview video {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.camera-overlay {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 250px;
    height: 250px;
    border: 3px solid #d92027;
    border-radius: 16px;
    box-shadow: 0 0 0 9999px rgba(0, 0, 0, 0.5);
}

.camera-corners {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
}

.camera-corner {
    position: absolute;
    width: 30px;
    height: 30px;
    border: 4px solid #d92027;
}

.camera-corner.top-left {
    top: 0;
    left: 0;
    border-right: none;
    border-bottom: none;
    border-top-left-radius: 16px;
}

.camera-corner.top-right {
    top: 0;
    right: 0;
    border-left: none;
    border-bottom: none;
    border-top-right-radius: 16px;
}

.camera-corner.bottom-left {
    bottom: 0;
    left: 0;
    border-right: none;
    border-top: none;
    border-bottom-left-radius: 16px;
}

.camera-corner.bottom-right {
    bottom: 0;
    right: 0;
    border-left: none;
    border-top: none;
    border-bottom-right-radius: 16px;
}

.camera-status {
    position: absolute;
    bottom: 20px;
    left: 50%;
    transform: translateX(-50%);
    background: rgba(0, 0, 0, 0.8);
    color: white;
    padding: 10px 20px;
    border-radius: 8px;
    font-size: 14px;
}

.camera-controls {
    margin-top: 20px;
    display: flex;
    gap: 10px;
}

.camera-controls button {
    flex: 1;
    padding: 14px;
    border: none;
    border-radius: 8px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
}

#start-camera {
    background: linear-gradient(135deg, #d92027 0%, #ff6b6b 100%);
    color: white;
}

#start-camera:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(217, 32, 39, 0.4);
}

#stop-camera {
    background: #444;
    color: white;
}

#stop-camera:hover {
    background: #555;
}

/* Manual Entry */
.manual-form {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.form-group label {
    font-size: 14px;
    font-weight: 600;
    color: #ccc;
}

.form-group input {
    padding: 14px;
    border: 2px solid #444;
    border-radius: 8px;
    font-size: 16px;
    background: #1a1a1a;
    color: white;
    transition: border-color 0.3s;
}

.form-group input:focus {
    outline: none;
    border-color: #d92027;
}

.form-group textarea {
    padding: 14px;
    border: 2px solid #444;
    border-radius: 8px;
    font-size: 14px;
    background: #1a1a1a;
    color: white;
    resize: vertical;
    min-height: 80px;
}

.form-group textarea:focus {
    outline: none;
    border-color: #d92027;
}

#verify-manual {
    padding: 14px;
    border: none;
    border-radius: 8px;
    font-size: 16px;
    font-weight: 600;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    cursor: pointer;
    transition: all 0.3s;
}

#verify-manual:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
}

/* Order Details Modal */
.order-modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.9);
    z-index: 10000;
    align-items: center;
    justify-content: center;
    padding: 20px;
}

.order-modal.active {
    display: flex;
}

.order-modal-content {
    background: #2a2a2a;
    border-radius: 16px;
    padding: 40px;
    max-width: 600px;
    width: 100%;
    max-height: 90vh;
    overflow-y: auto;
    position: relative;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.5);
}

.modal-close {
    position: absolute;
    top: 20px;
    right: 20px;
    background: #444;
    border: none;
    color: white;
    width: 36px;
    height: 36px;
    border-radius: 50%;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    transition: all 0.3s;
}

.modal-close:hover {
    background: #d92027;
    transform: rotate(90deg);
}

.order-details h2 {
    margin: 0 0 20px 0;
    font-size: 28px;
    color: white;
    text-align: center;
}

.order-info {
    background: #1a1a1a;
    padding: 20px;
    border-radius: 12px;
    margin-bottom: 20px;
}

.order-info-row {
    display: flex;
    justify-content: space-between;
    padding: 12px 0;
    border-bottom: 1px solid #333;
}

.order-info-row:last-child {
    border-bottom: none;
}

.order-info-label {
    color: #999;
    font-weight: 600;
}

.order-info-value {
    color: white;
    font-weight: 600;
}

.modal-actions {
    display: flex;
    gap: 15px;
    margin-top: 30px;
}

.modal-actions button {
    flex: 1;
    padding: 16px;
    border: none;
    border-radius: 8px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
}

#confirm-pickup {
    background: linear-gradient(135deg, #51cf66 0%, #37b24d 100%);
    color: white;
}

#confirm-pickup:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(81, 207, 102, 0.4);
}

#cancel-pickup {
    background: #444;
    color: white;
}

#cancel-pickup:hover {
    background: #555;
}

/* Statistics */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
    margin-top: 30px;
}

.stat-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 12px;
    padding: 25px;
    text-align: center;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
}

.stat-value {
    font-size: 42px;
    font-weight: 700;
    color: white;
    margin: 0 0 10px 0;
}

.stat-label {
    font-size: 14px;
    color: rgba(255, 255, 255, 0.9);
    text-transform: uppercase;
    letter-spacing: 1px;
}

/* Alerts */
.alert {
    padding: 16px 20px;
    border-radius: 8px;
    margin-bottom: 20px;
    display: none;
    align-items: center;
    gap: 12px;
    animation: slideIn 0.3s ease-out;
}

.alert.show {
    display: flex;
}

.alert-success {
    background: linear-gradient(135deg, #51cf66 0%, #37b24d 100%);
    color: white;
}

.alert-error {
    background: linear-gradient(135deg, #ff6b6b 0%, #d92027 100%);
    color: white;
}

.alert-info {
    background: linear-gradient(135deg, #4dabf7 0%, #1c7ed6 100%);
    color: white;
}

@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateY(-20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Responsive */
@media (max-width: 968px) {
    .scanner-grid {
        grid-template-columns: 1fr;
    }
    
    .stats-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<div class="scanner-container">
    <div class="scanner-header">
        <h1><i class="fas fa-qrcode"></i> Pickup Scanner</h1>
        <p>Scan QR codes or manually verify order pickups</p>
    </div>
    
    <!-- Alerts -->
    <div id="alert-container"></div>
    
    <!-- Scanner Grid -->
    <div class="scanner-grid">
        <!-- Camera Scanner -->
        <div class="scanner-card">
            <h2><i class="fas fa-camera"></i> QR Code Scanner</h2>
            
            <div id="camera-preview">
                <div class="camera-overlay">
                    <div class="camera-corners">
                        <div class="camera-corner top-left"></div>
                        <div class="camera-corner top-right"></div>
                        <div class="camera-corner bottom-left"></div>
                        <div class="camera-corner bottom-right"></div>
                    </div>
                </div>
                <div class="camera-status">Camera Inactive</div>
                <video id="camera-video" autoplay playsinline></video>
            </div>
            
            <div class="camera-controls">
                <button id="start-camera">
                    <i class="fas fa-video"></i> Start Camera
                </button>
                <button id="stop-camera">
                    <i class="fas fa-video-slash"></i> Stop Camera
                </button>
            </div>
        </div>
        
        <!-- Manual Entry -->
        <div class="scanner-card">
            <h2><i class="fas fa-keyboard"></i> Manual Verification</h2>
            
            <form class="manual-form" id="manual-verify-form">
                <div class="form-group">
                    <label for="order-number">Order Number</label>
                    <input type="text" id="order-number" placeholder="e.g., UCFC-20251127-001" required>
                </div>
                
                <div class="form-group">
                    <label for="pickup-notes">Notes (Optional)</label>
                    <textarea id="pickup-notes" placeholder="Any notes about the pickup..."></textarea>
                </div>
                
                <button type="submit" id="verify-manual">
                    <i class="fas fa-check-circle"></i> Verify Pickup
                </button>
            </form>
        </div>
    </div>
    
    <!-- Statistics -->
    <div class="scanner-card">
        <h2><i class="fas fa-chart-bar"></i> Today's Statistics</h2>
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-value" id="stat-total">0</div>
                <div class="stat-label">Total Pickups</div>
            </div>
            <div class="stat-card">
                <div class="stat-value" id="stat-scanned">0</div>
                <div class="stat-label">QR Scanned</div>
            </div>
            <div class="stat-card">
                <div class="stat-value" id="stat-manual">0</div>
                <div class="stat-label">Manual Entry</div>
            </div>
        </div>
    </div>
</div>

<!-- Order Verification Modal -->
<div class="order-modal" id="order-modal">
    <div class="order-modal-content">
        <button class="modal-close" id="modal-close">
            <i class="fas fa-times"></i>
        </button>
        
        <div class="order-details" id="order-details">
            <!-- Populated by JavaScript -->
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    let videoStream = null;
    let scanningActive = false;
    let currentOrder = null;
    
    // Start camera
    $('#start-camera').on('click', async function() {
        try {
            videoStream = await navigator.mediaDevices.getUserMedia({ 
                video: { facingMode: 'environment' } 
            });
            
            const video = document.getElementById('camera-video');
            video.srcObject = videoStream;
            
            $('.camera-status').text('Camera Active - Position QR Code');
            scanningActive = true;
            
            // Start scanning
            scanQRCode();
            
            showAlert('Camera started successfully', 'success');
        } catch (error) {
            showAlert('Failed to access camera: ' + error.message, 'error');
        }
    });
    
    // Stop camera
    $('#stop-camera').on('click', function() {
        if (videoStream) {
            videoStream.getTracks().forEach(track => track.stop());
            videoStream = null;
            scanningActive = false;
            $('.camera-status').text('Camera Inactive');
            showAlert('Camera stopped', 'info');
        }
    });
    
    // Manual verification form
    $('#manual-verify-form').on('submit', function(e) {
        e.preventDefault();
        
        const orderNumber = $('#order-number').val().trim();
        const notes = $('#pickup-notes').val().trim();
        
        if (!orderNumber) {
            showAlert('Please enter an order number', 'error');
            return;
        }
        
        // Verify order
        $.ajax({
            url: ucfcCart.ajax_url,
            type: 'POST',
            data: {
                action: 'ucfc_verify_pickup',
                nonce: ucfcCart.nonce,
                order_number: orderNumber,
                verification_method: 'manual',
                notes: notes
            },
            success: function(response) {
                if (response.success) {
                    showAlert('Pickup verified: ' + response.data.order_number, 'success');
                    $('#manual-verify-form')[0].reset();
                    updateStats();
                } else {
                    showAlert(response.data.message, 'error');
                }
            },
            error: function() {
                showAlert('Failed to verify pickup', 'error');
            }
        });
    });
    
    // Modal controls
    $('#modal-close, #cancel-pickup').on('click', function() {
        $('#order-modal').removeClass('active');
        currentOrder = null;
    });
    
    $('#confirm-pickup').on('click', function() {
        if (!currentOrder) return;
        
        const notes = $('#modal-pickup-notes').val();
        
        $.ajax({
            url: ucfcCart.ajax_url,
            type: 'POST',
            data: {
                action: 'ucfc_verify_pickup',
                nonce: ucfcCart.nonce,
                order_id: currentOrder.id,
                verification_method: 'scan',
                notes: notes
            },
            success: function(response) {
                if (response.success) {
                    showAlert('Pickup completed: ' + response.data.order_number, 'success');
                    $('#order-modal').removeClass('active');
                    updateStats();
                } else {
                    showAlert(response.data.message, 'error');
                }
            },
            error: function() {
                showAlert('Failed to complete pickup', 'error');
            }
        });
    });
    
    // Show alert
    function showAlert(message, type) {
        const icons = {
            success: 'fa-check-circle',
            error: 'fa-exclamation-circle',
            info: 'fa-info-circle'
        };
        
        const alert = $('<div class="alert alert-' + type + ' show">')
            .html('<i class="fas ' + icons[type] + '"></i> ' + message);
        
        $('#alert-container').append(alert);
        
        setTimeout(function() {
            alert.removeClass('show');
            setTimeout(function() {
                alert.remove();
            }, 300);
        }, 5000);
    }
    
    // Show order modal
    function showOrderModal(order) {
        currentOrder = order;
        
        const html = `
            <h2>Verify Pickup</h2>
            <div class="order-info">
                <div class="order-info-row">
                    <span class="order-info-label">Order Number:</span>
                    <span class="order-info-value">${order.order_number}</span>
                </div>
                <div class="order-info-row">
                    <span class="order-info-label">Customer:</span>
                    <span class="order-info-value">${order.customer_name}</span>
                </div>
                <div class="order-info-row">
                    <span class="order-info-label">Phone:</span>
                    <span class="order-info-value">${order.customer_phone}</span>
                </div>
                <div class="order-info-row">
                    <span class="order-info-label">Total:</span>
                    <span class="order-info-value">$${parseFloat(order.total).toFixed(2)}</span>
                </div>
                <div class="order-info-row">
                    <span class="order-info-label">Status:</span>
                    <span class="order-info-value">${order.order_status.toUpperCase()}</span>
                </div>
            </div>
            <div class="form-group">
                <label for="modal-pickup-notes">Pickup Notes (Optional)</label>
                <textarea id="modal-pickup-notes" placeholder="Any notes about this pickup..."></textarea>
            </div>
            <div class="modal-actions">
                <button id="cancel-pickup">Cancel</button>
                <button id="confirm-pickup">
                    <i class="fas fa-check"></i> Confirm Pickup
                </button>
            </div>
        `;
        
        $('#order-details').html(html);
        $('#order-modal').addClass('active');
    }
    
    // Update statistics
    function updateStats() {
        // In production, fetch real stats from backend
        const total = parseInt($('#stat-total').text()) || 0;
        $('#stat-total').text(total + 1);
    }
    
    // QR Code scanning (simplified - for production use a proper QR library)
    function scanQRCode() {
        if (!scanningActive) return;
        
        // This is a placeholder - implement actual QR scanning with jsQR or similar
        // For now, use keyboard shortcut to simulate scan
        
        setTimeout(scanQRCode, 100);
    }
    
    // Initialize stats
    updateStats();
});
</script>

<?php get_footer(); ?>
