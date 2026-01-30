<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once '../includes/security.php';
if (!isLoggedIn() || !isAdmin()) {
    header('Location: ../login.php');
    exit();
}

$app_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if (!hasAccessToApplication($app_id)) {
    $_SESSION['error'] = 'Access denied. You can only view applications from your department.';
    header('Location: applications.php');
    exit();
}

// Get application details
$stmt = $pdo->prepare("
    SELECT 
        a.*,
        u.name as applicant_name,
        u.email,
        u.mobile as phone,
        u.address,
        d.name as department_name,
        d.code as department_code,
        s.service_name,
        s.service_code,
        s.base_fee,
        s.processing_days
    FROM applications a
    JOIN users u ON a.user_id = u.id
    LEFT JOIN departments d ON a.department_id = d.id
    LEFT JOIN services s ON a.service_id = s.id
    WHERE a.id = ?
");
$stmt->execute([$app_id]);
$app = $stmt->fetch();

if (!$app) {
    header('Location: applications.php');
    exit();
}

// Get status history
$stmt = $pdo->prepare("
    SELECT h.*, u.name as updated_by_name
    FROM application_status_history h
    LEFT JOIN users u ON h.updated_by = u.id
    WHERE h.application_id = ?
    ORDER BY h.created_at DESC
");
$stmt->execute([$app_id]);
$history = $stmt->fetchAll();

$pageTitle = 'View Application';
include '../includes/header.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/admin-responsive.css">
    <link rel="stylesheet" href="../assets/css/admin/view_applications_styles.css">

</head>

<body>

    <div class="container">
        <!-- Header -->
        <div class="page-header">
            <div class="header-content">
                <div class="header-left">
                    <a href="javascript:history.back()">
                        <i class="fas fa-arrow-left"></i> Back to Applications
                    </a>
                    <h1>Application Details</h1>
                    <p>Tracking: <?php echo htmlspecialchars($app['tracking_number']); ?></p>
                </div>
                <div class="header-actions">
                    <button onclick="window.print()" class="btn btn-white">
                        <i class="fas fa-print"></i> Print
                    </button>
                    <?php if (strtolower($app['status']) !== 'cancelled'): ?>
                        <button onclick="openUpdateModal()" class="btn btn-white">
                            <i class="fas fa-edit"></i> Update Status
                        </button>
                    <?php else: ?>
                        <button disabled class="btn btn-white" style="opacity: 0.5; cursor: not-allowed;"
                            title="Cannot modify cancelled applications">
                            <i class="fas fa-ban"></i> Update Status
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Status Banner -->
        <div class="status-banner">
            <div class="status-info">
                <h2><?php echo htmlspecialchars($app['service_name']); ?></h2>
                <p style="color: var(--text-secondary); margin: 0;">
                    <i class="fas fa-building"></i> <?php echo htmlspecialchars($app['department_name']); ?>
                </p>
            </div>
            <div>
                <span class="status-badge status-<?php echo strtolower($app['status']); ?>">
                    <?php echo htmlspecialchars($app['status']); ?>
                </span>

                <?php if ($app['payment_required']): ?>
                    <?php
                    switch ($app['payment_status']) {
                        case 'pending':
                            echo '<span class="status-badge payment-status-required status-badge-spaced">PAYMENT REQUIRED</span>';
                            break;
                        case 'submitted':
                            echo '<span class="status-badge payment-status-verify status-badge-spaced">VERIFY PAYMENT</span>';
                            break;
                        case 'verified':
                            echo '<span class="status-badge payment-status-verified status-badge-spaced">PAID</span>';
                            break;
                        case 'rejected':
                            echo '<span class="status-badge payment-status-rejected status-badge-spaced">PAYMENT REJECTED</span>';
                            break;
                    }
                    ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Main Grid -->
        <div class="grid-2">
            <!-- Left Column -->
            <div>
                <!-- Applicant Info -->
                <div class="card">
                    <h3><i class="fas fa-user"></i> Applicant Information</h3>
                    <div class="info-grid">
                        <div class="info-item">
                            <div class="info-label">Full Name</div>
                            <div class="info-value"><?php echo htmlspecialchars($app['applicant_name']); ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Email Address</div>
                            <div class="info-value"><?php echo htmlspecialchars($app['email']); ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Phone Number</div>
                            <div class="info-value"><?php echo htmlspecialchars($app['phone'] ?: 'N/A'); ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Address</div>
                            <div class="info-value"><?php echo htmlspecialchars($app['address'] ?: 'N/A'); ?></div>
                        </div>
                    </div>
                </div>

                <!-- Service Info -->
                <div class="card">
                    <h3><i class="fas fa-briefcase"></i> Service Details</h3>
                    <div class="info-grid">
                        <div class="info-item">
                            <div class="info-label">Department</div>
                            <div class="info-value"><?php echo htmlspecialchars($app['department_name']); ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Service</div>
                            <div class="info-value"><?php echo htmlspecialchars($app['service_name']); ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Base Fee</div>
                            <div class="info-value">PHP <?php echo number_format($app['base_fee'], 2); ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Processing Time</div>
                            <div class="info-value"><?php echo $app['processing_days']; ?> days</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Submitted On</div>
                            <div class="info-value"><?php echo date('M d, Y h:i A', strtotime($app['created_at'])); ?>
                            </div>
                        </div>
                    </div>
                    <?php if ($app['purpose']): ?>
                        <div class="info-item" style="margin-top: 1rem;">
                            <div class="info-label">Purpose</div>
                            <div class="info-value"><?php echo nl2br(htmlspecialchars($app['purpose'])); ?></div>
                        </div>
                    <?php endif; ?>

                    <?php if ($app['remarks']): ?>
                        <div class="info-item" style="margin-top: 1rem;">
                            <div class="info-label">Remarks</div>
                            <div class="info-value"><?php echo nl2br(htmlspecialchars($app['remarks'])); ?></div>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Documents -->
                <?php if (isset($app['compiled_document']) && $app['compiled_document']): ?>
                    <div class="card" style="margin-top: 1.5rem;">
                        <h3><i class="fas fa-paperclip"></i> Submitted Documents</h3>
                        <div class="document-item">
                            <div class="doc-icon">
                                <i class="fas fa-file-pdf"></i>
                            </div>
                            <div class="doc-info">
                                <div class="doc-name">Compiled Requirements</div>
                                <div class="doc-size">PDF Document</div>
                            </div>
                            <div class="doc-actions">
                                <a href="../<?php echo htmlspecialchars($app['compiled_document']); ?>" target="_blank"
                                    class="btn btn-sm btn-primary"
                                    style="background: #7cb342 !important; color: white !important; border-color: #7cb342 !important;">
                                    <i class="fas fa-eye"></i> View
                                </a>
                                <a href="../<?php echo htmlspecialchars($app['compiled_document']); ?>" download
                                    class="btn btn-sm btn-primary"
                                    style="background: #7cb342 !important; color: white !important; border-color: #7cb342 !important;">
                                    <i class="fas fa-download"></i> Download
                                </a>
                            </div>
                        </div>

                        <div style="margin-top: 1rem;">
                            <iframe src="../<?php echo htmlspecialchars($app['compiled_document']); ?>"
                                style="width: 100%; height: 600px; border: 2px solid var(--border); border-radius: 8px;">
                            </iframe>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Right Column -->
            <div>
                <!-- Payment Information (if payment required) --><?php if ($app['payment_required']): ?>
                    <div class="card">
                        <h3><i class="fas fa-money-bill-wave"></i> Payment Information</h3>
                        <div class="info-grid" style="grid-template-columns: 1fr;">
                            <div class="info-item">
                                <div class="info-label">Status</div>
                                <div class="info-value">
                                    <?php
                                    switch ($app['payment_status']) {
                                        case 'pending':
                                            echo '<span class="payment-status-badge payment-status-required">AWAITING PAYMENT</span>';
                                            break;
                                        case 'submitted':
                                            echo '<span class="payment-status-badge payment-status-verify">VERIFY PAYMENT</span>';
                                            break;
                                        case 'verified':
                                            echo '<span class="payment-status-badge payment-status-verified">VERIFIED ✓</span>';
                                            break;
                                        case 'rejected':
                                            echo '<span class="payment-status-badge payment-status-rejected">REJECTED</span>';
                                            break;
                                    }
                                    ?>
                                </div>
                            </div>

                            <div class="info-item">
                                <div class="info-label">Amount</div>
                                <div class="info-value payment-amount">
                                    ₱<?php echo number_format($app['payment_amount'], 2); ?>
                                </div>
                            </div>
                        </div>

                        <?php if ($app['payment_status'] === 'submitted'): ?>
                            <a href="verify_payment.php?id=<?php echo $app_id; ?>" class="btn btn-primary"
                                style="width: 100%; margin-top: 1rem; justify-content: center;">
                                <i class="fas fa-check-circle"></i> Verify Payment
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                <!-- Status History -->
                <div class="card">
                    <h3><i class="fas fa-history"></i> Status History</h3>
                    <?php if (empty($history)): ?>
                        <p style="color: var(--text-secondary); text-align: center; padding: 2rem;">No status updates yet
                        </p>
                    <?php else: ?>
                        <div class="timeline">
                            <?php foreach ($history as $item): ?>
                                <div class="timeline-item">
                                    <div class="timeline-content">
                                        <div class="timeline-date">
                                            <i class="fas fa-clock"></i>
                                            <?php echo date('M d, Y h:i A', strtotime($item['created_at'])); ?>
                                        </div>
                                        <div class="timeline-status">
                                            <span class="status-badge status-<?php echo strtolower($item['status']); ?>">
                                                <?php echo htmlspecialchars($item['status']); ?>
                                            </span>
                                        </div>
                                        <?php if ($item['updated_by_name']): ?>
                                            <div class="timeline-user">
                                                <i class="fas fa-user"></i>
                                                Updated by: <?php echo htmlspecialchars($item['updated_by_name']); ?>
                                            </div>
                                        <?php endif; ?>
                                        <?php if ($item['remarks']): ?>
                                            <div class="timeline-remarks">
                                                <strong>Remarks:</strong><br>
                                                <?php echo nl2br(htmlspecialchars($item['remarks'])); ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Update Status Modal -->
    <div id="updateModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Update Application Status</h3>
                <button class="modal-close" onclick="closeUpdateModal()">&times;</button>
            </div>

            <form id="updateStatusForm">
                <div class="modal-body">
                    <input type="hidden" name="app_id" value="<?php echo $app_id; ?>">

                    <div class="form-group">
                        <label>Current Status</label>
                        <span class="status-badge status-<?php echo strtolower($app['status']); ?>">
                            <?php echo htmlspecialchars($app['status']); ?>
                        </span>
                    </div>

                    <div class="form-group">
                        <label>New Status *</label>
                        <select name="status" id="newStatus" required>
                            <option value="">-- Select Status --</option>
                            <option value="Pending">Pending</option>
                            <option value="Processing">Processing</option>
                            <option value="Approved">Approved</option>
                            <option value="Rejected">Rejected</option>
                            <option value="Completed">Completed</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Admin Remarks <span id="remarksRequired"
                                style="color: #f44336; display: none;">*</span></label>
                        <textarea name="remarks" id="adminRemarks"
                            placeholder="Add notes or feedback for the applicant..."></textarea>
                        <small id="remarksHint" style="color: #f44336; display: none; margin-top: 0.5rem;">
                            Remarks are required when rejecting an application
                        </small>
                    </div>

                    <div class="form-group">
                        <label style="display: flex; align-items: center; cursor: pointer;">
                            <input type="checkbox" name="send_email" value="1" checked>
                            <span>Send email notification to applicant</span>
                        </label>
                    </div>

                    <div class="form-group">
                        <label style="display: flex; align-items: center; cursor: pointer;">
                            <input type="checkbox" name="send_sms" value="1" checked>
                            <span>Send SMS notification to applicant</span>
                        </label>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" onclick="closeUpdateModal()" class="btn btn-secondary">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="submitBtn">Update Status</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Payment Request Modal -->
    <div id="paymentModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-money-bill-wave"></i> Approve with Payment Request</h3>
                <button class="modal-close" onclick="closePaymentModal()">&times;</button>
            </div>

            <form id="paymentForm">
                <div class="modal-body">
                    <input type="hidden" name="app_id" value="<?php echo $app_id; ?>">
                    <input type="hidden" name="status" value="Approved">
                    <input type="hidden" name="send_email" value="1">

                    <div class="form-group">
                        <label>Payment Amount (₱) *</label>
                        <input type="number" name="payment_amount" id="paymentAmount" min="0" step="0.01"
                            value="<?php echo $app['base_fee']; ?>" required placeholder="0.00">
                        <small style="color: var(--text-secondary); display: block; margin-top: 0.5rem;">
                            Base Fee: ₱<?php echo number_format($app['base_fee'], 2); ?>
                        </small>
                    </div>

                    <div class="form-group">
                        <label>Payment Deadline</label>
                        <input type="text" value="3 days from approval" readonly
                            style="background: var(--background); cursor: not-allowed;">
                        <small style="color: var(--text-secondary); display: block; margin-top: 0.5rem;">
                            Deadline will be automatically set to 3 days from now
                        </small>
                    </div>

                    <div class="form-group">
                        <label>Payment Instructions</label>
                        <textarea readonly style="background: var(--background); cursor: not-allowed;" rows="4">Pay via GCash to 09690805901
Account Name: LGU Carmona
Reference: Your Tracking Number
Upload screenshot as proof after payment</textarea>
                    </div>

                    <div class="form-group">
                        <label>Admin Remarks (Optional)</label>
                        <textarea name="remarks" id="paymentRemarks"
                            placeholder="Additional notes for the applicant..."></textarea>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" onclick="closePaymentModal()" class="btn btn-secondary">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="paymentSubmitBtn">
                        <i class="fas fa-check"></i> Approve & Request Payment
                    </button>
                </div>
            </form>
        </div>
    </div>
    <!-- Feedback Modal -->
    <div id="feedbackModal" class="modal">
        <div class="modal-content" style="max-width: 500px;">
            <div class="modal-header">
                <h3 id="feedbackTitle">Status Update</h3>
                <button class="modal-close" onclick="closeFeedbackModal()">&times;</button>
            </div>
            <div class="modal-body" id="feedbackBody" style="padding: 2rem;">
                <!-- Content will be inserted dynamically -->
            </div>
            <div class="modal-footer" style="justify-content: center;">
                <button onclick="closeFeedbackModal(); window.location.reload();" class="btn btn-primary"
                    style="background: var(--primary) !important; color: white !important; border: none !important;">
                    OK
                </button>
            </div>
        </div>
    </div>

    <script>
        // Feedback Modal Functions
        function showFeedbackModal(title, message) {
            document.getElementById('feedbackTitle').textContent = title;
            document.getElementById('feedbackBody').innerHTML = message;
            document.getElementById('feedbackModal').classList.add('show');
        }

        function closeFeedbackModal() {
            document.getElementById('feedbackModal').classList.remove('show');
        }


        // Store base fee for checking
        const baseFee = <?php echo $app['base_fee']; ?>;

        function openUpdateModal() {
            document.getElementById('updateModal').classList.add('show');
        }

        function closeUpdateModal() {
            document.getElementById('updateModal').classList.remove('show');
            document.getElementById('updateStatusForm').reset();
        }

        function openPaymentModal() {
            // Transfer remarks from status modal to payment modal
            const statusRemarks = document.getElementById('adminRemarks').value;
            document.getElementById('paymentRemarks').value = statusRemarks;

            // Close status modal and open payment modal
            closeUpdateModal();
            document.getElementById('paymentModal').classList.add('show');
        }

        function closePaymentModal() {
            document.getElementById('paymentModal').classList.remove('show');
            document.getElementById('paymentForm').reset();
        }
        // Handle status form submission
        document.getElementById('updateStatusForm').addEventListener('submit', function (e) {
            e.preventDefault();

            const formData = new FormData(this);
            const newStatus = formData.get('status');
            const currentStatus = '<?php echo $app['status']; ?>';
            const remarks = formData.get('remarks').trim();

            // If status is Rejected, require remarks
            if (newStatus === 'Rejected' && remarks === '') {
                showFeedbackModal('Remarks Required',
                    '<p style="text-align: center;"><i class="fas fa-exclamation-triangle" style="color: #ff9800; font-size: 2rem; margin-bottom: 1rem;"></i><br>Please provide remarks explaining why this application is being rejected.</p>');
                return;
            }
            if (newStatus === currentStatus && remarks === '') {
                showFeedbackModal('No Changes',
                    '<p style="text-align: center;"><i class="fas fa-exclamation-triangle" style="color: #ff9800; font-size: 2rem; margin-bottom: 1rem;"></i><br>No changes to update.<br>Status is already <strong>' + currentStatus + '</strong>.</p>' +
                    '<p style="text-align: center;">Please add remarks if you want to update.</p>');
                return;
            }

            // If status is Approved and there's a base fee, show payment modal
            if (newStatus === 'Approved' && baseFee > 0) {
                openPaymentModal();
                return;
            }

            // Otherwise, submit normally
            submitStatusUpdate(formData);
        });
        // Handle payment form submission
        document.getElementById('paymentForm').addEventListener('submit', function (e) {
            e.preventDefault();

            const submitBtn = document.getElementById('paymentSubmitBtn');
            const originalText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';

            const formData = new FormData(this);

            fetch('../api/approve_with_payment.php', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        closePaymentModal();

                        const paymentAmount = data.payment_amount || formData.get('payment_amount');
                        const feedbackHTML = `
                            <div style="text-align: center;">
                                <div style="font-size: 3rem; color: #4CAF50; margin-bottom: 1rem;">
                                    <i class="fas fa-check-circle"></i>
                                </div>
                                <h3 style="margin-bottom: 1rem;">Application Approved!</h3>
                                <p style="margin-bottom: 1rem;">Payment request sent to user</p>
                                <div style="background: #f5f5f5; padding: 1rem; border-radius: 8px; margin: 1rem 0;">
                                    <p style="margin: 0.5rem 0;"><strong>Payment Amount:</strong> ₱${parseFloat(paymentAmount).toFixed(2)}</p>
                                    <p style="margin: 0.5rem 0;"><strong>Deadline:</strong> 3 days from now</p>
                                </div>
                                <p style="margin-bottom: 0.5rem;">Applicant notified via:</p>
                                <p style="margin: 0;">
                                    <i class="fas fa-envelope" style="color: #2196F3;"></i> Email: <strong style="color: #4CAF50;">✓ Sent</strong><br>
                                    <i class="fas fa-sms" style="color: #2196F3;"></i> SMS: <strong style="color: ${data.sms_sent ? '#4CAF50;">✓ Sent' : '#f44336;">✗ Failed'}</strong>
                                </p>
                            </div>
                        `;

                        showFeedbackModal('SUCCESS', feedbackHTML);
                    } else {
                        closePaymentModal();
                        showFeedbackModal('ERROR', '<p style="text-align: center; color: #f44336;">' + data.message + '</p>');
                    }
                })
                .catch(error => {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                    showFeedbackModal('ERROR', '<p style="text-align: center; color: #f44336;">' + error.message + '</p>');
                });
        });

        // Submit status update (for non-approval statuses)
        function submitStatusUpdate(formData) {
            const submitBtn = document.getElementById('submitBtn');
            const originalText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Updating...';

            fetch('../api/update_application.php', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        closeUpdateModal();

                        const status = formData.get('status');
                        const statusColors = {
                            'Pending': '#ff9800',
                            'Processing': '#2196F3',
                            'Approved': '#4CAF50',
                            'Rejected': '#f44336',  // Add this line
                            'Completed': '#4CAF50',
                            'Cancelled': '#9e9e9e'
                        };
                        const statusIcons = {
                            'Pending': 'fa-clock',
                            'Processing': 'fa-spinner',
                            'Approved': 'fa-check-circle',
                            'Rejected': 'fa-times-circle',  // Add this line,
                            'Completed': 'fa-check-double',
                            'Cancelled': 'fa-ban'
                        };

                        const feedbackHTML = `
                            <div style="text-align: center;">
                                <div style="font-size: 3rem; color: ${statusColors[status] || '#4CAF50'}; margin-bottom: 1rem;">
                                    <i class="fas ${statusIcons[status] || 'fa-check'}"></i>
                                </div>
                                <h3 style="margin-bottom: 1rem;">Status Updated to '${status}'</h3>
                                <p style="margin-bottom: 0.5rem;">Applicant notified via:</p>
                                <p style="margin: 0;">
                                    <i class="fas fa-envelope" style="color: #2196F3;"></i> Email: <strong style="color: #4CAF50;">✓ Sent</strong><br>
                                    <i class="fas fa-sms" style="color: #2196F3;"></i> SMS: <strong style="color: ${data.sms_sent ? '#4CAF50;">✓ Sent' : '#f44336;">✗ Failed'}</strong>
                                </p>
                            </div>
                        `;

                        showFeedbackModal('SUCCESS', feedbackHTML);
                    } else {
                        closeUpdateModal();
                        showFeedbackModal('ERROR', '<p style="text-align: center; color: #f44336;">' + data.message + '</p>');
                    }
                })
                .catch(error => {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                    showFeedbackModal('ERROR', '<p style="text-align: center; color: #f44336;">' + error.message + '</p>');
                });
        }
        // Close modals when clicking outside
        window.onclick = function (event) {
            const updateModal = document.getElementById('updateModal');
            const paymentModal = document.getElementById('paymentModal');
            const feedbackModal = document.getElementById('feedbackModal');

            if (event.target === updateModal) closeUpdateModal();
            if (event.target === paymentModal) closePaymentModal();
            if (event.target === feedbackModal) closeFeedbackModal();
        }

        // Close modals on ESC key
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                closeUpdateModal();
                closePaymentModal();
                closeFeedbackModal();
            }
        });
        // Show/hide remarks required indicator based on status
        document.getElementById('newStatus').addEventListener('change', function () {
            const remarksRequired = document.getElementById('remarksRequired');
            const remarksHint = document.getElementById('remarksHint');
            const adminRemarks = document.getElementById('adminRemarks');

            if (this.value === 'Rejected') {
                remarksRequired.style.display = 'inline';
                remarksHint.style.display = 'block';
                adminRemarks.style.borderColor = '#f44336';
            } else {
                remarksRequired.style.display = 'none';
                remarksHint.style.display = 'none';
                adminRemarks.style.borderColor = '';
            }
        });
    </script>

</body>

</html>

<?php include '../includes/footer.php'; ?>