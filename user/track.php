<?php
session_start();
require_once '../config.php';
require_once '../includes/functions.php';

$application = null;
$tracking_number = isset($_GET['tracking']) ? trim($_GET['tracking']) : '';

if ($tracking_number) {
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
              WHERE a.tracking_number = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$tracking_number]);
    $application = $stmt->fetch(PDO::FETCH_ASSOC);
}

$pageTitle = 'Track Your Application';
include '../includes/header.php';
?>
<link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/user-responsive.css">
<style>
    :root {
        --primary: #7cb342;
        --primary-dark: #689f38;
        --secondary: #9ccc65;
        --text-dark: #2d3748;
        --text-light: #718096;
        --bg-light: #f8faf8;
        --warning: #ffc107;
        --danger: #dc3545;
    }

    body {
        background: linear-gradient(135deg, #7cb342 0%, #9ccc65 100%);
        min-height: 100vh;
        box-sizing: border-box;
    }

    .wrapper {
        background: linear-gradient(135deg, #f5f7fa 0%, #e8f0f7 100%);
        min-height: auto;
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

    /* Card Styles */
    .compact-card {
        background: white;
        border-radius: 20px;
        padding: 2rem;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        margin-bottom: 1.5rem;
    }

    .form-group {
        margin-bottom: 0.75rem;
    }

    .form-control {
        width: 100%;
        padding: 0.875rem 1.25rem;
        border: 2px solid #dcedc8;
        border-radius: 12px;
        font-size: 1rem;
        transition: all 0.3s ease;
        background: white;
        font-weight: 600;
        color: var(--text-dark);
    }

    .form-control:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 4px rgba(124, 179, 66, 0.1);
    }

    .form-text {
        font-size: 0.85rem;
        color: var(--text-light);
    }

    /* Buttons */
    .btn {
        padding: 0.875rem 1.5rem;
        border-radius: 25px;
        font-weight: 700;
        font-size: 1rem;
        cursor: pointer;
        transition: all 0.3s ease;
        border: none;
        text-decoration: none;
        display: inline-block;
        text-align: center;
    }

    .btn-primary {
        background: var(--primary);
        color: white;
        box-shadow: 0 4px 15px rgba(124, 179, 66, 0.3);
    }

    .btn-primary:hover {
        background: var(--primary-dark);
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(124, 179, 66, 0.4);
        color: white;
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
        color: var(--primary-dark);
    }

    .btn-block {
        width: 100%;
        display: block;
    }

    .btn-sm {
        padding: 0.5rem 1rem;
        font-size: 0.875rem;
    }

    .btn-outline {
        background: transparent;
        color: var(--primary);
        border: 2px solid #dcedc8;
    }

    .btn-outline:hover {
        background: #f1f8e9;
        color: var(--primary);
    }

    /* Alert Styles */
    .alert {
        padding: 1.5rem;
        border-radius: 15px;
        margin-bottom: 1.5rem;
        border-left: 5px solid;
    }

    .alert-success {
        background: #e8f5e9;
        border-color: #2e7d32;
        color: #1b5e20;
    }

    .alert-error {
        background: #ffebee;
        border-color: #c62828;
        color: #b71c1c;
    }

    .alert-warning {
        background: #fff3e0;
        border-color: #ef6c00;
        color: #e65100;
    }

    .alert-info {
        background: #e3f2fd;
        border-color: #1976d2;
        color: #0d47a1;
    }

    /* Timeline Styles */
    .timeline {
        position: relative;
        padding: 2rem 0;
    }

    .timeline-item {
        display: flex;
        gap: 1.5rem;
        position: relative;
        padding-bottom: 2rem;
    }

    .timeline-item:not(:last-child)::before {
        content: '';
        position: absolute;
        left: 20px;
        top: 40px;
        width: 3px;
        height: calc(100% - 20px);
        background: #dcedc8;
    }

    .timeline-item.active:not(:last-child)::before {
        background: var(--primary);
    }

    .timeline-marker {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: #f1f8e9;
        border: 3px solid #dcedc8;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        color: #dcedc8;
        flex-shrink: 0;
        position: relative;
        z-index: 1;
    }

    .timeline-item.active .timeline-marker {
        background: var(--primary);
        border-color: var(--primary);
        color: white;
        box-shadow: 0 5px 15px rgba(124, 179, 66, 0.3);
    }

    .timeline-content {
        flex: 1;
    }

    .timeline-content h4 {
        margin: 0 0 0.25rem 0;
        font-size: 1.1rem;
        color: var(--text-dark);
        font-weight: 700;
    }

    .timeline-content p {
        margin: 0 0 0.5rem 0;
        color: var(--text-light);
        font-size: 0.95rem;
    }

    .timeline-date {
        font-size: 0.85rem;
        color: var(--primary);
        font-weight: 600;
    }

    /* Table Styles */
    .table-responsive {
        overflow-x: auto;
    }

    .table {
        width: 100%;
        border-collapse: collapse;
    }

    .table tbody tr {
        border-bottom: 1px solid #f0f0f0;
    }

    .table tbody tr:last-child {
        border-bottom: none;
    }

    .table td {
        padding: 0.75rem 0;
        font-size: 0.95rem;
    }

    .table td:first-child {
        color: var(--text-light);
        width: 40%;
    }

    .mb-2 {
        margin-bottom: 0.5rem;
    }

    @media (max-width: 768px) {
        .container {
            padding: 0 1rem;
        }

        .dashboard-banner {
            padding: 2rem 1.5rem;
        }

        .dashboard-banner h1 {
            font-size: 2rem;
        }

        .compact-card {
            padding: 1.5rem;
        }
    }
</style>

<div class="wrapper">
    <div class="page-wrapper">
        <div class="container">
            <?php
            // Show flash messages
            if (isset($_SESSION['success'])) {
                echo '<div class="alert alert-success">' . htmlspecialchars($_SESSION['success']) . '</div>';
                unset($_SESSION['success']);
            }

            if (isset($_SESSION['error'])) {
                echo '<div class="alert alert-error">' . htmlspecialchars($_SESSION['error']) . '</div>';
                unset($_SESSION['error']);
            }
            ?>

            <!-- Dashboard Banner -->
            <div class="dashboard-banner">
                <h1>Track Your Application</h1>
                <p>Enter your tracking number to check the status of your application</p>
            </div>

            <!-- Search Form -->
            <div class="compact-card">
                <div
                    style="background: linear-gradient(135deg, #7cb342 0%, #9ccc65 100%); margin: -2rem -2rem 1.5rem -2rem; padding: 1.5rem 2rem; border-radius: 20px 20px 0 0;">
                    <h2 style="color: white; font-size: 1.25rem; margin: 0; font-weight: 700;">Search Application</h2>
                </div>
                <form method="GET" action="">
                    <div class="form-group" style="margin-bottom: 0.75rem;">
                        <input type="text" name="tracking" class="form-control"
                            placeholder="Enter your tracking number (e.g., CRMN-2025-158037)"
                            value="<?php echo htmlspecialchars($tracking_number); ?>" required
                            pattern="CRMN-[0-9]{4}-[0-9]{6}"
                            style="text-align: center; font-size: 1rem; padding: 0.75rem;">
                        <div class="form-text" style="text-align: center; font-size: 0.85rem; margin-top: 0.5rem;">
                            Format: CRMN-YYYY-NNNNNN (e.g., CRMN-2025-158037)
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block" style="margin-top: 1rem;">
                        Track Application
                    </button>
                </form>
            </div>

            <?php if ($tracking_number && $application): ?>
                <!-- Application Found -->

                <!-- Status Card -->
                <div class="compact-card">
                    <div
                        style="background: linear-gradient(135deg, #7cb342 0%, #9ccc65 100%); margin: -2rem -2rem 1.5rem -2rem; padding: 1.5rem 2rem; border-radius: 20px 20px 0 0;">
                        <h2 style="color: white; font-size: 1.25rem; margin: 0; font-weight: 700;">Application Status</h2>
                    </div>

                    <!-- Status Banner -->
                    <div style="display: flex; align-items: center; gap: 1rem; padding: 1rem; border-radius: 12px; margin-bottom: 1.5rem; border-left: 4px solid; <?php
                    switch (strtolower($application['status'])) {
                        case 'pending':
                            echo 'background: #fff3e0; border-color: #ef6c00;';
                            break;
                        case 'processing':
                            echo 'background: #e3f2fd; border-color: #1976d2;';
                            break;
                        case 'approved':
                            echo 'background: #e8f5e9; border-color: #2e7d32;';
                            break;
                        case 'rejected':
                            echo 'background: #ffebee; border-color: #c62828;';
                            break;
                        case 'completed':
                            echo 'background: #e8f5e9; border-color: #2e7d32;';
                            break;
                        case 'paid':
                            echo 'background: #e8f5e9; border-color: #2e7d32;';
                            break;
                        case 'cancelled':
                            echo 'background: #fff3e0; border-color: #f57c00;';
                            break;
                    }
                    ?>">
                        <div style="font-size: 2rem; font-weight: 900;">
                            <?php
                            switch (strtolower($application['status'])) {
                                case 'pending':
                                    echo '○';
                                    break;
                                case 'processing':
                                    echo '◐';
                                    break;
                                case 'approved':
                                    echo '✓';
                                    break;
                                case 'rejected':
                                    echo '✗';
                                    break;
                                case 'completed':
                                    echo '★';
                                    break;
                                case 'paid':
                                    echo '✓';
                                    break;
                                case 'cancelled':
                                    echo '⊗';
                                    break;
                                default:
                                    echo '▣';
                            }
                            ?>
                        </div>
                        <div>
                            <h3
                                style="margin: 0 0 0.25rem 0; font-size: 1.1rem; color: var(--text-dark); font-weight: 800; text-transform: uppercase;">
                                <?php echo ucfirst($application['status']); ?>
                            </h3>
                            <p style="margin: 0; color: var(--text-light); font-size: 0.9rem;">
                                <?php
                                switch (strtolower($application['status'])) {
                                    case 'pending':
                                        echo 'Your application is waiting for review';
                                        break;
                                    case 'processing':
                                        echo 'Your application is being processed';
                                        break;
                                    case 'approved':
                                        echo 'Your application has been approved';
                                        break;
                                    case 'rejected':
                                        echo 'Your application was not approved';
                                        break;
                                    case 'completed':
                                        echo 'Your service is ready for release';
                                        break;
                                    case 'paid':
                                        echo 'Payment has been received';
                                        break;
                                    case 'cancelled':
                                        echo 'Application has been cancelled';
                                        break;
                                }
                                ?>
                            </p>
                        </div>
                    </div>

                    <!-- Timeline -->
                        <div class="timeline">
                            <?php if (strtolower($application['status']) == 'cancelled'): ?>
                                <!-- Cancelled - Show only submitted -->
                                <div class="timeline-item active">
                                    <div class="timeline-marker">✓</div>
                                    <div class="timeline-content">
                                        <h4>Submitted</h4>
                                        <p>Application received</p>
                                        <span
                                            class="timeline-date"><?php echo date('M d, Y g:i A', strtotime($application['created_at'] . ' UTC')); ?></span>
                                    </div>
                                </div>

                                <div class="timeline-item active">
                                    <div class="timeline-marker">⊗</div>
                                    <div class="timeline-content">
                                        <h4>Cancelled</h4>
                                        <p>Application has been cancelled</p>
                                        <span
                                            class="timeline-date"><?php echo date('M d, Y g:i A', strtotime($application['updated_at'] . ' UTC')); ?></span>
                                    </div>
                                </div>

                            <?php else: ?>
                                <!-- Normal timeline flow -->
                                <div class="timeline-item active">
                                    <div class="timeline-marker">✓</div>
                                    <div class="timeline-content">
                                        <h4>Submitted</h4>
                                        <p>Application received</p>
                                        <span
                                            class="timeline-date"><?php echo date('M d, Y g:i A', strtotime($application['created_at'] . ' UTC')); ?></span>
                                    </div>
                                </div>

                                <div
                                    class="timeline-item <?php echo (strtolower($application['status']) != 'pending') ? 'active' : ''; ?>">
                                    <div class="timeline-marker">
                                        <?php echo (strtolower($application['status']) != 'pending') ? '✓' : ''; ?>
                                    </div>
                                    <div class="timeline-content">
                                        <h4>Under Review</h4>
                                        <p>Documents verification</p>
                                        <?php if (strtolower($application['status']) != 'pending'): ?>
                                            <span
                                                class="timeline-date"><?php echo date('M d, Y g:i A', strtotime($application['updated_at'] . ' UTC')); ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div
                                    class="timeline-item <?php echo (in_array(strtolower($application['status']), ['processing', 'approved', 'paid', 'completed'])) ? 'active' : ''; ?>">
                                    <div class="timeline-marker">
                                        <?php echo (in_array(strtolower($application['status']), ['processing', 'approved', 'paid', 'completed'])) ? '✓' : ''; ?>
                                    </div>
                                    <div class="timeline-content">
                                        <h4>Processing</h4>
                                        <p>Application in progress</p>
                                        <?php if (in_array(strtolower($application['status']), ['processing', 'approved', 'paid', 'completed'])): ?>
                                            <span
                                                class="timeline-date"><?php echo date('M d, Y g:i A', strtotime($application['updated_at'] . ' UTC')); ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div
                                    class="timeline-item <?php echo (in_array(strtolower($application['status']), ['approved', 'paid', 'completed'])) ? 'active' : ''; ?>">
                                    <div class="timeline-marker">
                                        <?php echo (in_array(strtolower($application['status']), ['approved', 'paid', 'completed'])) ? '✓' : ''; ?>
                                    </div>
                                    <div class="timeline-content">
                                        <h4><?php echo (strtolower($application['status']) == 'rejected') ? 'Rejected' : 'Approved'; ?>
                                        </h4>
                                        <p><?php echo (strtolower($application['status']) == 'rejected') ? 'Application not approved' : 'Ready for payment'; ?>
                                        </p>
                                        <?php if (in_array(strtolower($application['status']), ['approved', 'paid', 'completed', 'rejected'])): ?>
                                            <span
                                                class="timeline-date"><?php echo date('M d, Y g:i A', strtotime($application['updated_at'] . ' UTC')); ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <?php if (strtolower($application['status']) != 'rejected'): ?>
                                    <div
                                        class="timeline-item <?php echo (in_array(strtolower($application['status']), ['paid', 'completed'])) ? 'active' : ''; ?>">
                                        <div class="timeline-marker">
                                            <?php echo (in_array(strtolower($application['status']), ['paid', 'completed'])) ? '✓' : ''; ?>
                                        </div>
                                        <div class="timeline-content">
                                            <h4>Payment</h4>
                                            <p>Payment received</p>
                                            <?php if (in_array(strtolower($application['status']), ['paid', 'completed'])): ?>
                                                <span
                                                    class="timeline-date"><?php echo date('M d, Y g:i A', strtotime($application['updated_at'] . ' UTC')); ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <div
                                        class="timeline-item <?php echo (strtolower($application['status']) == 'completed') ? 'active' : ''; ?>">
                                        <div class="timeline-marker">
                                            <?php echo (strtolower($application['status']) == 'completed') ? '✓' : ''; ?>
                                        </div>
                                        <div class="timeline-content">
                                            <h4>Completed</h4>
                                            <p>Service ready for release</p>
                                            <?php if (strtolower($application['status']) == 'completed'): ?>
                                                <span
                                                    class="timeline-date"><?php echo date('M d, Y g:i A', strtotime($application['updated_at'] . ' UTC')); ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>

                        <?php if ($application['admin_remarks']): ?>
                            <div class="alert alert-info" style="margin-top: 1.5rem;">
                                <strong>Note - Admin Remarks:</strong>
                                <p style="margin: 0.5rem 0 0 0;">
                                    <?php echo nl2br(htmlspecialchars($application['admin_remarks'])); ?>
                                </p>
                            </div>
                        <?php endif; ?>
                </div>

                <!-- Application Details -->
                <?php if (isLoggedIn()): ?>
                    <div class="compact-card">
                        <div
                            style="background: linear-gradient(135deg, #7cb342 0%, #9ccc65 100%); margin: -2rem -2rem 1.5rem -2rem; padding: 1.5rem 2rem; border-radius: 20px 20px 0 0;">
                            <h2 style="color: white; font-size: 1.25rem; margin: 0; font-weight: 700;">Application Details</h2>
                        </div>
                        <div class="table-responsive">
                            <table class="table">
                                <tbody>
                                    <tr>
                                        <td><strong>Tracking Number:</strong></td>
                                        <td>
                                            <span
                                                style="font-family: monospace; color: var(--primary); font-weight: 800;"><?php echo htmlspecialchars($application['tracking_number']); ?></span>
                                            <button onclick="copyToClipboard('<?php echo $application['tracking_number']; ?>')"
                                                class="btn btn-sm btn-outline" style="margin-left: 0.5rem;">Copy</button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><strong>Service:</strong></td>
                                        <td><?php echo htmlspecialchars($application['service_name']); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Department:</strong></td>
                                        <td><?php echo htmlspecialchars($application['department_name']); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Applicant:</strong></td>
                                        <td><?php echo htmlspecialchars($application['applicant_name']); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Purpose:</strong></td>
                                        <td><?php echo htmlspecialchars($application['purpose']); ?></td>
                                    </tr>
                                    <?php if ($application['location']): ?>
                                        <tr>
                                            <td><strong>Location:</strong></td>
                                            <td><?php echo htmlspecialchars($application['location']); ?></td>
                                        </tr>
                                    <?php endif; ?>
                                    <tr>
                                        <td><strong>Date Submitted:</strong></td>
                                        <td><?php echo formatDate($application['created_at']); ?></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Processing Info -->
                <?php if (isLoggedIn()): ?>
                    <div class="compact-card">
                        <div
                            style="background: linear-gradient(135deg, #7cb342 0%, #9ccc65 100%); margin: -2rem -2rem 1.5rem -2rem; padding: 1.5rem 2rem; border-radius: 20px 20px 0 0;">
                            <h3 style="color: white; font-size: 1.1rem; margin: 0; font-weight: 700;">Processing Information
                            </h3>
                        </div>
                        <div
                            style="display: flex; justify-content: space-between; padding: 0.75rem 0; border-bottom: 1px solid #f0f0f0;">
                            <span style="color: var(--text-light);">Processing Fee:</span>
                            <strong style="color: var(--primary);"><?php echo formatCurrency($application['fee']); ?></strong>
                        </div>
                        <div style="display: flex; justify-content: space-between; padding: 0.75rem 0;">
                            <span style="color: var(--text-light);">Processing Time:</span>
                            <strong><?php echo $application['processing_days']; ?> days</strong>
                        </div>
                        <p class="form-text" style="margin-top: 0.5rem; font-size: 0.85rem;">Processing time is counted from the
                            day your application is marked as "Processing"</p>
                    </div>
                <?php endif; ?>

                <!-- Contact Info -->
                <?php if (isLoggedIn()): ?>
                    <div class="compact-card">
                        <div
                            style="background: linear-gradient(135deg, #7cb342 0%, #9ccc65 100%); margin: -2rem -2rem 1.5rem -2rem; padding: 1.5rem 2rem; border-radius: 20px 20px 0 0;">
                            <h3 style="color: white; font-size: 1.1rem; margin: 0; font-weight: 700;">Contact Information</h3>
                        </div>
                        <div style="padding: 0.75rem 0; border-bottom: 1px solid #f0f0f0;">
                            <strong style="color: var(--text-light); font-size: 0.9rem;">Email:</strong><br>
                            <span style="font-size: 0.95rem;"><?php echo htmlspecialchars($application['email']); ?></span>
                        </div>
                        <div style="padding: 0.75rem 0;">
                            <strong style="color: var(--text-light); font-size: 0.9rem;">Mobile:</strong><br>
                            <span
                                style="font-size: 0.95rem;"><?php echo htmlspecialchars($application['mobile_number']); ?></span>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if (isLoggedIn() && $_SESSION['user_id'] == $application['user_id']): ?>
                    <div class="compact-card" style="padding: 1rem;">
                        <a href="view_application.php?id=<?php echo $application['id']; ?>"
                            class="btn btn-primary btn-block mb-2">View Full Details</a>
                    </div>
                <?php endif; ?>

            <?php elseif ($tracking_number): ?>
                <!-- Not Found -->
                <div class="alert alert-warning">
                    <strong>⚠ Application Not Found</strong>
                    <p style="margin: 0.5rem 0 0 0;">No application found with tracking number:
                        <strong><?php echo htmlspecialchars($tracking_number); ?></strong>
                    </p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    function copyToClipboard(text) {
        navigator.clipboard.writeText(text).then(() => {
            alert('Tracking ID Copied!');
        }).catch(() => {
            const textarea = document.createElement('textarea');
            textarea.value = text;
            document.body.appendChild(textarea);
            textarea.select();
            document.execCommand('copy');
            document.body.removeChild(textarea);
            alert('Tracking ID Copied!');
        });
    }
</script>

<?php include '../includes/footer.php'; ?>