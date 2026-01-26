<?php
session_start();
require_once '../config.php';
require_once '../includes/functions.php';
require_once '../includes/security.php'; // NEW: Add security

// NEW: Check if user is logged in with security function
if(!isLoggedIn() || $_SESSION['role'] !== 'user') {
    log_security_event('UNAUTHORIZED_ACCESS', 'Attempt to access user dashboard');
    header('Location: ../auth/login.php');
    exit();
}

// NEW: Check session timeout (already in config.php, but extra validation)
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > SESSION_TIMEOUT) {
    session_unset();
    session_destroy();
    header('Location: ../auth/login.php?timeout=1');
    exit();
}

// Update last activity
$_SESSION['last_activity'] = time();

$user_id = $_SESSION['user_id'];
$user = getUserById($user_id);

// Get user statistics
$stats = [
   'total' => 0,
   'pending' => 0,
   'processing' => 0,
   'approved' => 0,
   'rejected' => 0
];

$query = "SELECT status, COUNT(*) as count FROM applications WHERE user_id = ? GROUP BY status COLLATE utf8mb4_unicode_ci";
$stmt = $pdo->prepare($query);
$stmt->execute([$user_id]);
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach($result as $row) {
   $status = strtolower($row['status']);
   if(isset($stats[$status])) {
       $stats[$status] = $row['count'];
       $stats['total'] += $row['count'];
   }
}

// Get recent applications using services instead of permit_types
$query = "SELECT a.*,
         COALESCE(s.service_name, 'Legacy Service') as service_name,
         COALESCE(s.description, a.purpose) as description,
         COALESCE(d.name, 'General Services') as department_name
         FROM applications a
         LEFT JOIN services s ON a.service_id = s.id
         LEFT JOIN departments d ON a.department_id = d.id
         WHERE a.user_id = ?
         ORDER BY a.created_at DESC
         LIMIT 5";
$stmt = $pdo->prepare($query);
$stmt->execute([$user_id]);
$recent_applications = $stmt->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = 'User Dashboard';
include '../includes/header.php';
?>

