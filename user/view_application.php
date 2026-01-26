<?php
session_start();
require_once '../config.php';
require_once '../includes/functions.php';

// Check if user is logged in
if(!isLoggedIn() || $_SESSION['role'] !== 'user') {
    header('Location: ../auth/login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$application_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$application_id) {
    $_SESSION['error'] = 'Invalid application ID';
    header('Location: applications.php');
    exit();
}

// FIXED: Get application details using services instead of permit_types
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

$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $application_id, $user_id);
$stmt->execute();
$application = $stmt->get_result()->fetch_assoc();

if(!$application) {
    $_SESSION['error'] = 'Application not found or you do not have permission to view it';
    header('Location: applications.php');
    exit();
}

// Get documents
$doc_query = "SELECT * FROM documents WHERE application_id = ? ORDER BY uploaded_at DESC";
$doc_stmt = $conn->prepare($doc_query);
$doc_stmt->bind_param("i", $application_id);
$doc_stmt->execute();
$documents = $doc_stmt->get_result();

$pageTitle = 'Application Details';
include '../includes/header.php';
?>

<div class="container" style="padding: 2rem;">
    <!-- Back Button -->
    <div style="margin-bottom: 1.5rem;">
        <a href="applications.php" class="btn btn-outline">
            <span class="icon">←</span> Back to Applications
        </a>
    </div>

    <!-- Header with Status -->
    <div class="page-header" style="display: flex; justify-content: space-between; align-items: center;">
        <div>
            <h1>Application Details</h1>
            <p class="text-muted">Tracking Number: <strong style="color: var(--primary); font-family: monospace;"><?php echo htmlspecialchars($application['tracking_number']); ?></strong></p>
        </div>
        <div>
            <span class="status-badge status-<?php echo strtolower($application['status']); ?>">
                <?php echo strtoupper($application['status']); ?>
            </span>
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
                    <div class="table-responsive">
                        <table class="table">
                            <tbody>
                                <tr>
                                    <td style="width: 30%;"><strong>Tracking Number:</strong></td>
                                    <td>
                                        <span style="font-family: monospace; color: var(--primary); font-size: 1.1rem;"><?php echo htmlspecialchars($application['tracking_number']); ?></span>
                                        <button onclick="copyToClipboard('<?php echo $application['tracking_number']; ?>')" 
                                                class="btn btn-sm btn-outline" style="margin-left: 0.5rem;">📋 Copy</button>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Service:</strong></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($application['service_name']); ?></strong>
                                        <p class="text-muted" style="margin: 0.25rem 0 0 0; font-size: 0.9rem;"><?php echo htmlspecialchars($application['description']); ?></p>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Department:</strong></td>
                                    <td><?php echo htmlspecialchars($application['department_name']); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Status:</strong></td>
                                    <td>
                                        <span class="badge badge-<?php echo getStatusClass($application['status']); ?>">
                                            <?php echo ucfirst($application['status']); ?>
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Date Submitted:</strong></td>
                                    <td><?php echo formatDate($application['created_at']); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Last Updated:</strong></td>
                                    <td><?php echo formatDate($application['updated_at']); ?></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <hr style="margin: 2rem 0; border: none; border-top: 1px solid var(--border-color);">

                    <h4 style="margin-bottom: 1rem;">Application Details</h4>
                    <div class="table-responsive">
                        <table class="table">
                            <tbody>
                                <tr>
                                    <td style="width: 30%;"><strong>Purpose:</strong></td>
                                    <td><?php echo nl2br(htmlspecialchars($application['purpose'])); ?></td>
                                </tr>
                                <?php if($application['location']): ?>
                                <tr>
                                    <td><strong>Location:</strong></td>
                                    <td><?php echo htmlspecialchars($application['location']); ?></td>
                                </tr>
                                <?php endif; ?>
                                <?php if($application['project_cost'] > 0): ?>
                                <tr>
                                    <td><strong>Project Cost:</strong></td>
                                    <td><?php echo formatCurrency($application['project_cost']); ?></td>
                                </tr>
                                <?php endif; ?>
                                <?php if($application['remarks']): ?>
                                <tr>
                                    <td><strong>Your Remarks:</strong></td>
                                    <td><?php echo nl2br(htmlspecialchars($application['remarks'])); ?></td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <?php if($application['admin_remarks']): ?>
                    <div class="alert alert-info" style="margin-top: 2rem;">
                        <strong>📝 Admin Remarks:</strong>
                        <p style="margin: 0.5rem 0 0 0;"><?php echo nl2br(htmlspecialchars($application['admin_remarks'])); ?></p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Uploaded Documents -->
            <div class="card">
                <div class="card-header">
                    <h2>Uploaded Documents</h2>
                </div>
                <div class="card-body">
                    <?php if($documents->num_rows > 0): ?>
                        <div style="display: grid; gap: 1rem;">
                            <?php while($doc = $documents->fetch_assoc()): ?>
                                <div style="display: flex; align-items: center; gap: 1rem; padding: 1rem; background: rgba(15, 23, 42, 0.5); border-radius: 8px; border: 1px solid var(--border-color);">
                                    <div style="font-size: 2rem;">
                                        <?php 
                                        $ext = strtolower(pathinfo($doc['filename'], PATHINFO_EXTENSION));
                                        echo ($ext === 'pdf') ? '📄' : '🖼️';
                                        ?>
                                    </div>
                                    <div style="flex: 1;">
                                        <h5 style="margin: 0 0 0.25rem 0; color: var(--text-primary);"><?php echo htmlspecialchars($doc['filename']); ?></h5>
                                        <p class="text-muted" style="margin: 0; font-size: 0.9rem;">
                                            Type: <?php echo strtoupper($ext); ?> | 
                                            Size: <?php echo number_format($doc['file_size'] / 1024, 2); ?> KB | 
                                            Uploaded: <?php echo formatDate($doc['uploaded_at']); ?>
                                        </p>
                                    </div>
                                    <div style="display: flex; gap: 0.5rem;">
                                        <a href="../<?php echo htmlspecialchars($doc['file_path']); ?>" 
                                           target="_blank" class="btn btn-sm btn-primary">
                                            👁️ View
                                        </a>
                                        <a href="../<?php echo htmlspecialchars($doc['file_path']); ?>" 
                                           download="<?php echo htmlspecialchars($doc['filename']); ?>" 
                                           class="btn btn-sm btn-outline">
                                            ⬇️ Download
                                        </a>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">No documents uploaded</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Applicant Information -->
            <div class="card">
                <div class="card-header">
                    <h2>Applicant Information</h2>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <tbody>
                                <tr>
                                    <td style="width: 30%;"><strong>Full Name:</strong></td>
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
            </div>
        </div>

        <!-- Right Sidebar -->
        <div class="col-lg-4">
            <!-- Quick Actions -->
            <div class="card">
                <div class="card-header">
                    <h3>Quick Actions</h3>
                </div>
                <div class="card-body">
                    <a href="track.php?tracking=<?php echo $application['tracking_number']; ?>" 
                       class="btn btn-primary btn-block mb-2">
                        🔍 Track Status
                    </a>
                    <a href="applications.php" class="btn btn-secondary btn-block mb-2">
                        📋 All Applications
                    </a>
                    <a href="dashboard.php" class="btn btn-outline btn-block mb-2">
                        🏠 Dashboard
                    </a>
                    <button onclick="window.print()" class="btn btn-outline btn-block">
                        🖨️ Print
                    </button>
                </div>
            </div>

            <!-- Processing Info -->
            <div class="card">
                <div class="card-header">
                    <h3>Processing Information</h3>
                </div>
                <div class="card-body">
                    <div style="display: flex; justify-content: space-between; padding: 0.75rem 0; border-bottom: 1px solid var(--border-color);">
                        <span class="text-muted">Processing Fee:</span>
                        <strong style="color: var(--primary); font-size: 1.1rem;"><?php echo formatCurrency($application['fee']); ?></strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; padding: 0.75rem 0;">
                        <span class="text-muted">Processing Time:</span>
                        <strong><?php echo $application['processing_days']; ?> business days</strong>
                    </div>
                    <p class="form-text" style="margin-top: 1rem;">
                        Processing time starts when your application status changes to "Processing"
                    </p>
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
                            <span class="badge badge-warning">Pending</span>
                            <p class="text-muted" style="margin: 0.25rem 0 0 0; font-size: 0.9rem;">Waiting for initial review</p>
                        </div>
                        <div>
                            <span class="badge badge-info">Processing</span>
                            <p class="text-muted" style="margin: 0.25rem 0 0 0; font-size: 0.9rem;">Being evaluated by staff</p>
                        </div>
                        <div>
                            <span class="badge badge-success">Approved</span>
                            <p class="text-muted" style="margin: 0.25rem 0 0 0; font-size: 0.9rem;">Application approved</p>
                        </div>
                        <div>
                            <span class="badge badge-danger">Rejected</span>
                            <p class="text-muted" style="margin: 0.25rem 0 0 0; font-size: 0.9rem;">Not approved - see remarks</p>
                        </div>
                        <div>
                            <span class="badge badge-primary">Completed</span>
                            <p class="text-muted" style="margin: 0.25rem 0 0 0; font-size: 0.9rem;">Ready for pickup</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Support -->
            <div class="card">
                <div class="card-header">
                    <h3>Need Help?</h3>
                </div>
                <div class="card-body">
                    <p><strong>📞 Call:</strong> (02) 8123-4567</p>
                    <p><strong>📧 Email:</strong> permits@lgu.gov.ph</p>
                    <p style="margin-bottom: 0;"><strong>🕐 Hours:</strong> Mon-Fri, 8AM-5PM</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        alert('✅ Tracking number copied to clipboard!');
    }).catch(() => {
        // Fallback for older browsers
        const textarea = document.createElement('textarea');
        textarea.value = text;
        textarea.style.position = 'fixed';
        textarea.style.opacity = '0';
        document.body.appendChild(textarea);
        textarea.select();
        document.execCommand('copy');
        document.body.removeChild(textarea);
        alert('✅ Tracking number copied to clipboard!');
    });
}
</script>

<style>
@media print {
    .navbar, .footer, .btn, .card-header h3, .col-lg-4 { 
        display: none; 
    }
    .card { 
        border: 1px solid #ddd; 
        box-shadow: none; 
    }
    .col-lg-8 {
        flex: 0 0 100%;
        max-width: 100%;
    }
    body {
        background: white !important;
    }
}
</style>

<?php include '../includes/footer.php'; ?>