<?php
session_start();
require_once '../config.php';
require_once '../includes/functions.php';


if (!isLoggedIn() || $_SESSION['role'] !== 'user') {
    redirect('../auth/login.php');
}


$app_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
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


<style>
:root {
    --primary: #7cb342;
    --primary-dark: #689f38;
    --danger: #dc3545;
    --warning: #ffc107;
}


body {
    /* Keep the dark green gradient on the body */
    background: linear-gradient(135deg, #7cb342 0%, #9ccc65 100%);
    min-height: 100vh;
    box-sizing: border-box;
}


.wrapper {
    background: linear-gradient(135deg, #f5f7fa 0%, #e8f0f7 100%);
    min-height: calc(100vh - 40px);
    border-radius: 24px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    position: relative;
    padding: 4rem 2rem;
}


.payment-wrapper {
    margin: 3rem auto;
    padding: 0 2rem;
}


.payment-card {
    background: white;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 20px 60px rgba(0,0,0,0.15);
}


.payment-header {
    background: linear-gradient(135deg, var(--primary), var(--primary-dark));
    color: white;
    padding: 2.5rem 2rem;
    text-align: center;
}


.payment-header h1 {
    margin: 0 0 0.5rem 0;
    font-size: 2rem;
    font-weight: 700;
}


.payment-header p {
    margin: 0;
    opacity: 0.95;
    font-size: 1.1rem;
}


.payment-body {
    padding: 3rem 2.5rem;
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
}


.deadline-alert.expired h3 {
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
    color: #333;
    font-size: 1.3rem;
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
    color: #666;
    font-weight: 600;
    text-transform: uppercase;
    margin-bottom: 0.5rem;
}


.info-value {
    font-size: 1.1rem;
    color: #333;
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


/* QR Code Section */
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
    box-shadow: 0 8px 24px rgba(0,0,0,0.15);
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
}


.payment-instructions ol {
    margin: 0;
    padding-left: 1.5rem;
}


.payment-instructions li {
    margin-bottom: 0.75rem;
    color: #333;
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
}


.gcash-detail {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem 0;
    border-bottom: 1px solid rgba(255,255,255,0.2);
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
    color: #333;
    font-size: 1rem;
}


.form-group input[type="text"],
.form-group input[type="file"],
.form-group textarea {
    width: 100%;
    padding: 1rem;
    border: 2px solid #e0e0e0;
    border-radius: 10px;
    font-size: 1rem;
    transition: all 0.3s ease;
}


.form-group input:focus,
.form-group textarea:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 4px rgba(124, 179, 66, 0.1);
}


.form-group textarea {
    min-height: 100px;
    resize: vertical;
}


.file-upload-area {
    border: 3px dashed #e0e0e0;
    border-radius: 12px;
    padding: 2rem;
    background: #f8f9fa;
    transition: all 0.3s ease;    
    display: flex;
    flex-direction: column;  
    align-items: center;      
    justify-content: center;  
    gap: 1rem;                
}


.file-upload-area:hover {
    border-color: var(--primary);
    background: #f0f7f0;
}


.file-upload-area input[type="file"] {
    width: auto;
    cursor: pointer;
}


.upload-hint {
    font-size: 0.9rem;
    color: #666;
    margin-top: 0.5rem;
}


.btn {
    padding: 1rem 2rem;
    border-radius: 50px;
    font-weight: 700;
    border: none;
    cursor: pointer;
    transition: all 0.3s ease;
    font-size: 1rem;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}


.btn-primary {
    display: inline-flex;
    padding: 1rem 1.5rem;
    border-radius: 25px;
    border: 2px solid #e0e0e0;
    background: white;
    color: var(--text-dark);
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    text-decoration: none;
    font-size: 0.95rem;
    margin-bottom: 0.5rem;
}


.btn-secondary {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding: 0.1rem 2rem;
    border-radius: 50px;    
    border: 2px solid #dcedc8;
    color: #558b2f;        
    font-weight: 600;
    font-size: 0.95rem;
    cursor: pointer;
    height:80px;
    transition: all 0.3s ease;
    text-decoration: none;
    margin-bottom: 0.5rem;
    margin-top: 0.6rem;
}


.btn-secondary:hover {
    background: #f1f8e9;  
    border-color: #7cb342;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(124, 179, 66, 0.15);
}




.btn-secondary.btn-sm {
    padding: 0.5rem 1.25rem;
    font-size: 0.85rem;
}


.btn-primary:hover:not(:disabled) {
    background: var(--primary);
    border-color: var(--primary);
    transform: translateY(-2px);
}


.btn-primary:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}


.form-actions {
    display: flex;
    gap: 1rem;
    justify-content: flex-end;
}


