<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/security.php';
// Check if user is admin
if (!isLoggedIn() || !isAdmin()) {
    http_response_code(403);
    die('Access denied');
}

// Check if TCPDF is installed
if (!file_exists(__DIR__ . '/../vendor/autoload.php')) {
    http_response_code(500);
    die('TCPDF library not installed. Please run: composer require tecnickcom/tcpdf');
}

require_once __DIR__ . '/../vendor/autoload.php';

// Get date range from request
$date_from = isset($_POST['date_from']) ? $_POST['date_from'] : (isset($_GET['date_from']) ? $_GET['date_from'] : date('Y-m-01'));
$date_to = isset($_POST['date_to']) ? $_POST['date_to'] : (isset($_GET['date_to']) ? $_GET['date_to'] : date('Y-m-t'));

// Get department filter
$dept_filter_data = getDepartmentFilter('a');
$dept_where = $dept_filter_data['where'] ? ' AND ' . $dept_filter_data['where'] : '';
$dept_params = $dept_filter_data['params'];
// Get chart images from POST
$chart_status = isset($_POST['chart_status']) ? $_POST['chart_status'] : '';
$chart_department = isset($_POST['chart_department']) ? $_POST['chart_department'] : '';
$chart_trend = isset($_POST['chart_trend']) ? $_POST['chart_trend'] : '';

