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

if(!$application) {
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
    box-shadow: 0 10px 40px rgba(0,0,0,0.15);
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

.status-pending {
    background: #fff3e0;
    color: #ef6c00;
}

.status-processing {
    background: #e3f2fd;
    color: #1976d2;
}

.status-approved {
    background: #e8f5e9;
    color: #2e7d32;
}

.status-paid {
    background: #e8f5e9;
    color: #2e7d32;
}

.status-rejected {
    background: #ffebee;
    color: #c62828;
}

.status-completed {
    background: #e1f5fe;
    color: #01579b;
}

/* Cards */
.card {
    background: white;
    border-radius: 16px;
    border: 1px solid #e2e8f0;
    margin-bottom: 1.5rem;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0,0,0,0.06);
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
    border: 1px solid rgba(255,255,255,0.3);
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

/* Responsive */
@media (max-width: 1024px) {
    .row {
        grid-template-columns: 1fr;
    }
    
    .page-header {
        flex-direction: column;
        gap: 1rem;
        text-align: center;
    }
}

@media (max-width: 768px) {
    .wrapper {
        margin: 10px;
        padding: 1.5rem 1rem;
    }
    
    .container {
        padding: 0 1rem;
    }
    
    .card-body {
        padding: 1.25rem;
    }
    
    .table td:first-child {
        width: 40%;
    }
    
    .document-item {
        flex-direction: column;
        text-align: center;
    }
    
    .doc-actions {
        width: 100%;
        flex-direction: column;
    }
    
    .btn {
        width: 100%;
        justify-content: center;
    }
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
    
    .back-btn, .copy-btn, .doc-actions, .col-lg-4 {
        display: none !important;
    }
    
    .card {
        border: 1px solid #ddd;
        box-shadow: none;
    }
}
</style>

<div class="wrapper">
    <div class="page-wrapper">
        <div class="container">
            <!-- Back Button -->
            <a href="applications.php" class="back-btn">
                &larr; Back to Applications
            </a>

            <!-- Page Header -->
            <div class="page-header">
                <div>
                    <h1>Application Details</h1>
                    <p class="tracking-info">
                        Tracking Number:
                        <span class="tracking-number"><?php echo htmlspecialchars($application['tracking_number']); ?></span>
                        <button onclick="copyToClipboard('<?php echo $application['tracking_number']; ?>')" class="copy-btn">
                            Copy
                        </button>
                    </p>
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
                            <table class="table">
                                <tbody>
                                    <tr>
                                        <td><strong>Tracking Number:</strong></td>
                                        <td>
                                            <span style="color: var(--primary); font-family: monospace; font-size: 1rem; font-weight: 700;">
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
                                        <td><strong>Status:</strong></td>
                                        <td>
                                            <span class="status-badge status-<?php echo strtolower($application['status']); ?>">
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

                            <hr>

                            <h3 style="color: var(--text-dark); margin-bottom: 1rem; font-size: 1.1rem; font-weight: 700;">Application Details</h3>
                            <table class="table">
                                <tbody>
                                    <tr>
                                        <td><strong>Purpose:</strong></td>
                                        <td><?php echo nl2br(htmlspecialchars($application['purpose'])); ?></td>
                                    </tr>
                                    <?php if($application['location']): ?>
                                    <tr>
                                        <td><strong>Location:</strong></td>
                                        <td><?php echo htmlspecialchars($application['location']); ?></td>
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

                            <?php if($application['admin_remarks']): ?>
                            <div class="alert alert-info">
                                <strong>Admin Remarks:</strong>
                                <p><?php echo nl2br(htmlspecialchars($application['admin_remarks'])); ?></p>
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
                            <?php if(count($documents) > 0): ?>
                                <?php foreach($documents as $doc): ?>      
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
                                            <a href="../<?php echo htmlspecialchars($doc['file_path']); ?>"
                                            target="_blank" class="btn btn-sm btn-primary">
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

                    <!-- Print Button -->
                    <div style="margin-top: 2rem; margin-bottom: 2rem; text-align: center;">
                        <button onclick="window.print()" style="background: linear-gradient(135deg, #7cb342 0%, #9ccc65 100%); color: white; border: none; padding: 1rem 3rem; border-radius: 10px; font-size: 1rem; font-weight: 600; cursor: pointer; box-shadow: 0 4px 15px rgba(124, 179, 66, 0.3); transition: all 0.3s ease; display: inline-flex; align-items: center; gap: 0.75rem;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 20px rgba(124, 179, 66, 0.4)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 15px rgba(124, 179, 66, 0.3)';">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="6 9 6 2 18 2 18 9"></polyline>
                                <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                                <rect x="6" y="14" width="12" height="8"></rect>
                            </svg>
                            Print Application
                        </button>
                    </div>
                </div>

                <!-- Right Sidebar -->
                <div class="col-lg-4">
                    <!-- Processing Info -->
                    <div class="card">
                        <div class="card-header">
                            <h3>Processing Information</h3>
                        </div>
                        <div class="card-body">
                            <div class="mini-card-item">
                                <span class="mini-label">Processing Fee</span>
                                <span class="mini-value highlight"><?php echo formatCurrency($application['fee']); ?></span>
                            </div>
                            <div class="mini-divider"></div>
                            <div class="mini-card-item">
                                <span class="mini-label">Estimated Time</span>
                                <span class="mini-value"><?php echo $application['processing_days']; ?> business days</span>
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
                                    <span class="status-badge status-pending" style="display: inline-block; margin-bottom: 0.5rem;">Pending</span>
                                    <p style="color: var(--text-light); margin: 0; font-size: 0.875rem; line-height: 1.5;">
                                        Waiting for initial review
                                    </p>
                                </div>
                                <div>
                                    <span class="status-badge status-processing" style="display: inline-block; margin-bottom: 0.5rem;">Processing</span>
                                    <p style="color: var(--text-light); margin: 0; font-size: 0.875rem; line-height: 1.5;">
                                        Being evaluated by staff
                                    </p>
                                </div>
                                <div>
                                    <span class="status-badge status-approved" style="display: inline-block; margin-bottom: 0.5rem;">Approved</span>
                                    <p style="color: var(--text-light); margin: 0; font-size: 0.875rem; line-height: 1.5;">
                                        Application approved
                                    </p>
                                </div>
                                <div>
                                    <span class="status-badge status-rejected" style="display: inline-block; margin-bottom: 0.5rem;">Rejected</span>
                                    <p style="color: var(--text-light); margin: 0; font-size: 0.875rem; line-height: 1.5;">
                                        Not approved - see remarks
                                    </p>
                                </div>
                                <div>
                                    <span class="status-badge status-completed" style="display: inline-block; margin-bottom: 0.5rem;">Completed</span>
                                    <p style="color: var(--text-light); margin: 0; font-size: 0.875rem; line-height: 1.5;">
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
</script>

<?php include '../includes/footer.php'; ?>