@media (max-width: 768px) {
    .info-grid {
        grid-template-columns: 1fr;
    }
   
    .form-actions {
        flex-direction: column;
    }
   
    .btn {
        width: 100%;
        justify-content: center;
    }
   
    .qr-code-container img {
        max-width: 250px;
    }
}
</style>


<div class="wrapper">
    <div class="payment-wrapper">
        <div class="payment-card">
            <div class="payment-header">
                <h1>Submit Payment</h1>
                <p>Complete your payment for application <?php echo htmlspecialchars($app['tracking_number']); ?></p>
            </div>
           
            <div class="payment-body">
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
                            <div class="info-value">✓ Approved - Payment Required</div>
                        </div>
                    </div>
                </div>
               
                <!-- Payment Amount -->
                <div class="amount-display">
                    <div class="amount-label">Total Amount Due</div>
                    <div class="amount-value">₱<?php echo number_format($app['payment_amount'], 2); ?></div>
                </div>
               
                <!-- QR Code Section (NEW) -->
                <div class="qr-section">
                    <h3>Scan QR Code to Pay</h3>
                    <p><strong>Use your GCash app to scan this QR code</strong></p>
                   
                    <div class="qr-code-container">
                        <!-- Replace this path with your actual QR code image -->
                        <img src="<?php echo BASE_URL; ?>/assets/gcash-qr-code.png"
                            alt="GCash QR Code"
                            onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                        <p style="display:none; color: #dc3545; margin-top: 1rem;">
                            ⚠ QR Code image not found. Please use manual payment method below.
                        </p>
                    </div>
                   
                    <div class="qr-instructions">
                        <strong>How to scan:</strong>
                        <ol>
                            <li>Open your GCash app</li>
                            <li>Tap "Scan QR" on the home screen</li>
                            <li>Point your camera at the QR code above</li>
                            <li>Enter amount: <strong>₱<?php echo number_format($app['payment_amount'], 2); ?></strong></li>
                            <li>Enter reference: <strong><?php echo htmlspecialchars($app['tracking_number']); ?></strong></li>
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
                    <div class="gcash-detail">
                        <span class="gcash-label">Reference:</span>
                        <span class="gcash-value"><?php echo htmlspecialchars($app['tracking_number']); ?></span>
                    </div>
                </div>
               
                <!-- Payment Instructions -->
                <div class="payment-instructions">
                    <h3>Manual Payment Instructions</h3>
                    <ol>
                        <li>Send payment via GCash to <strong><?php echo htmlspecialchars($config['gcash_number']); ?></strong></li>
                        <li>Use your tracking number <strong><?php echo htmlspecialchars($app['tracking_number']); ?></strong> as reference</li>
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
                        <label>Payment Reference Number <span style="color: red;">*</span></label>
                        <input type="text" name="payment_reference" id="payment_reference" required
                            placeholder="Enter GCash reference number">
                        <p class="upload-hint">Enter the reference number from your GCash transaction</p>
                    </div>
                   
                    <div class="form-group">
                        <label>Payment Proof (Screenshot) <span style="color: red;">*</span></label>
                        <div class="file-upload-area">
                            <input type="file" name="payment_proof" id="payment_proof"
                                accept="image/jpeg,image/jpg,image/png" required>
                            <p class="upload-hint">Upload a clear screenshot of your GCash payment confirmation (JPG, PNG - Max 5MB)</p>
                        </div>
                    </div>
                   
                    <div class="form-group">
                        <label>Additional Notes (Optional)</label>
                        <textarea name="payment_notes" id="payment_notes"
                                placeholder="Any additional information about your payment..."></textarea>
                    </div>
                   
                    <div class="form-actions">
                        <a href="applications.php" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary" id="submitBtn">
                            ✓ Submit Payment Proof
                        </button>
                    </div>
                </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>


<script>
document.getElementById('paymentForm').addEventListener('submit', async function(e) {
    e.preventDefault();
   
    const form = this;
    const submitBtn = document.getElementById('submitBtn');
    const formData = new FormData(form);
   
    // Validate file
    const fileInput = document.getElementById('payment_proof');
    if (fileInput.files.length === 0) {
        alert('× Please upload payment proof');
        return;
    }
   
    const file = fileInput.files[0];
    if (file.size > 5 * 1024 * 1024) {
        alert('× File size must be less than 5MB');
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
            alert('✓ ' + data.message);
            window.location.href = 'view_application.php?id=<?php echo $app_id; ?>';
        } else {
            alert('× Error: ' + data.message);
            submitBtn.disabled = false;
            submitBtn.innerHTML = '✓ Submit Payment Proof';
        }
    } catch (error) {
        console.error('Error:', error);
        alert('× An error occurred. Please try again.');
        submitBtn.disabled = false;
        submitBtn.innerHTML = '✓ Submit Payment Proof';
    }
});
</script>


<?php include '../includes/footer.php'; ?>