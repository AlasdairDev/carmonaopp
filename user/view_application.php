<?php
session_start();
require_once '../config.php';
require_once '../includes/functions.php';

// Check if user is logged in
if (!isLoggedIn() || $_SESSION['role'] !== 'user') {
    header('Location: ../auth/login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$application_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if (!$application_id) {
    $_SESSION['error'] = 'Invalid application ID';
    header('Location: applications.php');
    exit();
}

// Get application details
$query = "SELECT a.*,
          COALESCE(s.service_name, 'Legacy Service') as service_name,
          COALESCE(s.description, a.purpose) as description,
          COALESCE(s.processing_days, 7) as processing_days,
          COALESCE(s.base_fee, 0) as fee,
          COALESCE(d.name, 'General Services') as department_name,
          u.name as applicant_name, u.email, u.mobile as mobile_number, u.address
          FROM applications a
          LEFT JOIN services s ON a.service_id = s.id
          LEFT JOIN departments d ON a.department_id = d.id
          JOIN users u ON a.user_id = u.id
          WHERE a.id = ? AND a.user_id = ?";

$stmt = $pdo->prepare($query);
$stmt->execute([$application_id, $user_id]);
$application = $stmt->fetch(PDO::FETCH_ASSOC);
// Auto-cancel expired applications
if (
    $application['payment_required'] &&
    $application['payment_status'] === 'pending' &&
    $application['status'] !== 'cancelled'
) {

    $payment_deadline = new DateTime($application['payment_deadline']);
    $now = new DateTime();

    if ($now > $payment_deadline) {
        // Automatically cancel the application
        $cancel_query = "UPDATE applications 
                        SET status = 'cancelled',
                            payment_status = 'expired',
                            updated_at = NOW() 
                        WHERE id = ?";
        $stmt_cancel = $pdo->prepare($cancel_query);
        $stmt_cancel->execute([$application_id]);

        // Refresh application data
        $application['status'] = 'cancelled';
        $application['payment_status'] = 'expired';
    }
}
if (!$application) {
    $_SESSION['error'] = 'Application not found or you do not have permission to view it';
    header('Location: applications.php');
    exit();
}

// Get documents
$doc_query = "SELECT * FROM documents WHERE application_id = ? ORDER BY uploaded_at DESC";
$doc_stmt = $pdo->prepare($doc_query);
$doc_stmt->execute([$application_id]);
$documents = $doc_stmt->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = 'Application Details';
include '../includes/header.php';
?>
<link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/user-responsive.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
    :root {
        --primary: #7cb342;
        --primary-dark: #689f38;
        --secondary: #9ccc65;
        --text-dark: #2d3748;
        --text-light: #718096;
        --bg-light: #f8faf8;
    }

    body {
        background: linear-gradient(135deg, #7cb342 0%, #9ccc65 100%);
        min-height: 100vh;
        box-sizing: border-box;
    }

    .wrapper {
        background: #ffffff;
        min-height: calc(100vh - 40px);
        margin: 20px;
        border-radius: 20px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
        padding: 3rem 2rem;
    }

    .page-wrapper {
        position: relative;
        padding: 0;
    }

    .container {
        max-width: 1400px;
        margin: 0 auto;
        padding: 0 2rem;
    }

    .back-btn {
        display: inline-flex;
        gap: 0.5rem;
        padding: 0.75rem 1.5rem;
        border-radius: 10px;
        border: 2px solid #e2e8f0;
        background: white;
        color: var(--text-dark);
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
        text-decoration: none;
        font-size: 0.95rem;
        margin-bottom: 2rem;
    }

    .back-btn:hover {
        background: var(--primary);
        color: white;
        border-color: var(--primary);
    }

    /* Page Header */
    .page-header {
        background: linear-gradient(135deg, #7cb342 0%, #9ccc65 100%);
        border-radius: 16px;
        padding: 2rem;
        margin-bottom: 2rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        box-shadow: 0 4px 12px rgba(124, 179, 66, 0.25);
    }

    .page-header h1 {
        color: white;
        font-size: 1.75rem;
        font-weight: 700;
        margin: 0 0 0.5rem 0;
    }

    .page-header .tracking-info {
        color: rgba(255, 255, 255, 0.95);
        font-size: 0.95rem;
    }

    .page-header .tracking-number {
        color: white;
        font-family: 'Courier New', monospace;
        font-weight: 700;
        font-size: 1rem;
    }

    /* Status Badge */
    .status-badge {
        padding: 0.6rem 1.25rem;
        border-radius: 30px;
        font-size: 0.85rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    /* Pending - Orange */

    .status-pending {
        background: rgba(255, 107, 53, 0.15);
        color: #ff6b35;
    }

    /* Processing - Yellow */
    .status-processing {
        background: rgba(251, 191, 36, 0.15);
        color: #f59e0b;
    }

    /* Approved - Blue */
    .status-approved {
        background: rgba(59, 130, 246, 0.15);
        color: #2563eb;
    }

    /* Paid - Green */
    .status-paid {
        background: rgba(16, 185, 129, 0.15);
        color: #059669;
    }

    /* Completed - Cyan/Light Blue */
    .status-completed {
        background: rgba(6, 182, 212, 0.15);
        color: #0891b2;
    }

    /* Rejected - Red */
    .status-rejected {
        background: rgba(239, 68, 68, 0.15);
        color: #dc2626;
    }

    /* Cancelled - Brown */
    .status-cancelled {
        background: rgba(146, 64, 14, 0.15);
        color: #92400e;
    }

    /* Solid color for cancelled status badge in page header */
    .page-header .status-badge.status-cancelled {
        background: #92400e;
        color: white;
    }

    /* Solid colors for status badge in page header */
    .page-header .status-badge.status-pending {
        background: #ff6b35;
        color: white;
    }

    .page-header .status-badge.status-processing {
        background: #fbbf24;
        color: #854d0e;
    }

    .page-header .status-badge.status-approved {
        background: #3b82f6;
        color: white;
    }

    .page-header .status-badge.status-paid {
        background: #10b981;
        color: white;
    }

    .page-header .status-badge.status-completed {
        background: #06b6d4;
        color: white;
    }

    .page-header .status-badge.status-rejected {
        background: #ef4444;
        color: white;
    }

    /* Payment Status Badges */
    .payment-badge {
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        margin-left: 0.5rem;
    }

    .payment-required {
        background: #ffc107;
        color: #ffffff;
        border: none;
    }

    .payment-submitted {
        background: #0d6efd;
        color: #ffffff;
        border: none;
    }

    .payment-verified {
        background: #198754;
        color: #ffffff;
        border: none;
    }

    .payment-rejected {
        background: #dc3545;
        color: #ffffff;
        border: none;
    }

    .payment-expired {
        background: #f5f5f5;
        color: #6c757d;
    }

    /* Cards */
    .card {
        background: white;
        border-radius: 16px;
        border: 1px solid #e2e8f0;
        margin-bottom: 1.5rem;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
    }

    .card-header {
        padding: 1.25rem 1.75rem;
        border-bottom: 1px solid #e2e8f0;
        background: linear-gradient(135deg, #7cb342 0%, #9ccc65 100%);
    }

    .card-header h2,
    .card-header h3 {
        color: white;
        font-size: 1.15rem;
        font-weight: 700;
        margin: 0;
    }

    .card-body {
        padding: 1.75rem;
    }

    /* Table Styles */
    .table {
        width: 100%;
        border-collapse: collapse;
    }

    .table tr {
        border-bottom: 1px solid #f1f5f9;
    }

    .table tr:last-child {
        border-bottom: none;
    }

    .table td {
        padding: 0.875rem 0;
        vertical-align: top;
        font-size: 0.95rem;
    }

    .table td:first-child {
        color: var(--text-light);
        font-weight: 600;
        width: 30%;
    }

    .table td:last-child {
        color: var(--text-dark);
    }

    /* Section Divider */
    hr {
        border: none;
        border-top: 1px solid #e2e8f0;
        margin: 1.5rem 0;
    }

    /* Document Items */
    .document-item {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 1.25rem;
        background: #f8fafb;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        margin-bottom: 0.875rem;
        transition: all 0.2s ease;
    }

    .document-item:hover {
        background: white;
        border-color: var(--primary);
        box-shadow: 0 2px 12px rgba(124, 179, 66, 0.15);
    }

    .doc-icon {
        width: 48px;
        height: 48px;
        flex-shrink: 0;
        color: var(--primary);
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .doc-info {
        flex: 1;
    }

    .doc-info h5 {
        color: var(--text-dark);
        font-size: 0.95rem;
        font-weight: 600;
        margin: 0 0 0.375rem 0;
    }

    .doc-meta {
        color: var(--text-light);
        font-size: 0.85rem;
    }

    .doc-actions {
        display: flex;
        gap: 0.5rem;
    }

    /* Buttons */
    .btn {
        padding: 0.625rem 1.25rem;
        border-radius: 8px;
        font-weight: 600;
        border: none;
        cursor: pointer;
        transition: all 0.2s ease;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.9rem;
    }

    .btn-primary {
        background: var(--primary);
        color: white;
    }

    .btn-primary:hover {
        background: var(--primary-dark);
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(124, 179, 66, 0.3);
    }

    .btn-sm {
        padding: 0.5rem 1rem;
        font-size: 0.85rem;
    }

    .btn-block {
        width: 100%;
        justify-content: center;
    }

    /* Alert */
    .alert {
        padding: 1.25rem;
        border-radius: 12px;
        margin: 1.5rem 0;
        border-left: 4px solid;
    }

    .alert-info {
        background: #e3f2fd;
        border-left-color: #2196F3;
    }

    .alert strong {
        color: var(--text-dark);
        display: block;
        margin-bottom: 0.375rem;
        font-size: 0.95rem;
    }

    .alert p {
        color: var(--text-light);
        margin: 0;
        font-size: 0.9rem;
    }

    /* Copy Button */
    .copy-btn {
        margin-left: 0.5rem;
        padding: 0.375rem 0.875rem;
        border: 1px solid rgba(255, 255, 255, 0.3);
        background: rgba(255, 255, 255, 0.2);
        color: white;
        border-radius: 6px;
        font-size: 0.8rem;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .copy-btn:hover {
        background: white;
        color: var(--primary);
    }

    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 2.5rem;
        color: var(--text-light);
        font-size: 0.95rem;
    }

    /* Layout Grid */
    .row {
        display: grid;
        grid-template-columns: 1fr 380px;
        gap: 1.5rem;
    }

    .col-lg-8 {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
    }

    .col-lg-4 {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
    }

    /* Mini Card Items */
    .mini-card-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.625rem 0;
    }

    .mini-label {
        font-size: 0.9rem;
        color: var(--text-light);
        font-weight: 500;
    }

    .mini-value {
        font-size: 0.9rem;
        color: var(--text-dark);
        font-weight: 600;
    }

    .mini-value.highlight {
        color: var(--primary);
        font-size: 1.25rem;
        font-weight: 700;
    }

    .mini-divider {
        height: 1px;
        background: #e2e8f0;
        margin: 0.5rem 0;
    }

    /* Modal Styles */
    .modal {
        display: none;
        position: fixed;
        z-index: 9999;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        animation: fadeIn 0.2s ease;
    }

    .modal.show {
        display: flex;
        align-items: center;
        justify-content: center;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
        }

        to {
            opacity: 1;
        }
    }

    .modal-content {
        background: white;
        border-radius: 16px;
        width: 90%;
        max-width: 500px;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        animation: slideUp 0.3s ease;
    }

    @keyframes slideUp {
        from {
            transform: translateY(50px);
            opacity: 0;
        }

        to {
            transform: translateY(0);
            opacity: 1;
        }
    }

    .modal-header {
        padding: 1.5rem;
        border-bottom: 1px solid #e2e8f0;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .modal-close {
        background: none;
        border: none;
        font-size: 2rem;
        color: #94a3b8;
        cursor: pointer;
        line-height: 1;
        padding: 0;
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 6px;
        transition: all 0.2s;
    }

    .modal-close:hover {
        background: #fee;
        color: #dc2626;
    }

    .modal-body {
        padding: 1.5rem;
    }

    .modal-footer {
        padding: 1.25rem 1.5rem;
        border-top: 1px solid #e2e8f0;
        display: flex;
        gap: 0.75rem;
        justify-content: flex-end;
    }

    .btn-secondary {
        padding: 0.75rem 1.5rem;
        border: 1px solid #e2e8f0;
        background: white;
        color: #475569;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
        font-size: 0.95rem;
    }

    .btn-secondary:hover {
        background: #f8fafc;
        border-color: #cbd5e1;
    }

    .btn-danger {
        padding: 0.75rem 1.5rem;
        border: none;
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        color: white;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
        font-size: 0.95rem;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }

    .btn-danger:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(239, 68, 68, 0.4);
    }

    .btn-danger:disabled {
        opacity: 0.6;
        cursor: not-allowed;
        transform: none;
    }

    /* Print Styles */
    @media print {
        body {
            background: white !important;
        }

        .wrapper {
            margin: 0;
            box-shadow: none;
        }

        .back-btn,
        .copy-btn,
        .doc-actions,
        .col-lg-4 {
            display: none !important;
        }

        .card {
            border: 1px solid #ddd;
            box-shadow: none;
        }
    }

    /* Success/Error Modal Overlay */
    .modal-overlay {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.5);
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 10000;
        backdrop-filter: blur(4px);
    }

    .modal-overlay.show {
        display: flex;
    }

    .modal-container {
        background: white;
        border-radius: 16px;
        padding: 2rem;
        max-width: 450px;
        width: 90%;
        text-align: center;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    }

    @keyframes modalSlideIn {
        from {
            transform: scale(0.9);
            opacity: 0;
        }

        to {
            transform: scale(1);
            opacity: 1;
        }
    }

    .modal-icon {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1.5rem;
    }
</style>
<div class="wrapper">
    <div class="page-wrapper">
        <div class="container">
            <!-- Back Button and Cancel Button -->
            <div
                style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
                <a href="applications.php" class="back-btn">
                    &larr; Back to Applications
                </a>

                <?php if ($application['status'] === 'pending'): ?>
                    <button onclick="confirmCancel(<?php echo $application['id']; ?>)"
                        style="background: linear-gradient(135deg, #ef5350 0%, #e53935 100%); color: white; border: none; padding: 0.75rem 1.5rem; border-radius: 8px; font-size: 0.95rem; font-weight: 600; cursor: pointer; box-shadow: 0 2px 8px rgba(239, 83, 80, 0.3); transition: all 0.3s ease; display: inline-flex; align-items: center; gap: 0.5rem;"
                        onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 12px rgba(239, 83, 80, 0.4)';"
                        onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 8px rgba(239, 83, 80, 0.3)';">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="18" y1="6" x2="6" y2="18"></line>
                            <line x1="6" y1="6" x2="18" y2="18"></line>
                        </svg>
                        Cancel Application
                    </button>
                <?php endif; ?>
            </div>

            <!-- Page Header -->
            <div class="page-header">
                <div>
                    <h1>Application Details</h1>
                    <p class="tracking-info">
                        Tracking Number:
                        <span
                            class="tracking-number"><?php echo htmlspecialchars($application['tracking_number']); ?></span>
                        <button onclick="copyToClipboard('<?php echo $application['tracking_number']; ?>')"
                            class="copy-btn">
                            Copy
                        </button>
                    </p>
                </div>
                <div style="display: flex; gap: 0.5rem; align-items: center; flex-wrap: wrap;">
                    <span class="status-badge status-<?php echo strtolower($application['status']); ?>">
                        <?php echo strtoupper($application['status']); ?>
                    </span>

                    <?php if ($application['payment_required']): ?>
                        <?php if ($application['payment_status'] === 'pending'): ?>
                            <span class="payment-badge payment-required">
                                PAYMENT REQUIRED
                            </span>
                        <?php elseif ($application['payment_status'] === 'submitted'): ?>
                            <span class="payment-badge payment-submitted">
                                UNDER VERIFICATION
                            </span>
                        <?php elseif ($application['payment_status'] === 'verified'): ?>
                            <span class="payment-badge payment-verified">
                                PAYMENT REQUIRED
                            </span>
                        <?php elseif ($application['payment_status'] === 'rejected'): ?>
                            <span class="payment-badge payment-rejected">
                                PAYMENT REJECTED
                            </span>
                        <?php elseif ($application['payment_status'] === 'expired'): ?>
                            <span class="payment-badge payment-expired">
                                PAYMENT EXPIRED
                            </span>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>

            <div class="row">
                <!-- Left Column -->
                <div class="col-lg-8">
                    <!-- Application Information -->
                    <div class="card">
                        <div class="card-header">
                            <h2>Application Information</h2>
                        </div>
                        <div class="card-body">
                            <table class="table">
                                <tbody>
                                    <tr>
                                        <td><strong>Tracking Number:</strong></td>
                                        <td>
                                            <span
                                                style="color: var(--primary); font-family: monospace; font-size: 1rem; font-weight: 700;">
                                                <?php echo htmlspecialchars($application['tracking_number']); ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><strong>Service:</strong></td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($application['service_name']); ?></strong>
                                            <p style="color: #94a3b8; margin: 0.25rem 0 0 0; font-size: 0.875rem;">
                                                <?php echo htmlspecialchars($application['description']); ?>
                                            </p>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><strong>Department:</strong></td>
                                        <td><?php echo htmlspecialchars($application['department_name']); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Date Submitted:</strong></td>
                                        <td><?php echo formatDate($application['created_at']); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Last Updated:</strong></td>
                                        <td><?php echo formatDate($application['updated_at']); ?></td>
                                    </tr>
                                    <?php if ($application['payment_required']): ?>
                                        <tr>
                                            <td><strong>Payment Amount:</strong></td>
                                            <td>
                                                <strong style="color: var(--primary); font-size: 1.1rem;">
                                                    <?php echo number_format($application['payment_amount'], 2); ?>
                                                </strong>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>

                            <hr>

                            <h3
                                style="color: var(--text-dark); margin-bottom: 1rem; font-size: 1.1rem; font-weight: 700;">
                                Application Details</h3>
                            <table class="table">
                                <tbody>
                                    <tr>
                                        <td><strong>Purpose:</strong></td>
                                        <td><?php echo nl2br(htmlspecialchars($application['purpose'])); ?></td>
                                    </tr>
                                    <?php if ($application['location']): ?>
                                        <tr>
                                            <td><strong>Location:</strong></td>
                                            <td><?php echo htmlspecialchars($application['location']); ?></td>
                                        </tr>
                                    <?php endif; ?>
                                    <?php if ($application['remarks']): ?>
                                        <tr>
                                            <td><strong>Admin Remarks:</strong></td>
                                            <td>
                                                <div
                                                    style="background: #f8f9fa; padding: 1rem; border-radius: 8px; border-left: 4px solid var(--primary);">
                                                    <?php echo nl2br(htmlspecialchars($application['remarks'])); ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <!-- Uploaded Documents -->
                    <div class="card">
                        <div class="card-header">
                            <h2>Uploaded Documents</h2>
                        </div>
                        <div class="card-body">
                            <?php if (count($documents) > 0): ?>
                                <?php foreach ($documents as $doc): ?>
                                    <div class="document-item">
                                        <div class="doc-icon">
                                            <?php
                                            $ext = strtolower(pathinfo($doc['filename'], PATHINFO_EXTENSION));
                                            if ($ext === 'pdf') {
                                                echo '<svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line></svg>';
                                            } else {
                                                echo '<svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><polyline points="21 15 16 10 5 21"></polyline></svg>';
                                            }
                                            ?>
                                        </div>
                                        <div class="doc-info">
                                            <h5><?php echo htmlspecialchars($doc['filename']); ?></h5>
                                            <div class="doc-meta">
                                                Type: <?php echo strtoupper($ext); ?> |
                                                Size: <?php echo number_format($doc['file_size'] / 1024, 2); ?> KB |
                                                Uploaded: <?php echo formatDate($doc['uploaded_at']); ?>
                                            </div>
                                        </div>
                                        <div class="doc-actions">
                                            <a href="../<?php echo htmlspecialchars($doc['file_path']); ?>" target="_blank"
                                                class="btn btn-sm btn-primary">
                                                View
                                            </a>
                                            <a href="../<?php echo htmlspecialchars($doc['file_path']); ?>"
                                                download="<?php echo htmlspecialchars($doc['filename']); ?>"
                                                class="btn btn-sm btn-primary">
                                                Download
                                            </a>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="empty-state">
                                    <p>No documents uploaded</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Applicant Information -->
                    <div class="card">
                        <div class="card-header">
                            <h2>Applicant Information</h2>
                        </div>
                        <div class="card-body">
                            <table class="table">
                                <tbody>
                                    <tr>
                                        <td><strong>Full Name:</strong></td>
                                        <td><?php echo htmlspecialchars($application['applicant_name']); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Email:</strong></td>
                                        <td><?php echo htmlspecialchars($application['email']); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Mobile:</strong></td>
                                        <td><?php echo htmlspecialchars($application['mobile_number']); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Address:</strong></td>
                                        <td><?php echo htmlspecialchars($application['address']); ?></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div
                        style="margin-top: 2rem; margin-bottom: 2rem; text-align: center; display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
                        <?php if ($application['payment_required'] && ($application['payment_status'] === 'pending' || $application['payment_status'] === 'rejected')): ?>
                            <a href="submit_payment.php?id=<?php echo $application['id']; ?>"
                                style="background: linear-gradient(135deg, #ffc107 0%, #ffb300 100%); color: white; border: none; padding: 1rem 3rem; border-radius: 10px; font-size: 1rem; font-weight: 600; cursor: pointer; box-shadow: 0 4px 15px rgba(255, 193, 7, 0.3); transition: all 0.3s ease; display: inline-flex; align-items: center; gap: 0.75rem; text-decoration: none;"
                                onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 20px rgba(255, 193, 7, 0.4)';"
                                onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 15px rgba(255, 193, 7, 0.3)';">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect>
                                    <line x1="1" y1="10" x2="23" y2="10"></line>
                                </svg>
                                <?php echo $application['payment_status'] === 'rejected' ? 'Resubmit Payment' : 'Pay Now'; ?>
                            </a>
                        <?php endif; ?>

                        <button onclick="window.print()"
                            style="background: linear-gradient(135deg, #7cb342 0%, #9ccc65 100%); color: white; border: none; padding: 1rem 3rem; border-radius: 10px; font-size: 1rem; font-weight: 600; cursor: pointer; box-shadow: 0 4px 15px rgba(124, 179, 66, 0.3); transition: all 0.3s ease; display: inline-flex; align-items: center; gap: 0.75rem;"
                            onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 20px rgba(124, 179, 66, 0.4)';"
                            onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 15px rgba(124, 179, 66, 0.3)';">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round">
                                <polyline points="6 9 6 2 18 2 18 9"></polyline>
                                <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2">
                                </path>
                                <rect x="6" y="14" width="12" height="8"></rect>
                            </svg>
                            Print Application
                        </button>
                    </div>
                </div>

                <!-- Right Sidebar -->
                <div class="col-lg-4">

                    <?php if (!empty($application['admin_remarks'])): ?>
                        <div class="card">
                            <div class="card-header">
                                <h3>Admin Remarks</h3>
                            </div>
                            <div class="card-body">
                                <div
                                    style="background: #f0f9ff; padding: 1rem; border-left: 4px solid #3b82f6; border-radius: 8px;">
                                    <span style="color: #1e40af; font-weight: 600; font-size: 0.9rem;">Remarks:</span>
                                    <p style="color: #1e3a8a; margin: 0.5rem 0 0 0; line-height: 1.6; font-size: 0.95rem;">
                                        <?php echo nl2br(htmlspecialchars($application['admin_remarks'])); ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                    <!-- Processing Info -->
                    <div class="card">
                        <div class="card-header">
                            <h3>Processing Information</h3>
                        </div>
                        <div class="card-body">
                            <div class="mini-card-item">
                                <span class="mini-label">Processing Fee</span>
                                <span
                                    class="mini-value highlight"><?php echo formatCurrency($application['fee']); ?></span>
                            </div>
                            <div class="mini-divider"></div>
                            <div class="mini-card-item">
                                <span class="mini-label">Estimated Time</span>
                                <span class="mini-value"><?php echo $application['processing_days']; ?> business
                                    days</span>
                            </div>
                        </div>
                    </div>

                    <!-- Status Guide -->
                    <div class="card">
                        <div class="card-header">
                            <h3>Status Guide</h3>
                        </div>
                        <div class="card-body">
                            <div style="display: flex; flex-direction: column; gap: 1rem;">
                                <div>
                                    <span class="status-badge status-pending"
                                        style="display: inline-block; margin-bottom: 0.5rem;">Pending</span>
                                    <p
                                        style="color: var(--text-light); margin: 0; font-size: 0.875rem; line-height: 1.5;">
                                        Waiting for initial review
                                    </p>
                                </div>
                                <div>
                                    <span class="status-badge status-processing"
                                        style="display: inline-block; margin-bottom: 0.5rem;">Processing</span>
                                    <p
                                        style="color: var(--text-light); margin: 0; font-size: 0.875rem; line-height: 1.5;">
                                        Being evaluated by staff
                                    </p>
                                </div>
                                <div>
                                    <span class="status-badge status-approved"
                                        style="display: inline-block; margin-bottom: 0.5rem;">Approved</span>
                                    <p
                                        style="color: var(--text-light); margin: 0; font-size: 0.875rem; line-height: 1.5;">
                                        Application approved
                                    </p>
                                </div>
                                <div>
                                    <span class="status-badge status-rejected"
                                        style="display: inline-block; margin-bottom: 0.5rem;">Rejected</span>
                                    <p
                                        style="color: var(--text-light); margin: 0; font-size: 0.875rem; line-height: 1.5;">
                                        Not approved - see remarks
                                    </p>
                                </div>
                                <div>
                                    <span class="status-badge status-completed"
                                        style="display: inline-block; margin-bottom: 0.5rem;">Completed</span>
                                    <p
                                        style="color: var(--text-light); margin: 0; font-size: 0.875rem; line-height: 1.5;">
                                        Ready for pickup
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Cancellation Modal -->
    <div id="cancelModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <div style="display: flex; align-items: center; gap: 0.75rem;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                        stroke="#dc2626" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="15" y1="9" x2="9" y2="15"></line>
                        <line x1="9" y1="9" x2="15" y2="15"></line>
                    </svg>
                    <h3 style="margin: 0; color: #1e293b; font-size: 1.25rem;">Cancel Application</h3>
                </div>
                <button onclick="closeCancelModal()" class="modal-close">&times;</button>
            </div>
            <div class="modal-body">
                <label
                    style="display: block; margin-bottom: 0.5rem; color: #475569; font-weight: 600; font-size: 0.95rem;">
                    Cancellation Reason <span style="color: #dc2626;">*</span>
                </label>
                <textarea id="cancellationReason" placeholder="Explain why you are cancelling this application..."
                    rows="5"
                    style="width: 100%; padding: 0.875rem; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 0.95rem; font-family: inherit; resize: vertical; transition: border-color 0.2s;"
                    onfocus="this.style.borderColor='#7cb342'; this.style.outline='none';"
                    onblur="this.style.borderColor='#e2e8f0';"></textarea>
                <p style="color: #64748b; font-size: 0.875rem; margin-top: 0.5rem;">This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button onclick="closeCancelModal()" class="btn-secondary">
                    Cancel
                </button>
                <button onclick="submitCancellation()" class="btn-danger">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                    Cancel Application
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    function copyToClipboard(text) {
        navigator.clipboard.writeText(text).then(() => {
            alert('Tracking number copied to clipboard!');
        }).catch(() => {
            const textarea = document.createElement('textarea');
            textarea.value = text;
            textarea.style.position = 'fixed';
            textarea.style.opacity = '0';
            document.body.appendChild(textarea);
            textarea.select();
            document.execCommand('copy');
            document.body.removeChild(textarea);
            alert('Tracking number copied to clipboard!');
        });
    }

    // Store application ID for modal
    let currentApplicationId = null;

    function confirmCancel(applicationId) {
        currentApplicationId = applicationId;
        document.getElementById('cancelModal').classList.add('show');
        document.getElementById('cancellationReason').value = '';
        document.getElementById('cancellationReason').focus();
    }

    function closeCancelModal() {
        document.getElementById('cancelModal').classList.remove('show');
        currentApplicationId = null;
    } 
    function submitCancellation() {
        const reason = document.getElementById('cancellationReason').value.trim();

        if (reason === '') {
            alert('Please provide a reason for cancellation');
            return;
        }

        // Get submit button
        const submitBtn = document.querySelector('.btn-danger');
        const originalHTML = submitBtn.innerHTML;

        // Disable button and show loading
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span>Processing...</span>';

        // Send cancel request
        fetch('../api/cancel_application.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                application_id: currentApplicationId,
                reason: reason
            })
        })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                closeCancelModal();
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalHTML;

                if (data.success) {
                    showSuccessModal('Application cancelled successfully');
                } else {
                    showErrorModal(data.message || 'Failed to cancel application');
                }
            })
            .catch(error => {
                closeCancelModal();
                console.error('Error:', error);

                // Re-enable the button
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalHTML;

                showErrorModal('Error cancelling application. Please try again.');
            });
    }

    // Add success modal function
    function showSuccessModal(message) {
        const modal = document.createElement('div');
        modal.className = 'modal-overlay show';
        modal.innerHTML = `
        <div class="modal-container" style="animation: modalSlideIn 0.3s ease;">
            <div class="modal-icon" style="background: #dcfce7;">
                <i class="fas fa-check-circle" style="color: #16a34a; font-size: 2.5rem;"></i>
            </div>
            <h2 style="font-size: 1.5rem; font-weight: 700; margin-bottom: 0.75rem; color: #1e293b;">Success!</h2>
            <p style="font-size: 1rem; color: #64748b; margin-bottom: 1.5rem;">${message}</p>
            <button onclick="window.location.reload()" 
                style="padding: 0.75rem 2rem; border-radius: 8px; font-weight: 600; font-size: 1rem; 
                cursor: pointer; border: none; background: #16a34a; color: white; transition: all 0.2s ease;">
                OK
            </button>
        </div>
    `;
        document.body.appendChild(modal);
    }

    // Add error modal function
    function showErrorModal(message) {
        const modal = document.createElement('div');
        modal.className = 'modal-overlay show';
        modal.innerHTML = `
        <div class="modal-container" style="animation: modalSlideIn 0.3s ease;">
            <div class="modal-icon" style="background: #fee2e2;">
                <i class="fas fa-exclamation-circle" style="color: #ef4444; font-size: 2.5rem;"></i>
            </div>
            <h2 style="font-size: 1.5rem; font-weight: 700; margin-bottom: 0.75rem; color: #1e293b;">Error</h2>
            <p style="font-size: 1rem; color: #64748b; margin-bottom: 1.5rem;">${message}</p>
            <button onclick="this.closest('.modal-overlay').remove()" 
                style="padding: 0.75rem 2rem; border-radius: 8px; font-weight: 600; font-size: 1rem; 
                cursor: pointer; border: none; background: #ef4444; color: white; transition: all 0.2s ease;">
                OK
            </button>
        </div>
    `;
        document.body.appendChild(modal);
    }
    // Close modal when clicking outside
    window.onclick = function (event) {
        const modal = document.getElementById('cancelModal');
        if (event.target === modal) {
            closeCancelModal();
        }
    }

    // Close modal on ESC key
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            closeCancelModal();
        }
    });
</script>

<?php include '../includes/footer.php'; ?>