try {
    // Fetch all the data (same as reports.php)

    // Applications by status
    $where_clause = "DATE(a.created_at) BETWEEN ? AND ?" . $dept_where;
    $params = array_merge([$date_from, $date_to], $dept_params);

    $stmt = $pdo->prepare("
    SELECT a.status, COUNT(*) as count
    FROM applications a
    WHERE $where_clause
    GROUP BY a.status
");
    $stmt->execute($params);
    $status_data = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

    // Applications by service
    $stmt = $pdo->prepare("
    SELECT s.service_name, COUNT(*) as count
    FROM applications a
    JOIN services s ON a.service_id = s.id
    WHERE $where_clause
    GROUP BY s.id, s.service_name
    ORDER BY count DESC
    LIMIT 10
");
    $stmt->execute($params);
    $service_data = $stmt->fetchAll();

    // Applications by department
    $stmt = $pdo->prepare("
    SELECT d.name as department_name, COUNT(*) as count
    FROM applications a
    JOIN departments d ON a.department_id = d.id
    WHERE $where_clause
    GROUP BY d.id, d.name
    ORDER BY count DESC
");
    $stmt->execute($params);
    $department_data = $stmt->fetchAll();

    // Revenue by service
    $stmt = $pdo->prepare("
    SELECT s.service_name, SUM(s.base_fee) as total_fee, COUNT(*) as count
    FROM applications a
    JOIN services s ON a.service_id = s.id
    WHERE $where_clause
    GROUP BY s.id, s.service_name
    ORDER BY total_fee DESC
");
    $stmt->execute($params);
    $revenue_data = $stmt->fetchAll();

    // Processing time analysis
    $stmt = $pdo->prepare("
    SELECT 
        AVG(DATEDIFF(a.updated_at, a.created_at)) as avg_days,
        MIN(DATEDIFF(a.updated_at, a.created_at)) as min_days,
        MAX(DATEDIFF(a.updated_at, a.created_at)) as max_days
    FROM applications a
    WHERE a.status IN ('Approved', 'Completed', 'Rejected')
    AND $where_clause
");
    $stmt->execute($params);
    $processing_stats = $stmt->fetch();

    // Top applicants
    $stmt = $pdo->prepare("
    SELECT u.name, u.email, COUNT(a.id) as total_apps
    FROM applications a
    JOIN users u ON a.user_id = u.id
    WHERE $where_clause
    GROUP BY a.user_id, u.name, u.email
    ORDER BY total_apps DESC
    LIMIT 10
");
    $stmt->execute($params);
    $top_applicants = $stmt->fetchAll();

    // Overall statistics
    $total_apps = array_sum($status_data);
    $total_revenue_stmt = $pdo->prepare("
    SELECT SUM(s.base_fee) 
    FROM applications a 
    JOIN services s ON a.service_id = s.id 
    WHERE $where_clause
");
    $total_revenue_stmt->execute($params);
    $total_revenue = $total_revenue_stmt->fetchColumn();

    $approval_rate = $total_apps > 0
        ? round((($status_data['Approved'] ?? 0) + ($status_data['Completed'] ?? 0)) / $total_apps * 100, 1)
        : 0;

    // Build HTML for PDF
    $html = '
    <style>
        body {
            font-family: helvetica, sans-serif;
            font-size: 10pt;
            color: #2c3e50;
        }
        
        .header {
            background-color: #8bc34a;
            color: white;
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 5px;
        }
        
        .header h1 {
            margin: 0;
            font-size: 20pt;
            font-weight: bold;
        }
        
        .header p {
            margin: 3px 0 0 0;
            font-size: 10pt;
        }
        
        .stats-table {
            width: 100%;
            margin-bottom: 15px;
        }
        
        .stat-card {
            background-color: #f8fafc;
            padding: 10px;
            border-left: 4px solid #8bc34a;
            text-align: center;
        }
        
        .stat-label {
            font-size: 8pt;
            font-weight: bold;
            color: #64748b;
            text-transform: uppercase;
            margin-bottom: 3px;
        }
        
        .stat-value {
            font-size: 16pt;
            font-weight: bold;
            color: #2c3e50;
        }
        
        .section-title {
            font-size: 12pt;
            font-weight: bold;
            color: #2c3e50;
            margin: 15px 0 10px 0;
            padding-bottom: 5px;
            border-bottom: 2px solid #8bc34a;
        }
        
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        
        table.data-table th {
            background-color: #f8fafc;
            padding: 8px;
            text-align: left;
            font-weight: bold;
            font-size: 9pt;
            color: #2c3e50;
            border-bottom: 2px solid #8bc34a;
        }
        
        table.data-table td {
            padding: 6px 8px;
            border-bottom: 1px solid #e2e8f0;
            font-size: 9pt;
        }
        
        .highlight {
            color: #8bc34a;
            font-weight: bold;
        }
        
        .footer-text {
            text-align: center;
            font-size: 8pt;
            color: #64748b;
            margin-top: 20px;
            padding-top: 10px;
            border-top: 1px solid #e2e8f0;
        }
    </style>
    
    <!-- Header -->
    <div class="header">
        <h1>Reports & Analytics</h1>
        <p>Period: ' . date('F d, Y', strtotime($date_from)) . ' - ' . date('F d, Y', strtotime($date_to)) . '</p>
        <p>Generated: ' . date('F d, Y h:i A') . '</p>
    </div>
    
    <!-- Statistics Summary -->
    <table class="stats-table" cellpadding="5">
        <tr>
            <td width="25%">
                <div class="stat-card">
                    <div class="stat-label">Total Applications</div>
                    <div class="stat-value">' . number_format($total_apps) . '</div>
                </div>
            </td>
            <td width="25%">
                <div class="stat-card">
                    <div class="stat-label">Total Revenue</div>
                    <div class="stat-value">PHP ' . number_format($total_revenue, 2) . '</div>
                </div>
            </td>
            <td width="25%">
                <div class="stat-card">
                    <div class="stat-label">Avg. Processing</div>
                    <div class="stat-value">' . round($processing_stats['avg_days'] ?? 0, 1) . ' days</div>
                </div>
            </td>
            <td width="25%">
                <div class="stat-card">
                    <div class="stat-label">Approval Rate</div>
                    <div class="stat-value">' . $approval_rate . '%</div>
                </div>
            </td>
        </tr>
    </table>
    
    <!-- Applications by Status -->
    <div class="section-title">Applications by Status</div>
    <table class="data-table" cellpadding="5" cellspacing="0">
        <thead>
            <tr>
                <th>Status</th>
                <th align="right">Count</th>
                <th align="right">Percentage</th>
            </tr>
        </thead>
        <tbody>';

    foreach ($status_data as $status => $count) {
        $percentage = $total_apps > 0 ? round(($count / $total_apps) * 100, 1) : 0;
        $html .= '
            <tr>
                <td><b>' . htmlspecialchars($status) . '</b></td>
                <td align="right">' . number_format($count) . '</td>
                <td align="right" class="highlight">' . $percentage . '%</td>
            </tr>';
    }

    $html .= '
        </tbody>
    </table>
    
    <!-- Applications by Department -->
    <div class="section-title">Applications by Department</div>
    <table class="data-table" cellpadding="5" cellspacing="0">
        <thead>
            <tr>
                <th>Department</th>
                <th align="right">Applications</th>
            </tr>
        </thead>
        <tbody>';

    foreach ($department_data as $dept) {
        $html .= '
            <tr>
                <td>' . htmlspecialchars($dept['department_name']) . '</td>
                <td align="right" class="highlight"><b>' . number_format($dept['count']) . '</b></td>
            </tr>';
    }

    $html .= '
        </tbody>
    </table>
    
    <!-- Revenue by Service -->
    <div class="section-title">Revenue by Service</div>
    <table class="data-table" cellpadding="5" cellspacing="0">
        <thead>
            <tr>
                <th>Service</th>
                <th align="right">Applications</th>
                <th align="right">Total Revenue</th>
                <th align="right">Avg. Fee</th>
            </tr>
        </thead>
        <tbody>';

    foreach ($revenue_data as $service) {
        $avg_fee = $service['count'] > 0 ? $service['total_fee'] / $service['count'] : 0;
        $html .= '
            <tr>
                <td><b>' . htmlspecialchars($service['service_name']) . '</b></td>
                <td align="right">' . number_format($service['count']) . '</td>
                <td align="right" class="highlight"><b>PHP ' . number_format($service['total_fee'], 2) . '</b></td>
                <td align="right">PHP ' . number_format($avg_fee, 2) . '</td>
            </tr>';
    }

    $html .= '
        </tbody>
    </table>
    
    <!-- Page Break Before Visual Analytics -->
    <div style="page-break-before: always;"></div>
    
    <!-- Charts Section -->
    <div class="section-title">Visual Analytics</div>';

    // Add Status Chart if available
    if (!empty($chart_status)) {
        $html .= '<div style="text-align: center; margin-bottom: 20px;">
            <p style="font-weight: bold; margin-bottom: 10px; font-size: 11pt;">Applications by Status</p>
            <img src="' . $chart_status . '" style="width: 450px; height: auto;" />
        </div>';
    }

    // Add Department Chart if available
    if (!empty($chart_department)) {
        $html .= '<div style="text-align: center; margin-bottom: 20px;">
            <p style="font-weight: bold; margin-bottom: 10px; font-size: 11pt;">Applications by Department</p>
            <img src="' . $chart_department . '" style="width: 550px; height: auto;" />
        </div>';
    }

    // Add Trend Chart if available (on same page as other charts)
    if (!empty($chart_trend)) {
        $html .= '<div style="page-break-before: always;"></div>
        <div style="text-align: center; margin-bottom: 20px;">
            <p style="font-weight: bold; margin-bottom: 10px; font-size: 11pt;">Daily Applications Trend</p>
            <img src="' . $chart_trend . '" style="width: 700px; height: auto;" />
        </div>';
    }

    $html .= '
    
    <!-- Top Applicants -->
    <div class="section-title">Top Applicants</div>
    <table class="data-table" cellpadding="5" cellspacing="0">
        <thead>
            <tr>
                <th width="5%">#</th>
                <th>Name</th>
                <th>Email</th>
                <th align="right">Total Applications</th>
            </tr>
        </thead>
        <tbody>';

    $rank = 1;
    foreach ($top_applicants as $applicant) {
        $html .= '
            <tr>
                <td><b>' . $rank++ . '</b></td>
                <td>' . htmlspecialchars($applicant['name']) . '</td>
                <td>' . htmlspecialchars($applicant['email']) . '</td>
                <td align="right" class="highlight"><b>' . $applicant['total_apps'] . '</b></td>
            </tr>';
    }

    $html .= '
        </tbody>
    </table>
    
    <!-- Footer -->
    <div class="footer-text">
        <p>This report was automatically generated by the Permit Tracking System</p>
        <p>© ' . date('Y') . ' - Confidential Document</p>
    </div>';

    // Create PDF using TCPDF
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

    // Set document information
    $pdf->SetCreator('Permit Tracking System');
    $pdf->SetAuthor('Admin');
    $pdf->SetTitle('Report - ' . $date_from . ' to ' . $date_to);
    $pdf->SetSubject('Application Report');

    // Set margins
    $pdf->SetMargins(15, 15, 15);
    $pdf->SetHeaderMargin(5);
    $pdf->SetFooterMargin(10);

    // Set auto page breaks
    $pdf->SetAutoPageBreak(TRUE, 15);

    // Remove default header/footer
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);

    // Set font
    $pdf->SetFont('helvetica', '', 10);

    // Set image scale factor
    $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

    // Set JPEG quality for better image rendering
    $pdf->setJPEGQuality(90);

    // Add a page
    $pdf->AddPage();

    // Write HTML content
    $pdf->writeHTML($html, true, false, true, false, '');

    // Output PDF
    $filename = 'Report_' . $date_from . '_to_' . $date_to . '.pdf';
    $pdf->Output($filename, 'D'); // D = Download

} catch (Exception $e) {
    http_response_code(500);
    error_log('PDF Export Error: ' . $e->getMessage());
    die('Error generating PDF: ' . $e->getMessage());
}