<?php
require_once '../config.php';
require_once '../includes/functions.php';


// Check if user is logged in
if (!isLoggedIn() || $_SESSION['role'] !== 'user') {
    header('Location: ' . BASE_URL . '/auth/login.php');
    exit();
}


$user_id = $_SESSION['user_id'];


// Pagination logic
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;


// Filter by status
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';


// Build main query
$where = "WHERE a.user_id = ?";
$params = [$user_id];


if ($status_filter !== 'all') {
    $where .= " AND a.status = ?";
    $params[] = ucfirst($status_filter);
}


// Get total count
$count_query = "SELECT COUNT(*) as total FROM applications a $where";
$stmt = $pdo->prepare($count_query);
$stmt->execute($params);
$total_records = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
$total_pages = ceil($total_records / $per_page);


// Fetch stats in one optimized query
$stats_query = "SELECT
    COUNT(*) as total,
    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
    SUM(CASE WHEN status = 'processing' THEN 1 ELSE 0 END) as processing,
    SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
    SUM(CASE WHEN status = 'paid' THEN 1 ELSE 0 END) as paid,
    SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
    SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected,
    SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled,
    SUM(CASE WHEN payment_status = 'pending' AND payment_required = 1 THEN 1 ELSE 0 END) as payment_required
    FROM applications WHERE user_id = ?";
$stmt_stats = $pdo->prepare($stats_query);
$stmt_stats->execute([$user_id]);
$stats = $stmt_stats->fetch(PDO::FETCH_ASSOC);


// Get applications
$query = "SELECT a.*,
         COALESCE(s.service_name, 'Legacy Service') as service_name,
         COALESCE(s.base_fee, 0) as fee,
         COALESCE(d.name, 'General Services') as department_name
         FROM applications a
         LEFT JOIN services s ON a.service_id = s.id
         LEFT JOIN departments d ON a.department_id = d.id
         $where
         ORDER BY a.created_at DESC
         LIMIT ? OFFSET ?";
$params[] = $per_page;
$params[] = $offset;