<style>
/* Dashboard Enhancements */
.wrapper {
    background: linear-gradient(135deg, #f5f7fa 0%, #e8f0f7 100%);
    min-height: calc(100vh - 40px);
    border-radius: 24px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    position: relative;
    padding: 4rem 2rem;
}

.page-wrapper {
    position: relative;
    z-index: 2;
    padding: 0;
}

body {
   background: linear-gradient(135deg, #7cb342 0%, #9ccc65 100%);
   min-height: 100vh;
   margin: 0;
   font-family: 'Inter', sans-serif;
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
    box-shadow: 0 20px 60px rgba(0,0,0,0.15);
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
    color: rgba(255,255,255,0.95);
    font-size: 1.1rem;
    margin: 0.5rem 0 0 0;
}

/* Quick Actions Grid */
.quick-actions {
   display: grid;
   grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
   gap: 2rem;
   margin-bottom: 3rem;
}

.action-card {
   background: white;
   padding: 2.5rem;
   border-radius: 16px;
   text-decoration: none;
   text-align: center;
   transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
   box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
   border: 2px solid transparent;
   position: relative;
   overflow: hidden;
}

.action-card::before {
   content: '';
   position: absolute;
   top: 0;
   left: 0;
   width: 100%;
   height: 4px;
   background: linear-gradient(90deg, #7fb842, #6a9c35);
   transform: scaleX(0);
   transition: transform 0.3s ease;
}

.action-card:hover::before {
   transform: scaleX(1);
}

.action-card:hover {
   transform: translateY(-8px);
   box-shadow: 0 12px 24px rgba(0, 0, 0, 0.15);
   border-color: #7fb842;
}

.action-icon {
   width: 70px;
   height: 70px;
   margin: 0 auto 1.5rem;
   background: linear-gradient(135deg, #7fb842 0%, #6a9c35 100%);
   border-radius: 16px;
   display: flex;
   align-items: center;
   justify-content: center;
   color: white;
   font-size: 2rem;
   font-weight: bold;
   box-shadow: 0 4px 12px rgba(127, 184, 66, 0.3);
}

.action-card h3 {
   color: #1e293b;
   font-size: 1.3rem;
   font-weight: 600;
   margin-bottom: 0.75rem;
}

.action-card p {
   color: #64748b;
   font-size: 0.95rem;
   line-height: 1.5;
   margin: 0;
}

/* Statistics Grid */
.stats-grid {
   display: grid;
   grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
   gap: 1.5rem;
   margin-bottom: 3rem;
}

.stat-card {
   background: white;
   padding: 2rem;
   border-radius: 16px;
   display: flex;
   align-items: center;
   gap: 1.5rem;
   box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
   transition: all 0.3s ease;
   border-left: 4px solid transparent;
}

.stat-card:hover {
   transform: translateX(4px);
   box-shadow: 0 6px 12px rgba(0, 0, 0, 0.1);
}

.stat-card:nth-child(1) {
   border-left-color: #3b82f6;
}

.stat-card:nth-child(1) .stat-icon {
   background: linear-gradient(135deg, #3b82f6, #2563eb);
}

.stat-card:nth-child(2) {
   border-left-color: #f59e0b;
}

.stat-card:nth-child(2) .stat-icon {
   background: linear-gradient(135deg, #f59e0b, #d97706);
}

.stat-card:nth-child(3) {
   border-left-color: #8b5cf6;
}

.stat-card:nth-child(3) .stat-icon {
   background: linear-gradient(135deg, #8b5cf6, #7c3aed);
}

.stat-card:nth-child(4) {
   border-left-color: #10b981;
}

.stat-card:nth-child(4) .stat-icon {
   background: linear-gradient(135deg, #10b981, #059669);
}

.stat-icon {
   width: 60px;
   height: 60px;
   border-radius: 12px;
   display: flex;
   align-items: center;
   justify-content: center;
   color: white;
   font-size: 1.5rem;
   font-weight: bold;
   flex-shrink: 0;
}

.stat-info {
   flex: 1;
}

.stat-number {
   font-size: 2rem;
   font-weight: 700;
   color: #1e293b;
   line-height: 1;
   margin-bottom: 0.5rem;
}

.stat-label {
   font-size: 0.9rem;
   color: #64748b;
   font-weight: 500;
}

/* Card Styling */
.card {
   background: white;
   border-radius: 16px;
   box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
   overflow: hidden;
   margin-bottom: 2rem;
}

.card-header {
   padding: 2rem 2.5rem;
   border-bottom: 2px solid #f1f5f9;
   display: flex;
   justify-content: space-between;
   align-items: center;
   background: linear-gradient(to right, #f8fafc, #ffffff);
}

.card-header h2 {
   font-size: 1.5rem;
   font-weight: 600;
   color: #1e293b;
   margin: 0;
}

.card-body {
   padding: 2.5rem;
}

/* Empty State */
.empty-state {
   text-align: center;
   padding: 4rem 2rem;
   position: relative;
   background: linear-gradient(135deg, #fef9c3 0%, #d9f99d 50%, #bef264 100%);
   border-radius: 24px;
   overflow: hidden;
}

.empty-icon {
   width: 150px;
   height: 150px;
   margin: 0 auto 2rem;
   background: linear-gradient(135deg, #fbbf24 0%, #84cc16 100%);
   border-radius: 30px;
   display: flex;
   align-items: center;
   justify-content: center;
   font-size: 5rem;
   color: white;
   box-shadow: 0 20px 50px rgba(251, 191, 36, 0.4);
   position: relative;
   z-index: 1;
   transform: rotate(10deg);
   transition: transform 0.3s ease;
}

.empty-icon:hover {
   transform: rotate(0deg) scale(1.05);
}

.empty-icon::before {
   content: '📋';
   font-size: 5rem;
}

.empty-state h3 {
   font-size: 2rem;
   color: #365314;
   margin-bottom: 1rem;
   font-weight: 800;
   position: relative;
   z-index: 1;
   text-shadow: 0 2px 4px rgba(255,255,255,0.5);
}

.empty-state p {
   color: #4d7c0f;
   font-size: 1.15rem;
   margin-bottom: 2.5rem;
   max-width: 550px;
   margin-left: auto;
   margin-right: auto;
   line-height: 1.7;
   position: relative;
   z-index: 1;
   font-weight: 500;
}

.empty-state .btn-primary {
   background: linear-gradient(135deg, #facc15 0%, #84cc16 100%);
   color: #365314;
   font-weight: 700;
   font-size: 1.1rem;
   padding: 1.25rem 3rem;
   border-radius: 50px;
   box-shadow: 0 10px 30px rgba(132, 204, 22, 0.4);
   border: 3px solid #fef08a;
   position: relative;
   z-index: 1;
   transition: all 0.3s ease;
}

.empty-state .btn-primary:hover {
   transform: translateY(-3px);
   box-shadow: 0 15px 40px rgba(132, 204, 22, 0.5);
   background: linear-gradient(135deg, #fde047 0%, #a3e635 100%);
}

/* Info Cards */
.info-cards {
   display: grid;
   grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
   gap: 2rem;
   margin-top: 3rem;
}

.info-card {
   background: white;
   padding: 2rem;
   border-radius: 16px;
   box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
   transition: all 0.3s ease;
   border-top: 4px solid #7fb842;
}

.info-card:hover {
   transform: translateY(-4px);
   box-shadow: 0 8px 16px rgba(0, 0, 0, 0.12);
}

.info-card h3 {
   font-size: 1.3rem;
   color: #1e293b;
   margin-bottom: 1rem;
   font-weight: 600;
}

.info-card p {
   color: #64748b;
   line-height: 1.6;
   margin-bottom: 1.5rem;
}

/* Button Styles */
.btn {
   display: inline-block;
   padding: 0.75rem 1.5rem;
   border-radius: 8px;
   text-decoration: none;
   font-weight: 500;
   transition: all 0.3s ease;
   border: none;
   cursor: pointer;
}

.btn-primary {
   background: linear-gradient(135deg, #7fb842, #6a9c35);
   color: white;
   box-shadow: 0 4px 12px rgba(127, 184, 66, 0.3);
}

.btn-primary:hover {
   transform: translateY(-2px);
   box-shadow: 0 6px 16px rgba(127, 184, 66, 0.4);
}

.btn-outline {
   background: transparent;
   color: #7fb842;
   border: 2px solid #7fb842;
}

.btn-outline:hover {
   background: #7fb842;
   color: white;
}

.btn-sm {
   padding: 0.5rem 1rem;
   font-size: 0.9rem;
}

/* Table Responsive */
.table-responsive {
   overflow-x: auto;
}

.table {
   width: 100%;
   border-collapse: collapse;
}

.table thead {
   background: #f8fafc;
}

.table th {
   padding: 1rem;
   text-align: left;
   font-weight: 600;
   color: #475569;
   font-size: 0.9rem;
   text-transform: uppercase;
   letter-spacing: 0.5px;
}

.table td {
   padding: 1.25rem 1rem;
   border-top: 1px solid #f1f5f9;
   color: #334155;
}

.table tbody tr {
   transition: background-color 0.2s ease;
}

.table tbody tr:hover {
   background: #f8fafc;
}

/* Badge Styles */
.badge {
   display: inline-block;
   padding: 0.35rem 0.75rem;
   border-radius: 6px;
   font-size: 0.85rem;
   font-weight: 600;
   text-transform: capitalize;
}

.badge-pending {
   background: #fef3c7;
   color: #92400e;
}

.badge-processing {
   background: #ddd6fe;
   color: #5b21b6;
}

.badge-approved {
   background: #d1fae5;
   color: #065f46;
}

.badge-rejected {
   background: #fee2e2;
   color: #991b1b;
}

/* Icon Definitions */
.action-icon::before {
   font-family: Arial, sans-serif;
   font-weight: bold;
   font-size: 2rem;
}

.action-card:nth-child(1) .action-icon::before {
   content: '+';
}

.action-card:nth-child(2) .action-icon::before {
   content: '◉';
}

.action-card:nth-child(3) .action-icon::before {
   content: '≡';
}

.action-card:nth-child(4) .action-icon::before {
   content: '⚙';
}

.stat-card:nth-child(1) .stat-icon::before {
   content: '∑';
   font-size: 1.8rem;
}

.stat-card:nth-child(2) .stat-icon::before {
   content: '◯';
   font-size: 1.8rem;
}

.stat-card:nth-child(3) .stat-icon::before {
   content: '⟳';
   font-size: 1.8rem;
}

.stat-card:nth-child(4) .stat-icon::before {
   content: '✓';
   font-size: 1.8rem;
}
</style>

<div class="wrapper">
    <div class="page-wrapper">
        <div class="container">

       <div class="dashboard-banner">
           <h1>Dashboard</h1>
           <p>Welcome back, <?php echo htmlspecialchars($user['name']); ?>!</p>
       </div>

       <!-- Quick Actions -->
       <div class="quick-actions">
           <a href="apply.php" class="action-card">
               <div class="action-icon"></div>
               <h3>New Application</h3>
               <p>Submit a new service request</p>
           </a>
           <a href="track.php" class="action-card">
               <div class="action-icon"></div>
               <h3>Track Application</h3>
               <p>Check your application status</p>
           </a>
           <a href="applications.php" class="action-card">
               <div class="action-icon"></div>
               <h3>My Applications</h3>
               <p>View all your applications</p>
           </a>
           <a href="profile.php" class="action-card">
               <div class="action-icon"></div>
               <h3>My Profile</h3>
               <p>Update your information</p>
           </a>
       </div>

       <!-- Statistics Cards -->
       <div class="stats-grid">
           <div class="stat-card">
               <div class="stat-icon"></div>
               <div class="stat-info">
                   <div class="stat-number"><?php echo $stats['total']; ?></div>
                   <div class="stat-label">Total Applications</div>
               </div>
           </div>
           <div class="stat-card">
               <div class="stat-icon"></div>
               <div class="stat-info">
                   <div class="stat-number"><?php echo $stats['pending']; ?></div>
                   <div class="stat-label">Pending Review</div>
               </div>
           </div>
           <div class="stat-card">
               <div class="stat-icon"></div>
               <div class="stat-info">
                   <div class="stat-number"><?php echo $stats['processing']; ?></div>
                   <div class="stat-label">Processing</div>
               </div>
           </div>
           <div class="stat-card">
               <div class="stat-icon"></div>
               <div class="stat-info">
                   <div class="stat-number"><?php echo $stats['approved']; ?></div>
                   <div class="stat-label">Approved</div>
               </div>
           </div>
       </div>

       <!-- Recent Applications -->
       <div class="card">
           <div class="card-header">
               <h2>Recent Applications</h2>
               <a href="applications.php" class="btn btn-sm btn-outline">View All</a>
           </div>
           <div class="card-body">
               <?php if(count($recent_applications) > 0): ?>
                   <div class="table-responsive">
                       <table class="table">
                           <thead>
                               <tr>
                                   <th>Tracking No.</th>
                                   <th>Service</th>
                                   <th>Department</th>
                                   <th>Purpose</th>
                                   <th>Status</th>
                                   <th>Date Applied</th>
                                   <th>Actions</th>
                               </tr>
                           </thead>
                           <tbody>
                               <?php foreach($recent_applications as $app): ?>
                                   <tr>
                                       <td>
                                           <strong><?php echo htmlspecialchars($app['tracking_number']); ?></strong>
                                       </td>
                                       <td><?php echo htmlspecialchars($app['service_name']); ?></td>
                                       <td><small><?php echo htmlspecialchars($app['department_name']); ?></small></td>
                                       <td><?php echo htmlspecialchars(substr($app['purpose'], 0, 50)) . (strlen($app['purpose']) > 50 ? '...' : ''); ?></td>
                                       <td>
                                           <span class="badge badge-<?php echo getStatusClass($app['status']); ?>">
                                               <?php echo ucfirst($app['status']); ?>
                                           </span>
                                       </td>
                                       <td><?php echo formatDate($app['created_at']); ?></td>
                                       <td>
                                           <a href="view_application.php?id=<?php echo $app['id']; ?>"
                                              class="btn btn-sm btn-primary">View</a>
                                       </td>
                                   </tr>
                               <?php endforeach; ?>
                           </tbody>
                       </table>
                   </div>
               <?php else: ?>
                   <div class="empty-state">
                       <div class="empty-icon"></div>
                       <h3>Ready to Get Started?</h3>
                       <p>No applications submitted yet. Create your first service request and track it every step of the way!</p>
                       <a href="apply.php" class="btn btn-primary">Create New Application</a>
                   </div>
               <?php endif; ?>
           </div>
       </div>

   </div>
   </div>
</div>

<?php include '../includes/footer.php'; ?>