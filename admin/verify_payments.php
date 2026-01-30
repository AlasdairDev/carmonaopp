<?php
require_once __DIR__ . '/../config.php';
require_once '../includes/functions.php';
require_once '../includes/security.php';  

if (!isLoggedIn() || !isAdmin()) {
    header('Location: ../login.php');
    exit();
}

// Get department filter
$dept_filter_data = getDepartmentFilter('a');
$dept_where = $dept_filter_data['where'] ? ' AND ' . $dept_filter_data['where'] : '';
$dept_params = $dept_filter_data['params'];

// Stats query with department filter
$stats_query = "SELECT 
    COUNT(*) as total_payments,
    SUM(CASE WHEN payment_status = 'submitted' THEN 1 ELSE 0 END) as pending_verification,
    SUM(CASE WHEN payment_status = 'verified' THEN 1 ELSE 0 END) as verified,
    SUM(CASE WHEN payment_status = 'rejected' THEN 1 ELSE 0 END) as rejected,
    SUM(CASE WHEN payment_status = 'submitted' THEN payment_amount ELSE 0 END) as pending_amount
    FROM applications a
    WHERE payment_required = 1 $dept_where";

if (!empty($dept_params)) {
    $stats_stmt = $pdo->prepare($stats_query);
    $stats_stmt->execute($dept_params);
    $stats = $stats_stmt->fetch();
} else {
    $stats = $pdo->query($stats_query)->fetch();
}

// Applications query with department filter
$query = "
    SELECT a.*, 
           u.name as applicant_name,
           u.email,
           u.mobile,
           s.service_name,
           d.name as department_name
    FROM applications a
    JOIN users u ON a.user_id = u.id
    LEFT JOIN services s ON a.service_id = s.id
    LEFT JOIN departments d ON a.department_id = d.id
    WHERE a.payment_status = 'submitted' $dept_where
    ORDER BY a.payment_submitted_at DESC
";

if (!empty($dept_params)) {
    $stmt = $pdo->prepare($query);
    $stmt->execute($dept_params);
    $pending_payments = $stmt->fetchAll();
} else {
    $pending_payments = $pdo->query($query)->fetchAll();
}