$stmt = $pdo->prepare($query);
$stmt->execute($params);
$applications = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (isset($_GET['ajax'])) {

}
$pageTitle = 'My Applications';
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
        margin: 1;
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


    .stats-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 1.5rem;
        margin-bottom: 2rem;
    }


    .stat-card {
        background: white;
        padding: 1.5rem;
        border-radius: 15px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        border-left: 4px solid var(--primary);
        transition: all 0.3s ease;
    }


    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(124, 179, 66, 0.2);
    }


    .stat-label {
        font-size: 0.9rem;
        color: var(--text-light);
        margin-bottom: 0.5rem;
        font-weight: 600;
    }


    .stat-value {
        font-size: 2.5rem;
        font-weight: 700;
        color: var(--primary);
    }


    .filter-section {
        background: white;
        padding: 1.5rem;
        border-radius: 15px;
        margin-bottom: 2rem;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
    }


    .filter-buttons {
        display: flex;
        gap: 1rem;
        flex-wrap: wrap;
        justify-content: center;
    }


    .filter-btn {
        padding: 0.75rem 1.5rem;
        border-radius: 25px;
        border: 2px solid #e0e0e0;
        background: white;
        color: var(--text-dark);
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        text-decoration: none;
        font-size: 0.95rem;
    }


    .filter-btn:hover,
    .filter-btn.active {
        background: var(--primary);
        color: white;
        border-color: var(--primary);
        transform: translateY(-2px);
    }


    .applications-card {
        background: white;
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
    }


    .applications-header {
        padding: 1.5rem 2rem;
        background: linear-gradient(135deg, #f1f8e9 0%, #ffffff 100%);
        border-bottom: 2px solid #dcedc8;
    }


    .applications-header h2 {
        font-size: 1.5rem;
        color: var(--text-dark);
        margin: 0;
        font-weight: 700;
    }


    .applications-body {
        padding: 2rem;
    }


    .application-item {
        background: white;
        border: 2px solid #f0f0f0;
        border-radius: 15px;
        padding: 1.5rem;
        margin-bottom: 1rem;
        transition: all 0.3s ease;
    }


    .application-item:hover {
        border-color: var(--primary);
        transform: translateX(5px);
        box-shadow: 0 4px 15px rgba(124, 179, 66, 0.2);
    }


    .app-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 1rem;
        flex-wrap: wrap;
        gap: 1rem;
    }


    .app-title {
        flex: 1;
        min-width: 250px;
    }


    .app-title h3 {
        font-size: 1.2rem;
        color: var(--text-dark);
        margin: 0 0 0.5rem 0;
        font-weight: 700;
    }


    .tracking-number {
        font-family: monospace;
        color: var(--primary);
        font-size: 0.95rem;
        font-weight: 600;
    }


    .app-status {
        display: flex;
        gap: 0.5rem;
        align-items: center;
        flex-wrap: wrap;
    }


    .app-details {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 2rem;
        margin-bottom: 1.5rem;
        padding-bottom: 1.5rem;
        border-bottom: 1px solid #f0f0f0;
    }


    .detail-item {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }


    .detail-item:last-child {
        margin-left: auto;
    }


    .detail-icon {
        font-size: 1.1rem;
        color: var(--primary);
    }


    .detail-text {
        font-size: 0.9rem;
        color: var(--text-light);
    }


    .app-actions {
        display: flex;
        gap: 0.75rem;
        flex-wrap: wrap;
    }


    .badge {
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 600;
        display: inline-block;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }

    .badge-cancelled {
        background: #fed7aa;
        color: #7c2d12;
    }

    .badge-pending {
        background: #fff3e0;
        color: #ef6c00;
    }


    .badge-processing {
        background: #e3f2fd;
        color: #1976d2;
    }


    .badge-approved {
        background: #e8f5e9;
        color: #2e7d32;
    }


    .badge-rejected {
        background: #ffebee;
        color: #c62828;
    }


    .badge-completed {
        background: #e1f5fe;
        color: #01579b;
    }


    .badge-paid {
        background: #e8f5e9;
        color: #2e7d32;
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
    }


    .payment-required {
        background: #fff3cd;
        color: #856404;
        animation: pulse 2s infinite;
    }


    .payment-submitted {
        background: #cfe2ff;
        color: #084298;
    }


    .payment-verified {
        background: #d1e7dd;
        color: #0f5132;
    }


    .payment-rejected {
        background: #f8d7da;
        color: #842029;
    }


    @keyframes pulse {

        0%,
        100% {
            opacity: 1;
        }

        50% {
            opacity: 0.7;
        }
    }


    .btn-view {
        padding: 0.75rem 1.5rem;
        background: var(--primary);
        color: white;
        border-radius: 25px;
        text-decoration: none;
        font-weight: 600;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.9rem;
    }


    .btn-view:hover {
        background: var(--primary-dark);
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(124, 179, 66, 0.3);
        color: white;
    }


    .btn-pay {
        padding: 0.75rem 1.5rem;
        background: linear-gradient(135deg, #ffc107, #ff9800);
        color: white;
        border-radius: 25px;
        text-decoration: none;
        font-weight: 600;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.9rem;
    }


    .btn-pay:hover {
        transform: translateY(-2px);
        color: white;
        box-shadow: 0 4px 15px rgba(255, 193, 7, 0.4);
    }


    .empty-state {
        text-align: center;
        padding: 4rem 2rem;
    }


    .empty-icon {
        font-size: 3rem;
        margin-bottom: 1.5rem;
        color: #7cb342;
    }


    .empty-state h3 {
        color: var(--text-dark);
        font-size: 1.5rem;
        margin-bottom: 1rem;
    }


    .empty-state p {
        color: var(--text-light);
        margin-bottom: 2rem;
    }


    .pagination {
        display: flex;
        justify-content: center;
        gap: 0.5rem;
        margin-top: 2rem;
        flex-wrap: wrap;
    }


    .page-btn {
        padding: 0.75rem 1.25rem;
        border-radius: 8px;
        border: 2px solid #e0e0e0;
        background: white;
        color: var(--text-dark);
        text-decoration: none;
        font-weight: 600;
        transition: all 0.3s ease;
    }


    .page-btn:hover,
    .page-btn.active {
        background: var(--primary);
        color: white;
        border-color: var(--primary);
    }

    .filter-btn {
        /* existing styles... */
        pointer-events: auto;
        cursor: pointer;
    }

    .filter-btn:active {
        transform: none;
    }
</style>
<div class="wrapper">
    <div class="page-wrapper">
        <div class="container">


            <div class="dashboard-banner">
                <h1>My Applications</h1>
                <p>Track and manage all your service requests</p>
            </div>


            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-label">Total Applications</div>
                    <div class="stat-value"><?php echo $stats['total']; ?></div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Pending</div>
                    <div class="stat-value"><?php echo $stats['pending']; ?></div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Processing</div>
                    <div class="stat-value"><?php echo $stats['processing']; ?></div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Approved</div>
                    <div class="stat-value"><?php echo $stats['approved']; ?></div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Paid</div>
                    <div class="stat-value"><?php echo $stats['paid']; ?></div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Completed</div>
                    <div class="stat-value"><?php echo $stats['completed']; ?></div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Rejected</div>
                    <div class="stat-value"><?php echo $stats['rejected']; ?></div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Cancelled</div>
                    <div class="stat-value"><?php echo $stats['cancelled']; ?></div>
                </div>
            </div>


            <div class="filter-section">
                <div class="filter-buttons">
                    <a href="?status=all" class="filter-btn <?php echo $status_filter === 'all' ? 'active' : ''; ?>">
                        All Applications
                    </a>
                    <a href="?status=pending"
                        class="filter-btn <?php echo $status_filter === 'pending' ? 'active' : ''; ?>">
                        Pending
                    </a>
                    <a href="?status=processing"
                        class="filter-btn <?php echo $status_filter === 'processing' ? 'active' : ''; ?>">
                        Processing
                    </a>
                    <a href="?status=approved"
                        class="filter-btn <?php echo $status_filter === 'approved' ? 'active' : ''; ?>">
                        Approved
                    </a>
                    <a href="?status=paid" class="filter-btn <?php echo $status_filter === 'paid' ? 'active' : ''; ?>">
                        Paid
                    </a>
                    <a href="?status=completed"
                        class="filter-btn <?php echo $status_filter === 'completed' ? 'active' : ''; ?>">
                        Completed
                    </a>
                    <a href="?status=rejected"
                        class="filter-btn <?php echo $status_filter === 'rejected' ? 'active' : ''; ?>">
                        Rejected
                    </a>
                    <a href="?status=cancelled"
                        class="filter-btn <?php echo $status_filter === 'cancelled' ? 'active' : ''; ?>">
                        Cancelled
                    </a>
                </div>
            </div>


            <div class="applications-card">
                <div class="applications-header">
                    <h2>Applications List (<?php echo $total_records; ?>)</h2>
                </div>
                <div class="applications-body">
                    <?php if (count($applications) > 0): ?>
                        <?php foreach ($applications as $app): ?>
                            <div class="application-item">
                                <div class="app-header">
                                    <div class="app-title">
                                        <h3><?php echo htmlspecialchars($app['service_name']); ?></h3>
                                        <div class="tracking-number">
                                            # <?php echo htmlspecialchars($app['tracking_number']); ?>
                                        </div>
                                    </div>
                                    <div class="app-status">
                                        <span class="badge badge-<?php echo strtolower($app['status']); ?>">
                                            <?php echo ucfirst($app['status']); ?>
                                        </span>

                                        <?php if ($app['payment_required']): ?>
                                            <?php if ($app['payment_status'] === 'pending'): ?>
                                                <span class="payment-badge payment-required">
                                                    PAYMENT REQUIRED
                                                </span>
                                            <?php elseif ($app['payment_status'] === 'submitted'): ?>
                                                <span class="payment-badge payment-submitted">
                                                    UNDER VERIFICATION
                                                </span>
                                            <?php elseif ($app['payment_status'] === 'verified'): ?>
                                                <span class="payment-badge payment-verified">
                                                    PAYMENT VERIFIED
                                                </span>
                                            <?php elseif ($app['payment_status'] === 'rejected'): ?>
                                                <span class="payment-badge payment-rejected">
                                                    PAYMENT REJECTED
                                                </span>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </div>
                                </div>


                                <div class="app-details">
                                    <div class="detail-item">
                                        <span class="detail-icon">▪</span>
                                        <span
                                            class="detail-text"><?php echo htmlspecialchars($app['department_name']); ?></span>
                                    </div>
                                    <div class="detail-item">
                                        <span class="detail-icon">•</span>
                                        <span
                                            class="detail-text"><?php echo date('M d, Y', strtotime($app['created_at'])); ?></span>
                                    </div>
                                    <?php if ($app['payment_required']): ?>
                                        <div class="detail-item">
                                            <span class="detail-icon">₱</span>
                                            <span class="detail-text"><?php echo number_format($app['payment_amount'], 2); ?></span>
                                        </div>
                                    <?php endif; ?>
                                </div>


                                <div class="app-actions">
                                    <a href="view_application.php?id=<?php echo $app['id']; ?>" class="btn-view">
                                        View Details
                                    </a>

                                    <?php if ($app['payment_required'] && ($app['payment_status'] === 'pending' || $app['payment_status'] === 'rejected')): ?>
                                        <a href="submit_payment.php?id=<?php echo $app['id']; ?>" class="btn-pay">
                                            <?php echo $app['payment_status'] === 'rejected' ? 'Resubmit Payment' : 'Pay Now'; ?>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>


                        <?php if ($total_pages > 1): ?>
                            <div class="pagination">
                                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                    <a href="?page=<?php echo $i; ?>&status=<?php echo $status_filter; ?>"
                                        class="page-btn <?php echo $i == $page ? 'active' : ''; ?>">
                                        <?php echo $i; ?>
                                    </a>
                                <?php endfor; ?>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="empty-state">
                            <div class="empty-icon"><i class="fas fa-folder-open"></i></div>
                            <h3>No Applications Found</h3>
                            <p>
                                <?php
                                if ($status_filter && $status_filter !== 'all') {
                                    echo "No applications with '" . ucfirst($status_filter) . "' status yet.";
                                } else {
                                    echo "You haven't submitted any applications yet.";
                                }
                                ?>
                            </p>
                            <?php if (!$status_filter || $status_filter === 'all'): ?>
                                <a href="apply.php" class="btn-view" style="display: inline-flex;">
                                    + Create New Application
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const filterButtons = document.querySelectorAll('.filter-btn');

        // Apply filter with AJAX (no page reload)
        filterButtons.forEach(button => {
            button.addEventListener('click', function (e) {
                e.preventDefault();

                const url = new URL(this.href);
                const status = url.searchParams.get('status') || 'all';
                const page = url.searchParams.get('page') || 1;

                // Fetch new data
                fetch(`?status=${status}&page=${page}`)
                    .then(response => response.text())
                    .then(html => {
                        const parser = new DOMParser();
                        const doc = parser.parseFromString(html, 'text/html');

                        // Update stats
                        const statsCards = document.querySelectorAll('.stat-value');
                        const newStatsCards = doc.querySelectorAll('.stat-value');
                        statsCards.forEach((card, i) => {
                            if (newStatsCards[i]) {
                                card.textContent = newStatsCards[i].textContent;
                            }
                        });

                        // Update applications count in header
                        const newHeader = doc.querySelector('.applications-header h2');
                        if (newHeader) {
                            document.querySelector('.applications-header h2').textContent = newHeader.textContent;
                        }

                        // Update applications body
                        const newBody = doc.querySelector('.applications-body');
                        if (newBody) {
                            document.querySelector('.applications-body').innerHTML = newBody.innerHTML;
                        }

                        // Update active button state
                        filterButtons.forEach(btn => {
                            btn.classList.remove('active');
                        });
                        this.classList.add('active');

                        // Reattach pagination listeners
                        attachPaginationListeners();
                    })
                    .catch(error => console.error('Error:', error));
            });
        });

        // Pagination handler
        function attachPaginationListeners() {
            const paginationButtons = document.querySelectorAll('.page-btn');
            paginationButtons.forEach(button => {
                button.addEventListener('click', function (e) {
                    e.preventDefault();

                    const url = new URL(this.href);
                    const status = url.searchParams.get('status') || 'all';
                    const page = url.searchParams.get('page') || 1;

                    fetch(`?status=${status}&page=${page}`)
                        .then(response => response.text())
                        .then(html => {
                            const parser = new DOMParser();
                            const doc = parser.parseFromString(html, 'text/html');

                            const newBody = doc.querySelector('.applications-body');
                            if (newBody) {
                                document.querySelector('.applications-body').innerHTML = newBody.innerHTML;
                            }

                            attachPaginationListeners();
                        })
                        .catch(error => console.error('Error:', error));
                });
            });
        }

        // Initial pagination listeners
        attachPaginationListeners();
    });
</script>
<?php include '../includes/footer.php'; ?>
