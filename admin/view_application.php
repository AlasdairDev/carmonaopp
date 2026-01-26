<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/functions.php';

if (!isLoggedIn() || !isAdmin()) {
    header('Location: ../login.php');
    exit();
}

$app_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$app_id) {
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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        :root {
            --primary: #8bc34a;
            --primary-dark: #689f38;
            --secondary: #558b2f;
            --background: #f5f7fa;
            --surface: #ffffff;
            --text-primary: #2c3e50;
            --text-secondary: #64748b;
            --border: #e2e8f0;
            --radius: 12px;
        }

        body {
            font-family: 'Inter', -apple-system, sans-serif;
            background: var(--background);
            color: var(--text-primary);
            line-height: 1.6;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 1.5rem 1.5rem 1.5rem;
        }

        /* Header */
        .page-header {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            border-radius: var(--radius);
            padding: 2rem;
            color: white;
            margin-bottom: 1.5rem;
            box-shadow: 0 10px 15px rgba(0, 0, 0, 0.1);
            position: relative;
            overflow: hidden;
        }

        .page-header::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 300px;
            height: 300px;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            border-radius: 50%;
            transform: translate(30%, -30%);
        }

        .header-content {
            position: relative;
            z-index: 1;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header-left a {
            color: white;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 0.5rem;
            font-size: 0.9375rem;
            opacity: 0.9;
        }

        .header-left a:hover {
            opacity: 1;
        }

        .header-left h1 {
            font-size: 2rem;
            font-weight: 800;
            margin-bottom: 0.25rem;
        }

        .header-left p {
            font-size: 1rem;
            opacity: 0.95;
        }

        .header-actions {
            display: flex;
            gap: 0.75rem;
        }

        .btn {
            padding: 0.625rem 1.25rem;
            border-radius: 8px;
            font-weight: 600;
            text-decoration: none;
            border: none;
            cursor: pointer;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.875rem;
        }

        .btn-white {
            background: rgba(255, 255, 255, 0.95);
            color: var(--primary);
        }

        .btn-white:hover {
            background: white;
            transform: translateY(-2px);
        }

        /* Status Banner */
        .status-banner {
            background: white;
            border-radius: var(--radius);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .status-info h2 {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .status-badge {
            padding: 0.5rem 1rem;
            border-radius: 50px;
            font-weight: 700;
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status-pending { background: #fef3c7; color: #92400e; }
        .status-processing { background: #dbeafe; color: #1e40af; }
        .status-approved { background: #d1fae5; color: #065f46; }
        .status-paid { background: #c6f6d5; color: #22543d; }
        .status-rejected { background: #fee2e2; color: #991b1b; }
        .status-completed { background: #e0e7ff; color: #3730a3; }

        .status-meta {
            color: var(--text-secondary);
            font-size: 0.875rem;
            margin-top: 0.5rem;
        }

        .status-action .btn {
            padding: 0.75rem 1.5rem;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(139, 195, 74, 0.4);
        }

        .btn-secondary {
            background: var(--border);
            color: var(--text-primary);
        }

        .modal-footer .btn {
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
        }

        
        .btn-secondary:hover {
            background: #cbd5e1;
        }

        .btn-sm {
            padding: 0.5rem 1rem;
            font-size: 0.8125rem;
        }

        /* Grid */
        .grid-2 {
            display: grid;
            grid-template-columns: 1fr 400px;
            gap: 1.5rem;
        }

        /* Card */
        .card {
            background: white;
            border-radius: var(--radius);
            padding: 1.5rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            margin-bottom: 1.5rem;
        }

        .card h3 {
            font-size: 1.125rem;
            font-weight: 700;
            margin-bottom: 1.25rem;
            color: var(--text-primary);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .card h3 i {
            color: var(--primary);
        }

        /* Info Items */
        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
        }

        .info-item {
            padding: 1rem;
            background: var(--background);
            border-radius: 8px;
            border-left: 3px solid var(--primary);
        }

        .info-label {
            font-size: 0.75rem;
            font-weight: 700;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.375rem;
        }

        .info-value {
            font-size: 0.9375rem;
            font-weight: 600;
            color: var(--text-primary);
        }

        /* Documents */
        .document-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem;
            background: var(--background);
            border-radius: 8px;
        }

        .doc-icon {
            width: 48px;
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            border-radius: 8px;
            color: white;
            font-size: 1.5rem;
        }

        .doc-info {
            flex: 1;
        }

        .doc-name {
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 0.25rem;
        }

        .doc-size {
            font-size: 0.8125rem;
            color: var(--text-secondary);
        }

        .doc-actions {
            display: flex;
            gap: 0.5rem;
        }

        /* Timeline */
        .timeline {
            position: relative;
        }

        .timeline-item {
            position: relative;
            padding-left: 2rem;
            padding-bottom: 1.5rem;
        }

        .timeline-item:last-child {
            padding-bottom: 0;
        }

        .timeline-item::before {
            content: '';
            position: absolute;
            left: 0.4375rem;
            top: 0;
            bottom: 0;
            width: 2px;
            background: var(--border);
        }

        .timeline-item:last-child::before {
            display: none;
        }

        .timeline-item::after {
            content: '';
            position: absolute;
            left: 0;
            top: 0.375rem;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: var(--primary);
            border: 3px solid white;
            box-shadow: 0 0 0 2px var(--border);
        }

        .timeline-content {
            background: var(--background);
            padding: 1rem;
            border-radius: 8px;
        }

        .timeline-date {
            font-size: 0.8125rem;
            color: var(--text-secondary);
            margin-bottom: 0.5rem;
        }

        .timeline-status {
            margin-bottom: 0.5rem;
        }

        .timeline-user {
            font-size: 0.8125rem;
            color: var(--text-secondary);
            margin-bottom: 0.5rem;
        }

        .timeline-remarks {
            margin-top: 0.75rem;
            padding: 0.75rem;
            background: white;
            border-radius: 6px;
            border-left: 3px solid var(--primary);
            font-size: 0.875rem;
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }

        .modal.show {
            display: flex;
        }

        .modal-content {
            background: white;
            border-radius: 12px;
            width: 90%;
            max-width: 600px;
            max-height: 90vh;
            overflow-y: auto;
        }

        .modal-header {
            padding: 1.5rem;
            border-bottom: 2px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-header h3 {
            margin: 0;
            font-size: 1.25rem;
        }

        .modal-close {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: var(--text-secondary);
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 4px;
            transition: all 0.2s;
        }

        .modal-close:hover {
            background: var(--background);
            color: var(--text-primary);
        }

        .modal-body {
            padding: 1.5rem;
        }

        .modal-footer {
            padding: 1rem 1.5rem;
            border-top: 2px solid var(--border);
            display: flex;
            gap: 0.75rem;
            justify-content: flex-end;
            align-items: end;
        }

        .modal-footer .btn {
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px !important;
            padding: 0;
            min-width: 120px;
        }

        .modal-footer .btn-secondary,
        .modal-footer .btn-primary {
            height: 48px !important;
            min-height: 48px !important;
        }



        .btn-secondary:hover {
            background: #cbd5e1;
        }

        .btn-secondary:hover {
            background: #cbd5e1;
            transform: translateY(-2px);
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, var(--primary-dark), var(--secondary));
        }

        .form-group {
            margin-bottom: 1.25rem;
        }

        .form-group label {
            display: block;
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--text-secondary);
            margin-bottom: 0.5rem;
        }

        .form-group input[type="text"],
        .form-group input[type="number"],
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 0.625rem;
            border: 1.5px solid var(--border);
            border-radius: 8px;
            font-size: 0.875rem;
            font-family: inherit;
            transition: all 0.2s;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(139, 195, 74, 0.1);
        }

        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }

        .form-group input[type="checkbox"] {
            margin-right: 0.5rem;
        }

        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            border-left: 4px solid;
        }

        .alert-info {
            background: #dbeafe;
            border-color: #3b82f6;
            color: #1e40af;
        }

        .alert-warning {
            background: #fef3c7;
            border-color: #f59e0b;
            color: #92400e;
        }

        /* Responsive */
        @media (max-width: 968px) {
            .grid-2 {
                grid-template-columns: 1fr;
            }

            .info-grid {
                grid-template-columns: 1fr;
            }

            .header-content {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }

            .status-banner {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }
        }

        @media print {
            .header-actions, .status-action, .btn {
                display: none !important;
            }
        }
    </style>
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
                <button onclick="openUpdateModal()" class="btn btn-white">
                    <i class="fas fa-edit"></i> Update Status
                </button>
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
                        <div class="info-value">₱<?php echo number_format($app['base_fee'], 2); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Processing Time</div>
                        <div class="info-value"><?php echo $app['processing_days']; ?> days</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Submitted On</div>
                        <div class="info-value"><?php echo date('M d, Y h:i A', strtotime($app['created_at'])); ?></div>
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
                            <a href="../<?php echo htmlspecialchars($app['compiled_document']); ?>" target="_blank" class="btn btn-sm btn-primary">
                                <i class="fas fa-eye"></i> View
                            </a>
                            <a href="../<?php echo htmlspecialchars($app['compiled_document']); ?>" download class="btn btn-sm btn-primary">
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
            <!-- Status History -->
            <div class="card">
                <h3><i class="fas fa-history"></i> Status History</h3>
                <?php if (empty($history)): ?>
                    <p style="color: var(--text-secondary); text-align: center; padding: 2rem;">No status updates yet</p>
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
                    <label>Admin Remarks</label>
                    <textarea name="remarks" id="adminRemarks" placeholder="Add notes or feedback for the applicant..."></textarea>
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
                
                <div class="alert alert-info">
                    <strong><i class="fas fa-info-circle"></i> Payment Request</strong>
                    <p style="margin: 0.5rem 0 0 0; font-size: 0.875rem;">
                        This will approve the application and send a payment request to the user. They will have <strong>3 days</strong> to submit payment proof.
                    </p>
                </div>
                
                <div class="form-group">
                    <label>Payment Amount (₱) *</label>
                    <input 
                        type="number" 
                        name="payment_amount" 
                        id="paymentAmount" 
                        min="0" 
                        step="0.01" 
                        value="<?php echo $app['base_fee']; ?>"
                        required
                        placeholder="0.00"
                    >
                    <small style="color: var(--text-secondary); display: block; margin-top: 0.5rem;">
                        Base Fee: ₱<?php echo number_format($app['base_fee'], 2); ?>
                    </small>
                </div>
                
                <div class="form-group">
                    <label>Payment Deadline</label>
                    <input 
                        type="text" 
                        value="3 days from approval"
                        readonly
                        style="background: var(--background); cursor: not-allowed;"
                    >
                    <small style="color: var(--text-secondary); display: block; margin-top: 0.5rem;">
                        Deadline will be automatically set to 3 days from now
                    </small>
                </div>
                
                <div class="form-group">
                    <label>Payment Instructions</label>
                    <textarea 
                        readonly
                        style="background: var(--background); cursor: not-allowed;"
                        rows="4"
                    >Pay via GCash to 09690805901
Account Name: LGU Carmona
Reference: Your Tracking Number
Upload screenshot as proof after payment</textarea>
                </div>
                
                <div class="form-group">
                    <label>Admin Remarks (Optional)</label>
                    <textarea name="remarks" id="paymentRemarks" placeholder="Additional notes for the applicant..."></textarea>
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

<script>
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
document.getElementById('updateStatusForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const newStatus = formData.get('status');
    const currentStatus = '<?php echo $app['status']; ?>';
    const remarks = formData.get('remarks').trim();
    
    // ✅ ADD THIS CHECK
    if (newStatus === currentStatus && remarks === '') {
        alert('⚠️ No changes to update. Status is already ' + currentStatus + '. Please add remarks if you want to update.');
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
document.getElementById('paymentForm').addEventListener('submit', function(e) {
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
            alert('✅ Application approved! Payment request sent to user.\n\nPayment Amount: ₱' + 
                  (data.payment_amount || formData.get('payment_amount')) + 
                  '\nDeadline: 3 days from now');
            window.location.reload();
        } else {
            alert('❌ Error: ' + data.message);
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }
    })
    .catch(error => {
        alert('❌ Error: ' + error.message);
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
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
            alert('✅ ' + data.message);
            window.location.reload();
        } else {
            alert('❌ Error: ' + data.message);
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }
    })
    .catch(error => {
        alert('❌ Error: ' + error.message);
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    });
}

// Close modals when clicking outside
window.onclick = function(event) {
    const updateModal = document.getElementById('updateModal');
    const paymentModal = document.getElementById('paymentModal');
    
    if (event.target === updateModal) {
        closeUpdateModal();
    }
    if (event.target === paymentModal) {
        closePaymentModal();
    }
}

// Close modals on ESC key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeUpdateModal();
        closePaymentModal();
    }
});
</script>

</body>
</html>

<?php include '../includes/footer.php'; ?>