$pageTitle = 'Verify Payments';
include '../includes/header.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">


    <link rel="stylesheet" href="../assets/css/admin-responsive.css">
    <link rel="stylesheet" href="../assets/css/admin/verify_payments_syles.css">
    <style>
        /* ============================================
   STANDARD ADMIN PAGE LAYOUT - 
   ============================================ */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html {
            margin: 0;
            padding: 0;
        }

        body {
            margin: 0 !important;
            padding: 0 !important;
            min-height: 100vh;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: var(--background);
            color: var(--text-primary);
            line-height: 1.6;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 1.5rem 1.5rem 1.5rem !important;
        }


        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary: #8bc34a;
            --primary-dark: #689f38;
            --primary-light: #dcedc8;
            --secondary: #558b2f;
            --background: #f5f7fa;
            --surface: #ffffff;
            --text-primary: #2c3e50;
            --text-secondary: #64748b;
            --border: #e2e8f0;
            --shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            --shadow-md: 0 4px 6px rgba(0, 0, 0, 0.1);
            --shadow-lg: 0 10px 15px rgba(0, 0, 0, 0.1);
            --radius: 12px;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: var(--background);
            color: var(--text-primary);
            line-height: 1.6;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 1.5rem 1.5rem 1.5rem;
        }

        .page-header {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            border-radius: var(--radius);
            padding: 2rem;
            color: white;
            margin-bottom: 1.5rem;
            box-shadow: var(--shadow-lg);
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
            background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
            border-radius: 50%;
            transform: translate(30%, -30%);
        }

        .page-header h1 {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            font-size: 2rem;
            font-weight: 800;
            margin-bottom: 0.25rem;
            position: relative;
            z-index: 1;
        }

        .page-header p {
            font-size: 1rem;
            opacity: 0.95;
            position: relative;
            z-index: 1;
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .stat-card {
            background: var(--surface);
            border-radius: var(--radius);
            padding: 1.75rem;
            box-shadow: var(--shadow);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            cursor: pointer;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: var(--primary);
            transition: width 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-lg);
        }

        .stat-card:hover::before {
            width: 8px;
        }

        .stat-card h3 {
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.75rem;
        }

        .stat-value {
            font-size: 2.5rem;
            font-weight: 800;
            color: var(--text-primary);
            line-height: 1;
        }

        /* Payment List */


        .payment-item {
            border-bottom: 2px solid var(--border);
            padding: 1.75rem;
            transition: all 0.3s ease;
        }

        .payment-item:hover {
            background: var(--background);
        }

        .payment-item:last-child {
            border-bottom: none;
        }

        .payment-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1rem;
            gap: 1rem;
        }

        .payment-info h3 {
            font-size: 1.25rem;
            margin-bottom: 0.5rem;
            color: var(--text-primary);
            font-weight: 700;
        }

        .payment-meta {
            display: flex;
            gap: 1rem;
            font-size: 0.875rem;
            color: var(--text-secondary);
            margin-bottom: 1rem;
            flex-wrap: wrap;
        }

        .payment-meta span {
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }

        .payment-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            padding: 1rem;
            background: var(--background);
            border-radius: var(--radius);
            margin-bottom: 1rem;
        }

        .detail-item {
            display: flex;
            flex-direction: column;
        }

        .detail-label {
            font-size: 0.75rem;
            color: var(--text-secondary);
            font-weight: 600;
            margin-bottom: 0.25rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .detail-value {
            font-size: 1rem;
            color: var(--text-primary);
            font-weight: 600;
        }

        .payment-notes {
            padding: 1rem;
            background: #fffbeb;
            border-radius: var(--radius);
            margin-bottom: 1rem;
            border-left: 4px solid #fbbf24;
        }

        .payment-notes strong {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--text-primary);
        }

        .payment-proof {
            display: grid;
            grid-template-columns: 200px 1fr;
            gap: 1.5rem;
            align-items: flex-start;
            margin-bottom: 1rem;
        }

        .proof-preview {
            width: 200px;
            height: 200px;
            border-radius: var(--radius);
            overflow: hidden;
            border: 2px solid var(--border);
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .proof-preview:hover {
            transform: scale(1.05);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
        }

        .proof-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .proof-actions p {
            margin-bottom: 0.75rem;
            font-size: 0.875rem;
            color: var(--text-secondary);
        }

        .proof-actions p strong {
            display: block;
            margin-bottom: 0.25rem;
            color: var(--text-primary);
            font-size: 1rem;
        }

        .proof-buttons {
            display: flex;
            gap: 0.75rem;
            margin-bottom: 1rem;
        }

        .action-buttons {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0.75rem;
        }

        .action-buttons .btn {
            width: 100%;
            margin: 0;
            padding: 0.75rem 1.5rem;
            text-align: center;
            box-sizing: border-box;
        }

        /* Buttons */
        .btn {
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-weight: 600;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            text-decoration: none;
            font-size: 0.875rem;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(139, 195, 74, 0.3);
        }

        .btn-success {
            background: linear-gradient(135deg, #4caf50, #388e3c);
            color: white;
        }

        .btn-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(76, 175, 80, 0.3);
        }

        .btn-danger {
            background: linear-gradient(135deg, #f44336, #d32f2f);
            color: white;
        }

        .btn-danger:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(244, 67, 54, 0.3);
        }

        .btn-info {
            background: linear-gradient(135deg, #2196F3, #1976d2);
            color: white;
        }

        .btn-info:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(33, 150, 243, 0.3);
        }

        .btn-secondary {
            background: #e2e8f0;
            color: var(--text-primary);
            border-radius: 8px;
            /* Add or update this */
        }

        .btn-secondary:hover {
            background: #cbd5e1;
        }

        .modal-actions .btn-secondary,
        .modal-actions .btn-danger,
        .modal-actions .btn-primary {
            border-radius: 8px !important;
            /* Force same radius for all modal buttons */
        }

        /* Badge */
        .badge {
            display: inline-flex;
            align-items: center;
            padding: 0.375rem 0.875rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .badge-warning {
            background: #fef3c7;
            color: #92400e;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            color: var(--text-secondary);
        }

        .empty-state i {
            font-size: 4rem;
            margin-bottom: 1rem;
            opacity: 0.3;
        }

        .empty-state h3 {
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
            color: var(--text-primary);
        }

        /* Modal */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(4px);
            z-index: 1000;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }

        .modal.show {
            display: flex;
        }

        .modal-content {
            background: white;
            border-radius: var(--radius);
            padding: 2rem;
            max-width: 600px;
            width: 100%;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid var(--border);
        }

        .modal-header h3 {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--text-primary);
            margin: 0;
        }

        .close-modal {
            background: none;
            border: none;
            font-size: 2rem;
            cursor: pointer;
            color: #999;
            padding: 0;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: all 0.3s ease;
        }

        .close-modal:hover {
            background: #f0f0f0;
            color: #333;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            font-weight: 700;
            margin-bottom: 0.5rem;
            color: var(--text-primary);
            font-size: 0.875rem;
        }

        .form-control {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid var(--border);
            border-radius: 8px;
            font-size: 0.875rem;
            transition: all 0.3s ease;
            background: var(--background);
            font-family: inherit;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(139, 195, 74, 0.1);
            background: white;
        }

        textarea.form-control {
            resize: vertical;
            min-height: 100px;
        }

        .modal-actions {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0.75rem;
            margin-top: 1.5rem;
            align-items: center;
    
        }

        .modal-actions .btn {
            width: 100%;
            margin: 0 !important;
            padding: 0.75rem 1.5rem;
            text-align: center;
            box-sizing: border-box;
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            /* Match the border radius */
            align-self: center;
            /* Ensure vertical alignment */
        }

        /* Image Modal */
        .image-modal {
            cursor: zoom-out;
        }

        .image-modal .modal-content {
            max-width: 90%;
            max-height: 90vh;
            padding: 0;
            background: transparent;
            box-shadow: none;
        }

        .image-modal img {
            width: 100%;
            height: auto;
            border-radius: var(--radius);
        }

        /* Compact Payment Card */
        .payment-card-compact {
            background: var(--surface);
            border-radius: 10px;
            box-shadow: var(--shadow);
            margin-bottom: 1rem;
            transition: all 0.2s ease;
            border: 1px solid var(--border);
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        .payment-card-compact:hover {
            box-shadow: var(--shadow-md);
            border-color: var(--primary);
        }

        .payment-row {
            display: grid;
            grid-template-columns: 2fr 3fr auto;
            gap: 2rem;
            padding: 1.25rem 1.5rem;
            align-items: center;
            background: var(--surface);
        }

        /* Applicant Section */
        .applicant-section {
            display: flex;
            align-items: center;
            gap: 0.875rem;
        }

        .applicant-avatar-sm {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.25rem;
            font-weight: 700;
            flex-shrink: 0;
            box-shadow: 0 2px 8px rgba(139, 195, 74, 0.3);
        }

        .applicant-details-sm h4 {
            font-size: 1rem;
            font-weight: 700;
            color: var(--text-primary);
            margin: 0 0 0.375rem 0;
        }

        .meta-row {
            display: flex;
            gap: 1.25rem;
            font-size: 0.8125rem;
            color: var(--text-secondary);
        }

        .meta-row span {
            display: flex;
            align-items: center;
            gap: 0.375rem;
        }

        .meta-row i {
            font-size: 0.75rem;
        }

        /* Payment Info Grid */
        .payment-info-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1rem;
        }

        .info-item-compact {
            display: flex;
            flex-direction: column;
            gap: 0.375rem;
            padding: 0.875rem 1rem;
            background: var(--background);
            border-radius: 8px;
            border: 1px solid var(--border);
            border-left: 3px solid var(--primary);
            transition: all 0.2s ease;
        }

        .info-item-compact:hover {
            border-left-width: 4px;
            box-shadow: 0 2px 8px rgba(139, 195, 74, 0.15);
        }

        .info-label-compact {
            font-size: 0.6875rem;
            font-weight: 700;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .info-value-compact {
            font-size: 0.9375rem;
            font-weight: 600;
            color: var(--text-primary);
            word-break: break-word;
        }

        /* Proof & Actions Section */
        .proof-actions-section {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .proof-thumb {
            width: 100px;
            height: 100px;
            border-radius: 10px;
            overflow: hidden;
            border: 2px solid var(--border);
            cursor: pointer;
            position: relative;
            flex-shrink: 0;
            transition: all 0.3s ease;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .proof-thumb:hover {
            border-color: var(--primary);
            transform: scale(1.05);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .proof-thumb img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .thumb-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.6);
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .proof-thumb:hover .thumb-overlay {
            opacity: 1;
        }

        .thumb-overlay i {
            color: white;
            font-size: 1.5rem;
        }

        /* Action Buttons */
        .action-btns-compact {
            display: flex;
            flex-direction: column;
            gap: 0.625rem;
        }

        .btn-compact {
            width: 44px;
            height: 44px;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
            font-size: 1rem;
        }

        .btn-verify {
            background: linear-gradient(135deg, #4caf50, #388e3c);
            color: white;
            box-shadow: 0 2px 8px rgba(76, 175, 80, 0.3);
        }

        .btn-verify:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(76, 175, 80, 0.4);
        }

        .btn-reject {
            background: linear-gradient(135deg, #f44336, #d32f2f);
            color: white;
            box-shadow: 0 2px 8px rgba(244, 67, 54, 0.3);
        }

        .btn-reject:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(244, 67, 54, 0.4);
        }

        /* Notes Row */
        .notes-row {
            padding: 0.875rem 1.5rem;
            background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%);
            border-top: 1px solid #fbbf24;
            font-size: 0.875rem;
            color: #92400e;
            display: flex;
            align-items: flex-start;
            gap: 0.625rem;
        }

        .notes-row i {
            color: #f59e0b;
            margin-top: 0.125rem;
            flex-shrink: 0;
        }

        /* Custom Notification Modal */
        .notify-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(4px);
            z-index: 10000;
            align-items: center;
            justify-content: center;
            padding: 1rem;
            animation: fadeIn 0.3s ease;
        }

        .notify-modal.show {
            display: flex;
        }

        .notify-content {
            background: white;
            border-radius: 16px;
            padding: 0;
            max-width: 450px;
            width: 100%;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            transform: scale(0.9);
            animation: modalPop 0.3s ease forwards;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        @keyframes modalPop {
            to {
                transform: scale(1);
            }
        }

        .notify-header {
            padding: 2rem 2rem 1.5rem 2rem;
            text-align: center;
            border-bottom: 2px solid var(--border);
        }

        .notify-icon {
            width: 70px;
            height: 70px;
            margin: 0 auto 1rem;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
        }

        .notify-icon.success {
            background: linear-gradient(135deg, #4caf50, #388e3c);
            color: white;
        }

        .notify-icon.error {
            background: linear-gradient(135deg, #f44336, #d32f2f);
            color: white;
        }

        .notify-icon.warning {
            background: linear-gradient(135deg, #ff9800, #f57c00);
            color: white;
        }

        .notify-icon.question {
            background: linear-gradient(135deg, #2196F3, #1976d2);
            color: white;
        }

        .notify-header h3 {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--text-primary);
            margin: 0 0 0.5rem 0;
        }

        .notify-header p {
            color: var(--text-secondary);
            margin: 0;
            font-size: 0.95rem;
        }

        .notify-body {
            padding: 2rem;
        }

        .notify-actions {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 0.75rem;
        }

        .notify-actions.single {
            display: flex;
            justify-content: center;
        }

        .notify-actions.single .btn {
            width: auto;
            min-width: 120px;
        }


        .notify-actions .btn {
            margin: 0;
            padding: 0.875rem 1.5rem;
            font-size: 0.95rem;
        }
    </style>
</head>

<body>
    <div class="container">
        <!-- Page Header -->
        <div class="page-header">
            <h1> Payment Verification</h1>
            <p>Review and verify submitted payment proofs</p>
        </div>

        <!-- Statistics -->
        <div class="stats-grid">
            <div class="stat-card">
                <h3>Pending Verification</h3>
                <div class="stat-value"><?php echo $stats['pending_verification']; ?></div>
            </div>
            <div class="stat-card">
                <h3>Pending Amount</h3>
                <div class="stat-value">₱<?php echo number_format($stats['pending_amount'], 2); ?></div>
            </div>
            <div class="stat-card">
                <h3>Verified</h3>
                <div class="stat-value"><?php echo $stats['verified']; ?></div>
            </div>
            <div class="stat-card">
                <h3>Rejected</h3>
                <div class="stat-value"><?php echo $stats['rejected']; ?></div>
            </div>
        </div>

        <!-- Pending Payments -->
        <div class="payment-list">
            <?php if (empty($pending_payments)): ?>
                <div class="empty-state">
                    <i class="fas fa-check-circle"></i>
                    <h3> All Caught Up!</h3>
                    <p>No pending payment verifications at the moment.</p>
                </div>
            <?php else: ?>
                <?php foreach ($pending_payments as $payment): ?>
                    <div class="payment-card-compact">
                        <div class="payment-row">
                            <!-- Left: Applicant Info -->
                            <div class="applicant-section">
                                <div class="applicant-avatar-sm">
                                    <?php echo strtoupper(substr($payment['applicant_name'], 0, 1)); ?>
                                </div>
                                <div class="applicant-details-sm">
                                    <h4><?php echo htmlspecialchars($payment['applicant_name']); ?></h4>
                                    <div class="meta-row">
                                        <span><i class="fas fa-hashtag"></i>
                                            <?php echo htmlspecialchars($payment['tracking_number']); ?></span>
                                        <span><i class="fas fa-briefcase"></i>
                                            <?php echo htmlspecialchars($payment['service_name']); ?></span>
                                    </div>
                                </div>
                            </div>

                            <!-- Middle: Payment Info Grid -->
                            <div class="payment-info-grid">
                                <div class="info-item-compact">
                                    <div class="info-label-compact">AMOUNT</div>
                                    <div class="info-value-compact">₱<?php echo number_format($payment['payment_amount'], 2); ?>
                                    </div>
                                </div>
                                <div class="info-item-compact">
                                    <div class="info-label-compact">REFERENCE</div>
                                    <div class="info-value-compact">
                                        <?php echo htmlspecialchars($payment['payment_reference']); ?>
                                    </div>
                                </div>
                                <div class="info-item-compact">
                                    <div class="info-label-compact">SUBMITTED</div>
                                    <div class="info-value-compact">
                                        <?php echo date('M d, h:i A', strtotime($payment['payment_submitted_at'])); ?>
                                    </div>
                                </div>
                            </div>

                            <!-- Right: Proof & Actions -->
                            <div class="proof-actions-section">
                                <div class="proof-thumb"
                                    onclick="viewImage('<?php echo htmlspecialchars($payment['payment_proof']); ?>')">
                                    <img src="../<?php echo htmlspecialchars($payment['payment_proof']); ?>" alt="Proof">
                                    <div class="thumb-overlay">
                                        <i class="fas fa-search-plus"></i>
                                    </div>
                                </div>
                                <div class="action-btns-compact">
                                    <button onclick="verifyPayment(<?php echo $payment['id']; ?>)"
                                        class="btn-compact btn-verify" title="Verify Payment">
                                        <i class="fas fa-check"></i>
                                    </button>
                                    <button onclick="openRejectModal(<?php echo $payment['id']; ?>)"
                                        class="btn-compact btn-reject" title="Reject Payment">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Optional Notes -->
                        <?php if ($payment['payment_notes']): ?>
                            <div class="notes-row">
                                <i class="fas fa-sticky-note"></i>
                                <span><?php echo htmlspecialchars(substr($payment['payment_notes'], 0, 150)); ?><?php if (strlen($payment['payment_notes']) > 150): ?>...<?php endif; ?></span>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Reject Modal -->
    <div id="rejectModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>❌ Reject Payment</h3>
                <button class="close-modal" onclick="closeRejectModal()">&times;</button>
            </div>

            <form id="rejectForm">
                <input type="hidden" name="app_id" id="reject_app_id">

                <div class="form-group">
                    <label>Rejection Reason <span style="color: red;">*</span></label>
                    <textarea name="rejection_reason" id="rejection_reason" class="form-control" required
                        placeholder="Explain why this payment is being rejected..."></textarea>
                </div>

                <div class="modal-actions">
                    <button type="button" onclick="closeRejectModal()" class="btn btn-secondary">
                        Cancel
                    </button>
                    <button type="submit" class="btn btn-danger" id="rejectBtn">
                        <i class="fas fa-times"></i> Reject Payment
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Image Viewer Modal -->
    <div id="imageModal" class="modal image-modal" onclick="closeImageModal()">
        <div class="modal-content">
            <img id="modalImage" alt="Payment Proof">
        </div>
    </div>

    <!-- Notification Modal -->
    <div id="notifyModal" class="notify-modal">
        <div class="notify-content">
            <div class="notify-header">
                <div class="notify-icon" id="notifyIcon">
                    <i class="fas fa-check"></i>
                </div>
                <h3 id="notifyTitle">Success</h3>
                <p id="notifyMessage">Operation completed successfully</p>
            </div>
            <div class="notify-body">
                <div class="notify-actions" id="notifyActions">
                    <button class="btn btn-primary" id="notifyOkBtn">OK</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function viewImage(path) {
            document.getElementById('modalImage').src = '../' + path;
            document.getElementById('imageModal').classList.add('show');
        }

        function closeImageModal() {
            document.getElementById('imageModal').classList.remove('show');
        }

        function openRejectModal(appId) {
            document.getElementById('reject_app_id').value = appId;
            document.getElementById('rejectModal').classList.add('show');
        }

        function closeRejectModal() {
            document.getElementById('rejectModal').classList.remove('show');
            document.getElementById('rejectForm').reset();
        }
        async function verifyPayment(appId) {
            showNotification({
                type: 'question',
                icon: 'question-circle',
                title: 'Verify Payment',
                message: 'Are you sure you want to verify this payment?',
                showCancel: true,
                confirmText: 'Yes, Verify',
                cancelText: 'Cancel',
                onConfirm: async () => {
                    try {
                        const response = await fetch('../api/verify_payment.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                            },
                            body: `app_id=${appId}&action=verify`
                        });

                        const data = await response.json();

                        if (data.success) {
                            showNotification({
                                type: 'success',
                                icon: 'check-circle',
                                title: 'Payment Verified',
                                message: data.message,
                                onConfirm: () => location.reload()
                            });
                        } else {
                            showNotification({
                                type: 'error',
                                icon: 'exclamation-circle',
                                title: 'Error',
                                message: data.message
                            });
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        showNotification({
                            type: 'error',
                            icon: 'exclamation-triangle',
                            title: 'Error',
                            message: 'An error occurred. Please try again.'
                        });
                    }
                }
            });
        }

        console.log('✅ Payment verification page loaded successfully');
        // Custom Notification System
        function showNotification(options) {
            const modal = document.getElementById('notifyModal');
            const icon = document.getElementById('notifyIcon');
            const title = document.getElementById('notifyTitle');
            const message = document.getElementById('notifyMessage');
            const actions = document.getElementById('notifyActions');

            // Set icon
            icon.className = 'notify-icon ' + (options.type || 'success');
            icon.innerHTML = `<i class="fas fa-${options.icon || 'check'}"></i>`;

            // Set content
            title.textContent = options.title || 'Notification';
            message.textContent = options.message || '';

            // Clear previous actions
            actions.innerHTML = '';

            // Add buttons
            if (options.showCancel) {
                actions.className = 'notify-actions';

                const cancelBtn = document.createElement('button');
                cancelBtn.className = 'btn btn-secondary';
                cancelBtn.textContent = options.cancelText || 'Cancel';
                cancelBtn.onclick = () => {
                    closeNotification();
                    if (options.onCancel) options.onCancel();
                };

                const confirmBtn = document.createElement('button');
                // Use success (green) for question type, danger for error, primary for others
                if (options.type === 'question') {
                    confirmBtn.className = 'btn btn-success';
                } else if (options.type === 'error') {
                    confirmBtn.className = 'btn btn-danger';
                } else {
                    confirmBtn.className = 'btn btn-primary';
                }
                confirmBtn.textContent = options.confirmText || 'OK';
                confirmBtn.onclick = () => {
                    closeNotification();
                    if (options.onConfirm) options.onConfirm();
                };

                actions.appendChild(cancelBtn);
                actions.appendChild(confirmBtn);
            } else {
                actions.className = 'notify-actions single';

                const okBtn = document.createElement('button');
                // Use green button for success notifications
                if (options.type === 'success') {
                    okBtn.className = 'btn btn-success';
                } else if (options.type === 'error') {
                    okBtn.className = 'btn btn-danger';
                } else {
                    okBtn.className = 'btn btn-primary';
                }
                okBtn.textContent = 'OK';
                okBtn.onclick = () => {
                    closeNotification();
                    if (options.onConfirm) options.onConfirm();
                };

                actions.appendChild(okBtn);
            }

            modal.classList.add('show');
        }

        function closeNotification() {
            document.getElementById('notifyModal').classList.remove('show');
        }

        // Close on backdrop click
        document.getElementById('notifyModal').addEventListener('click', function (e) {
            if (e.target === this) {
                closeNotification();
            }
        });
        document.getElementById('rejectForm').addEventListener('submit', async function (e) {
            e.preventDefault();

            const formData = new FormData(this);
            formData.append('action', 'reject');

            const submitBtn = document.getElementById('rejectBtn');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';

            try {
                const response = await fetch('../api/verify_payment.php', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    closeRejectModal();
                    showNotification({
                        type: 'success',
                        icon: 'check-circle',
                        title: 'Payment Rejected',
                        message: data.message,
                        onConfirm: () => location.reload()
                    });
                } else {
                    showNotification({
                        type: 'error',
                        icon: 'exclamation-circle',
                        title: 'Error',
                        message: data.message
                    });
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<i class="fas fa-times"></i> Reject Payment';
                }
            } catch (error) {
                console.error('Error:', error);
                showNotification({
                    type: 'error',
                    icon: 'exclamation-triangle',
                    title: 'Error',
                    message: 'An error occurred. Please try again.'
                });
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-times"></i> Reject Payment';
            }
        });
    </script>
</body>

</html>

<?php include '../includes/footer.php'; ?>
