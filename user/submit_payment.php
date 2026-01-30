<?php
require_once '../config.php';
require_once '../includes/functions.php';

if (!isLoggedIn() || $_SESSION['role'] !== 'user') {
    redirect('../auth/login.php');
}

$app_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$user_id = $_SESSION['user_id'];

if (!$app_id) {
    redirect('applications.php');
}

// Get application details
$stmt = $pdo->prepare("
    SELECT a.*,
           u.name as applicant_name,
           s.service_name,
           d.name as department_name
    FROM applications a
    JOIN users u ON a.user_id = u.id
    LEFT JOIN services s ON a.service_id = s.id
    LEFT JOIN departments d ON a.department_id = d.id
    WHERE a.id = ? AND a.user_id = ?
");
$stmt->execute([$app_id, $user_id]);
$app = $stmt->fetch();

if (!$app) {
    $_SESSION['error'] = 'Application not found';
    redirect('applications.php');
}

// Check if payment is required and still pending or rejected
if (!$app['payment_required'] || !in_array($app['payment_status'], ['pending', 'rejected'])) {
    $_SESSION['error'] = 'Payment not required or already submitted';
    redirect('view_application.php?id=' . $app_id);
}

// Get payment config
$stmt = $pdo->prepare("SELECT config_key, config_value FROM payment_config");
$stmt->execute();
$config = [];
while ($row = $stmt->fetch()) {
    $config[$row['config_key']] = $row['config_value'];
}

// Check if payment deadline has passed
$deadline_passed = strtotime($app['payment_deadline']) < time();

$pageTitle = 'Submit Payment - ' . $app['tracking_number'];
include '../includes/header.php';
?>

<link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/user-responsive.css">

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
    :root {
        --primary: #7cb342;
        --primary-dark: #689f38;
        --danger: #dc3545;
        --warning: #ffc107;
        --text-dark: #2d3748;
        --text-light: #718096;
    }

    body {
        background: linear-gradient(135deg, #7cb342 0%, #9ccc65 100%);
        min-height: 100vh;
        box-sizing: border-box;
    }

    .wrapper {
        background: linear-gradient(135deg, #f5f7fa 0%, #e8f0f7 100%);
        min-height: calc(100vh - 40px);
        border-radius: 24px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        position: relative;
        padding: 4rem 2rem;
    }

    .page-wrapper {
        position: relative;
        z-index: 2;
        padding: 0;
    }

    .container {
        max-width: 1400px;
        margin: 0 auto;
        padding: 0 2rem;
    }

    .dashboard-banner {
        background: linear-gradient(135deg, #7cb342 0%, #9ccc65 100%);
        border-radius: 30px;
        padding: 3rem 2rem;
        margin-bottom: 2rem;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
        position: relative;
        overflow: hidden;
        margin: 0 0 2rem 0;
    }

    .dashboard-banner h1 {
        color: white;
        font-size: 2.5rem;
        font-weight: 700;
        margin: 0;
    }

    .dashboard-banner p {
        color: rgba(255, 255, 255, 0.95);
        font-size: 1.1rem;
        margin: 0.5rem 0 0 0;
    }

    .payment-card {
        background: white;
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        margin-bottom: 2rem;
    }

    .card-header-custom {
        background: linear-gradient(135deg, #f1f8e9 0%, #ffffff 100%);
        padding: 1.5rem 2rem;
        border-bottom: 2px solid #dcedc8;
    }

    .card-header-custom h2 {
        font-size: 1.5rem;
        color: var(--text-dark);
        margin: 0;
        font-weight: 700;
    }

    .card-body {
        padding: 2rem;
    }

    .deadline-alert {
        background: linear-gradient(135deg, #fff3cd, #ffe8a1);
        border-left: 5px solid var(--warning);
        padding: 1.5rem;
        border-radius: 12px;
        margin-bottom: 2rem;
    }

    .deadline-alert.expired {
        background: linear-gradient(135deg, #f8d7da, #f5c2c7);
        border-left-color: var(--danger);
    }

    .deadline-alert h3 {
        margin: 0 0 0.5rem 0;
        color: #856404;
        font-size: 1.2rem;
        font-weight: 700;
    }

    .deadline-alert.expired h3 {
        color: #842029;
    }

    .deadline-alert p {
        margin: 0.25rem 0;
        color: #856404;
        line-height: 1.6;
    }

    .deadline-alert.expired p {
        color: #842029;
    }

    .info-section {
        background: #f8f9fa;
        border-radius: 12px;
        padding: 2rem;
        margin-bottom: 2rem;
    }

    .info-section h3 {
        margin: 0 0 1.5rem 0;
        color: var(--text-dark);
        font-size: 1.3rem;
        font-weight: 700;
    }

    .info-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 1.5rem;
    }

    .info-item {
        display: flex;
        flex-direction: column;
    }

    .info-label {
        font-size: 0.85rem;
        color: var(--text-light);
        font-weight: 600;
        text-transform: uppercase;
        margin-bottom: 0.5rem;
    }

    .info-value {
        font-size: 1.1rem;
        color: var(--text-dark);
        font-weight: 600;
    }

    .amount-display {
        background: linear-gradient(135deg, #e8f5e9, #c8e6c9);
        border: 3px solid var(--primary);
        border-radius: 15px;
        padding: 2rem;
        text-align: center;
        margin-bottom: 2rem;
    }

    .amount-label {
        font-size: 1rem;
        color: #558b2f;
        font-weight: 600;
        margin-bottom: 0.5rem;
    }

    .amount-value {
        font-size: 3.5rem;
        color: var(--primary-dark);
        font-weight: 800;
        line-height: 1;
    }

    .qr-section {
        background: linear-gradient(135deg, #e3f2fd, #bbdefb);
        border: 3px solid #2196F3;
        border-radius: 15px;
        padding: 2rem;
        margin-bottom: 2rem;
        text-align: center;
    }

    .qr-section h3 {
        margin: 0 0 1rem 0;
        color: #1565c0;
        font-size: 1.5rem;
        font-weight: 700;
    }

    .qr-section p {
        color: #1976d2;
        margin-bottom: 1.5rem;
        font-size: 1.1rem;
    }

    .qr-code-container {
        background: white;
        padding: 2rem;
        border-radius: 15px;
        display: inline-block;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
        margin-bottom: 1rem;
    }

    .qr-code-container img {
        display: block;
        max-width: 300px;
        width: 100%;
        height: auto;
        border-radius: 10px;
    }

    .qr-instructions {
        background: white;
        padding: 1.5rem;
        border-radius: 10px;
        margin-top: 1rem;
    }

    .qr-instructions ol {
        text-align: left;
        margin: 0;
        padding-left: 1.5rem;
        color: #1565c0;
    }

    .qr-instructions li {
        margin-bottom: 0.75rem;
        line-height: 1.6;
    }

    .payment-instructions {
        background: #fff9e6;
        border-left: 5px solid #ffc107;
        padding: 2rem;
        border-radius: 12px;
        margin-bottom: 2rem;
    }

    .payment-instructions h3 {
        margin: 0 0 1rem 0;
        color: #856404;
        font-weight: 700;
    }

    .payment-instructions ol {
        margin: 0;
        padding-left: 1.5rem;
    }

    .payment-instructions li {
        margin-bottom: 0.75rem;
        color: var(--text-dark);
        line-height: 1.6;
    }

    .gcash-info {
        background: linear-gradient(135deg, #007bff, #0056b3);
        color: white;
        padding: 1.5rem;
        border-radius: 12px;
        margin-bottom: 2rem;
    }

    .gcash-info h4 {
        margin: 0 0 1rem 0;
        font-size: 1.2rem;
        font-weight: 700;
    }

    .gcash-detail {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.75rem 0;
        border-bottom: 1px solid rgba(255, 255, 255, 0.2);
    }

    .gcash-detail:last-child {
        border-bottom: none;
    }

    .gcash-label {
        font-weight: 600;
        opacity: 0.9;
    }

    .gcash-value {
        font-size: 1.2rem;
        font-weight: 700;
    }

    .form-group {
        margin-bottom: 2rem;
    }

    .form-group label {
        display: block;
        font-weight: 700;
        margin-bottom: 0.75rem;
        color: var(--text-dark);
        font-size: 1rem;
    }

    .form-group label .required {
        color: #dc3545;
    }

    .form-control {
        width: 100%;
        padding: 0.875rem 1.25rem;
        border: 2px solid #dcedc8;
        border-radius: 12px;
        font-size: 1rem;
        transition: all 0.3s ease;
        background: white;
        color: var(--text-dark);
    }

    .form-control:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 4px rgba(124, 179, 66, 0.1);
    }

    textarea.form-control {
        min-height: 100px;
        resize: vertical;
    }

    .file-upload-area {
        border: 3px dashed #dcedc8;
        border-radius: 12px;
        padding: 2rem;
        background: #f8faf8;
        transition: all 0.3s ease;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 1rem;
        cursor: pointer;
    }

    .file-upload-area:hover {
        border-color: var(--primary);
        background: #f1f8e9;
    }

    .upload-hint {
        font-size: 0.9rem;
        color: var(--text-light);
        margin: 0;
        text-align: center;
    }

    .btn {
        padding: 0.875rem 2rem;
        border-radius: 25px;
        font-weight: 700;
        border: none;
        cursor: pointer;
        transition: all 0.3s ease;
        font-size: 1rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        text-decoration: none;
    }

    .btn-primary {
        background: linear-gradient(135deg, var(--primary) 0%, #9ccc65 100%);
        color: white;
        box-shadow: 0 4px 15px rgba(124, 179, 66, 0.3);
        border: none;
    }

    .btn-primary:hover:not(:disabled) {
        background: linear-gradient(135deg, var(--primary-dark) 0%, #8bc34a 100%);
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(124, 179, 66, 0.4);
    }

    .btn-primary:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }

    .btn-secondary {
        background: white;
        color: var(--primary-dark);
        border: 2px solid #dcedc8;
    }

    .btn-secondary:hover {
        background: #f1f8e9;
        border-color: var(--primary);
        transform: translateY(-2px);
    }

    .form-actions {
        display: flex;
        gap: 1rem;
        justify-content: flex-end;
        margin-top: 2rem;
        align-items: end;
    }

    #submitBtn {
        height: 65px;
        border-radius: 8px;
    }

    #cancelBtn {
        height: 65px;
        border-radius: 20px;
    }

    .modal-icon i {
        font-size: 2rem;
        line-height: 1;
    }

    .modal-icon {
        width: 60px;
        height: 60px;
        margin: 0 auto 1rem;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
    }

    .modal-icon.success {
        background: #d4edda;
        color: #28a745;
    }

    .modal-icon.success i {
        display: block;
        font-size: 1.8rem;
    }

    /* Modal Overlay */
    .modal-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        z-index: 9999;
        justify-content: center;
        align-items: center;
    }

    .modal-overlay.active {
        display: flex;
    }

    /* Modal Box */
    .modal-box {
        background: white;
        border-radius: 16px;
        padding: 2rem;
        max-width: 500px;
        width: 90%;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        animation: modalSlideIn 0.3s ease;
    }

    @keyframes modalSlideIn {
        from {
            transform: translateY(-50px);
            opacity: 0;
        }

        to {
            transform: translateY(0);
            opacity: 1;
        }
    }

    .modal-icon {
        width: 60px;
        height: 60px;
        margin: 0 auto 1rem;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
    }

    .modal-icon.success {
        background: #d4edda;
        color: #28a745;
    }

    .modal-icon.error {
        background: #f8d7da;
        color: #dc3545;
    }

    .modal-title {
        font-size: 1.5rem;
        font-weight: 700;
        text-align: center;
        margin-bottom: 0.5rem;
        color: #333;
    }

    .modal-message {
        text-align: center;
        color: #666;
        margin-bottom: 1.5rem;
        line-height: 1.6;
    }

    .modal-button {
        display: block;
        width: 100%;
        padding: 0.875rem;
        background: linear-gradient(135deg, #7cb342, #9ccc65);
        color: white;
        border: none;
        border-radius: 8px;
        font-size: 1rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s;
    }

    .modal-button:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(124, 179, 66, 0.4);
    }
</style>

<div class="wrapper">
    <div class="page-wrapper">
        <div class="container">

            <!-- Dashboard Banner -->
            <div class="dashboard-banner">
                <h1>Submit Payment</h1>
                <p>Complete your payment for application <?php echo htmlspecialchars($app['tracking_number']); ?></p>
            </div>

            <div class="payment-card">
                <div class="card-body">
                    <?php if ($deadline_passed): ?>
                        <div class="deadline-alert expired">
                            <h3>⚠ Payment Deadline Expired</h3>
                            <p>The payment deadline has passed. Please contact the office for assistance.</p>
                        </div>
                    <?php else: ?>
                        <div class="deadline-alert">
                            <h3>⏱ Payment Deadline</h3>
                            <p><strong><?php echo date('F d, Y h:i A', strtotime($app['payment_deadline'])); ?></strong></p>
                            <p>Time remaining: <strong><?php
                            $diff = strtotime($app['payment_deadline']) - time();
                            $days = floor($diff / 86400);
                            $hours = floor(($diff % 86400) / 3600);
                            echo $days . ' days, ' . $hours . ' hours';
                            ?></strong></p>
                        </div>
                    <?php endif; ?>

                    <!-- Application Info -->
                    <div class="info-section">
                        <h3>Application Details</h3>
                        <div class="info-grid">
                            <div class="info-item">
                                <div class="info-label">Tracking Number</div>
                                <div class="info-value"><?php echo htmlspecialchars($app['tracking_number']); ?></div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Service</div>
                                <div class="info-value"><?php echo htmlspecialchars($app['service_name']); ?></div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Department</div>
                                <div class="info-value"><?php echo htmlspecialchars($app['department_name']); ?></div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Status</div>
                                <div class="info-value">Approved - Payment Required</div>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Amount -->
                    <div class="amount-display">
                        <div class="amount-label">Total Amount Due</div>
                        <div class="amount-value">₱<?php echo number_format($app['payment_amount'], 2); ?></div>
                    </div>

                    <!-- QR Code Section -->
                    <div class="qr-section">
                        <h3>Scan QR Code to Pay</h3>
                        <p><strong>Use your GCash app to scan this QR code</strong></p>

                        <div class="qr-code-container">
                            <img src="<?php echo BASE_URL; ?>/assets/gcash-qr-code.png" alt="GCash QR Code"
                                onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                            <p style="display:none; color: #dc3545; margin-top: 1rem;">
                                QR Code image not found. Please use manual payment method below.
                            </p>
                        </div>

                        <div class="qr-instructions">
                            <strong>How to scan:</strong>
                            <ol>
                                <li>Open your GCash app</li>
                                <li>Tap "Scan QR" on the home screen</li>
                                <li>Point your camera at the QR code above</li>
                                <li>Enter amount:
                                    <strong>₱<?php echo number_format($app['payment_amount'], 2); ?></strong>
                                </li>
                                <li>Complete payment and take a screenshot</li>
                            </ol>
                        </div>
                    </div>

                    <!-- Manual Payment Option -->
                    <div class="gcash-info">
                        <h4>Manual Payment Option</h4>
                        <div class="gcash-detail">
                            <span class="gcash-label">GCash Number:</span>
                            <span class="gcash-value"><?php echo htmlspecialchars($config['gcash_number']); ?></span>
                        </div>
                        <div class="gcash-detail">
                            <span class="gcash-label">Account Name:</span>
                            <span class="gcash-value"><?php echo htmlspecialchars($config['gcash_name']); ?></span>
                        </div>
                    </div>

                    <!-- Payment Instructions -->
                    <div class="payment-instructions">
                        <h3>Manual Payment Instructions</h3>
                        <ol>
                            <li>Send payment via GCash to
                                <strong><?php echo htmlspecialchars($config['gcash_number']); ?></strong>
                            </li>
                            <li>Take a screenshot of the payment confirmation</li>
                            <li>Upload the screenshot below along with the reference number</li>
                            <li>Wait for payment verification (usually within 24 hours)</li>
                        </ol>
                    </div>

                    <?php if (!$deadline_passed): ?>
                        <!-- Payment Submission Form -->
                        <form id="paymentForm" enctype="multipart/form-data">
                            <input type="hidden" name="app_id" value="<?php echo $app_id; ?>">

                            <div class="form-group">
                                <label>Payment Reference Number <span class="required">*</span></label>
                                <input type="text" name="payment_reference" id="payment_reference" class="form-control"
                                    required placeholder="Enter GCash reference number">
                                <p class="upload-hint" style="margin-top: 0.5rem;">Enter the reference number from your
                                    GCash transaction</p>
                            </div>

                            <div class="form-group">
                                <label>Payment Proof (Screenshot) <span class="required">*</span></label>
                                <div class="file-upload-area" id="uploadArea"
                                    onclick="document.getElementById('payment_proof').click()">
                                    <button type="button"
                                        style="padding: 0.75rem 2rem; border: 2px solid #7cb342; background: white; border-radius: 8px; font-size: 1rem; color: #558b2f; cursor: pointer; margin-bottom: 0.5rem; font-weight: 600;">
                                        Browse Files
                                    </button>
                                    <p class="upload-hint">Choose a file</p>
                                    <input type="file" style="display: none;" name="payment_proof" id="payment_proof"
                                        accept="image/jpeg,image/jpg,image/png" required>
                                </div>
                                <p class="upload-hint" style="margin-top: 0.5rem;">Upload a clear screenshot of your GCash
                                    payment confirmation (JPG, PNG - Max 5MB)</p>
                                <div id="filePreview" style="display: none; margin-top: 1rem;"></div>
                            </div>

                            <div class="form-group">
                                <label>Additional Notes (Optional)</label>
                                <textarea name="payment_notes" id="payment_notes" class="form-control"
                                    placeholder="Any additional information about your payment..."></textarea>
                            </div>

                            <div class="form-actions">
                                <a href="applications.php" class="btn btn-secondary" id="cancelBtn">Cancel</a>
                                <button type="submit" class="btn btn-primary" id="submitBtn">
                                    Submit Payment Proof
                                </button>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Custom Modal -->
<div class="modal-overlay" id="customModal">
    <div class="modal-box">
        <div class="modal-icon success" id="modalIcon">
            <i class="fas fa-check"></i>
        </div>
        <h2 class="modal-title" id="modalTitle">Success!</h2>
        <p class="modal-message" id="modalMessage"></p>
        <button class="modal-button" onclick="closeModal()">OK</button>
    </div>
</div>
<script>
    // File upload preview handler
    document.getElementById('payment_proof').addEventListener('change', function (e) {
        const file = e.target.files[0];
        const filePreview = document.getElementById('filePreview');
        const uploadArea = document.getElementById('uploadArea');

        if (file) {
            // Validate file size
            if (file.size > 5 * 1024 * 1024) {
                alert('× File size must be less than 5MB');
                this.value = '';
                filePreview.style.display = 'none';
                return;
            }

            // Show preview
            const reader = new FileReader();
            reader.onload = function (e) {
                filePreview.innerHTML = `
                <div style="background: #f1f8e9; border: 2px solid #7cb342; border-radius: 12px; padding: 1rem; display: flex; align-items: center; justify-content: space-between;">
                    <div style="display: flex; align-items: center; gap: 1rem; flex: 1;">
                        <img src="${e.target.result}" style="width: 80px; height: 80px; object-fit: cover; border-radius: 8px; border: 2px solid #dcedc8;">
                        <div style="flex: 1;">
                            <div style="font-weight: 700; color: #558b2f; margin-bottom: 0.25rem;">${file.name}</div>
                            <div style="font-size: 0.875rem; color: #7cb342;">${(file.size / 1024).toFixed(2)} KB</div>
                        </div>
                    </div>
                    <button type="button" onclick="removeFile()" style="background: #dc3545; color: white; border: none; border-radius: 8px; padding: 0.5rem 1rem; cursor: pointer; font-weight: 600;">
                        Remove
                    </button>
                </div>
            `;
                filePreview.style.display = 'block';
                uploadArea.style.display = 'none';
            };
            reader.readAsDataURL(file);
        }
    });

    function removeFile() {
        document.getElementById('payment_proof').value = '';
        document.getElementById('filePreview').style.display = 'none';
        document.getElementById('uploadArea').style.display = 'flex';
    }

    function showModal(message, type = 'success') {
        const modal = document.getElementById('customModal');
        const icon = document.getElementById('modalIcon');
        const title = document.getElementById('modalTitle');
        const messageEl = document.getElementById('modalMessage');

        // Set content
        messageEl.textContent = message;

        if (type === 'success') {
            icon.className = 'modal-icon success';
            icon.innerHTML = '<i class="fas fa-check"></i>';
            title.textContent = 'Success!';
        } else {
            icon.className = 'modal-icon error';
            icon.innerHTML = '<i class="fas fa-times"></i>';
            title.textContent = 'Error';
        }

        modal.classList.add('active');
    }

    function closeModal() {
        const modal = document.getElementById('customModal');
        const submitBtn = document.getElementById('submitBtn');

        modal.classList.remove('active');

        // Reset the submit button
        if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.innerHTML = 'Submit Payment Proof';
        }

        // Optionally refresh the page to show updated status
        // location.reload();
    }
    document.getElementById('paymentForm').addEventListener('submit', async function (e) {
        e.preventDefault();

        const form = this;
        const submitBtn = document.getElementById('submitBtn');
        const formData = new FormData(form);

        // Validate file
        const fileInput = document.getElementById('payment_proof');
        if (fileInput.files.length === 0) {
            showModal('Please upload payment proof', 'error');
            return;
        }

        const file = fileInput.files[0];
        if (file.size > 5 * 1024 * 1024) {
            showModal('File size must be less than 5MB', 'error');
            return;
        }

        // Disable button
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Uploading...';

        try {
            const response = await fetch('../api/submit_payment.php', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                showModal(data.message, 'success');
                // Modal closeModal() function will redirect
            } else {
                showModal('Error: ' + data.message, 'error');
                submitBtn.disabled = false;
                submitBtn.innerHTML = 'Submit Payment Proof';
            }
        } catch (error) {
            console.error('Error:', error);
            showModal('An error occurred. Please try again.', 'error');
            submitBtn.disabled = false;
            submitBtn.innerHTML = 'Submit Payment Proof';
        }
    });
    // Replace your existing alert with:
    // showModal('✓ Payment proof submitted successfully! Your application status has been changed to PAID and is now under verification.', 'success');
</script>

<?php include '../includes/footer.php'; ?>