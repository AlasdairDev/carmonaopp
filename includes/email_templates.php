<?php
// Email template configuration
define('EMAIL_HEADER_COLOR', '#2c3e50');
define('EMAIL_PRIMARY_COLOR', '#3498db');
define('EMAIL_SUCCESS_COLOR', '#27ae60');
define('EMAIL_WARNING_COLOR', '#f39c12');
define('EMAIL_DANGER_COLOR', '#e74c3c');

// Base email template
function getEmailTemplate($title, $content, $footer_text = '') {
    $year = date('Y');
    $footer = $footer_text ?: "This is an automated message from the Local Government Permit System. Please do not reply to this email.";
    
    return "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <style>
            body {
                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
                line-height: 1.6;
                color: #333;
                margin: 0;
                padding: 0;
                background-color: #f4f4f4;
            }
            .container {
                max-width: 600px;
                margin: 0 auto;
                background: white;
            }
            .header {
                background: " . EMAIL_HEADER_COLOR . ";
                color: white;
                padding: 30px 20px;
                text-align: center;
            }
            .header h1 {
                margin: 0;
                font-size: 24px;
            }
            .content {
                padding: 40px 30px;
            }
            .button {
                display: inline-block;
                padding: 12px 30px;
                background: " . EMAIL_PRIMARY_COLOR . ";
                color: white !important;
                text-decoration: none;
                border-radius: 5px;
                margin: 20px 0;
                font-weight: bold;
            }
            .info-box {
                background: #f8f9fa;
                border-left: 4px solid " . EMAIL_PRIMARY_COLOR . ";
                padding: 15px;
                margin: 20px 0;
            }
            .status-badge {
                display: inline-block;
                padding: 8px 16px;
                border-radius: 5px;
                font-weight: bold;
                margin: 10px 0;
            }
            .status-pending { background: #fff3cd; color: #856404; }
            .status-processing { background: #d1ecf1; color: #0c5460; }
            .status-approved { background: #d4edda; color: #155724; }
            .status-rejected { background: #f8d7da; color: #721c24; }
            .status-completed { background: #d1ecf1; color: #004085; }
            .footer {
                background: #f8f9fa;
                padding: 20px;
                text-align: center;
                font-size: 12px;
                color: #666;
                border-top: 1px solid #dee2e6;
            }
            .divider {
                height: 1px;
                background: #dee2e6;
                margin: 30px 0;
            }
            table {
                width: 100%;
                border-collapse: collapse;
                margin: 20px 0;
            }
            table td {
                padding: 10px;
                border-bottom: 1px solid #dee2e6;
            }
            table td:first-child {
                font-weight: bold;
                width: 40%;
            }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>🏛️ Local Government Permit System</h1>
                <p style='margin: 10px 0 0 0; opacity: 0.9;'>{$title}</p>
            </div>
            <div class='content'>
                {$content}
            </div>
            <div class='footer'>
                <p>{$footer}</p>
                <p style='margin-top: 10px;'>&copy; {$year} Local Government Permit System. All rights reserved.</p>
            </div>
        </div>
    </body>
    </html>
    ";
}

// Welcome email template
function getWelcomeEmail($user_name, $email) {
    $content = "
        <h2>Welcome, {$user_name}! 👋</h2>
        <p>Thank you for registering with our Local Government Permit System. Your account has been successfully created.</p>
        
        <div class='info-box'>
            <strong>Your Account Details:</strong>
            <table>
                <tr>
                    <td>Name:</td>
                    <td>{$user_name}</td>
                </tr>
                <tr>
                    <td>Email:</td>
                    <td>{$email}</td>
                </tr>
            </table>
        </div>
        
        <p>You can now:</p>
        <ul>
            <li>Submit permit applications online</li>
            <li>Track your application status in real-time</li>
            <li>Upload required documents</li>
            <li>Receive instant notifications</li>
        </ul>
        
        <div style='text-align: center;'>
            <a href='" . BASE_URL . "/login.php' class='button'>Login to Your Account</a>
        </div>
        
        <div class='divider'></div>
        
        <p><strong>Need help getting started?</strong></p>
        <p>Visit our help center or contact us at " . SMTP_FROM_EMAIL . "</p>
    ";
    
    return getEmailTemplate('Welcome to Our Service', $content);
}

// Application submitted email
function getApplicationSubmittedEmail($user_name, $tracking_number, $permit_type, $fee) {
    $content = "
        <h2>Application Submitted Successfully! ✅</h2>
        <p>Dear {$user_name},</p>
        <p>We have received your permit application. Here are the details:</p>
        
        <div class='info-box'>
            <strong>Application Details:</strong>
            <table>
                <tr>
                    <td>Tracking Number:</td>
                    <td><strong>{$tracking_number}</strong></td>
                </tr>
                <tr>
                    <td>Permit Type:</td>
                    <td>{$permit_type}</td>
                </tr>
                <tr>
                    <td>Processing Fee:</td>
                    <td>₱" . number_format($fee, 2) . "</td>
                </tr>
                <tr>
                    <td>Status:</td>
                    <td><span class='status-badge status-pending'>Pending</span></td>
                </tr>
                <tr>
                    <td>Submitted:</td>
                    <td>" . date('F d, Y h:i A') . "</td>
                </tr>
            </table>
        </div>
        
        <p><strong>What happens next?</strong></p>
        <ol>
            <li>Our team will review your application and documents</li>
            <li>You'll receive email updates on any status changes</li>
            <li>You can track your application anytime using your tracking number</li>
        </ol>
        
        <div style='text-align: center;'>
            <a href='" . BASE_URL . "/user/track.php' class='button'>Track Your Application</a>
        </div>
        
        <div class='divider'></div>
        
        <p><strong>Important:</strong> Please keep your tracking number <strong>{$tracking_number}</strong> for reference.</p>
    ";
    
    return getEmailTemplate('Application Submitted', $content);
}

// Status update email
function getStatusUpdateEmail($user_name, $tracking_number, $permit_type, $old_status, $new_status, $remarks = '') {
    $status_colors = [
        'Pending' => 'status-pending',
        'Processing' => 'status-processing',
        'Approved' => 'status-approved',
        'Rejected' => 'status-rejected',
        'Completed' => 'status-completed'
    ];
    
    $status_class = $status_colors[$new_status] ?? 'status-pending';
    
    $status_messages = [
        'Processing' => 'Your application is now being processed by our team.',
        'Approved' => 'Congratulations! Your application has been approved.',
        'Rejected' => 'Unfortunately, your application has been rejected.',
        'Completed' => 'Great news! Your permit is ready for release.'
    ];
    
    $status_message = $status_messages[$new_status] ?? 'Your application status has been updated.';
    
    $content = "
        <h2>Application Status Updated 🔔</h2>
        <p>Dear {$user_name},</p>
        <p>{$status_message}</p>
        
        <div class='info-box'>
            <strong>Application Details:</strong>
            <table>
                <tr>
                    <td>Tracking Number:</td>
                    <td><strong>{$tracking_number}</strong></td>
                </tr>
                <tr>
                    <td>Permit Type:</td>
                    <td>{$permit_type}</td>
                </tr>
                <tr>
                    <td>Previous Status:</td>
                    <td><span class='status-badge " . ($status_colors[$old_status] ?? '') . "'>{$old_status}</span></td>
                </tr>
                <tr>
                    <td>Current Status:</td>
                    <td><span class='status-badge {$status_class}'>{$new_status}</span></td>
                </tr>
                <tr>
                    <td>Updated:</td>
                    <td>" . date('F d, Y h:i A') . "</td>
                </tr>
            </table>
        </div>
        
        " . ($remarks ? "
        <div class='info-box' style='border-left-color: " . EMAIL_WARNING_COLOR . ";'>
            <strong>Admin Remarks:</strong>
            <p style='margin: 10px 0 0 0;'>" . nl2br(htmlspecialchars($remarks)) . "</p>
        </div>
        " : "") . "
        
        <div style='text-align: center;'>
            <a href='" . BASE_URL . "/user/view_application.php?id={$tracking_number}' class='button'>View Full Details</a>
        </div>
        
        <div class='divider'></div>
        
        <p>For any questions, please contact us at " . SMTP_FROM_EMAIL . "</p>
    ";
    
    return getEmailTemplate('Status Update - ' . $new_status, $content);
}

// Document request email
function getDocumentRequestEmail($user_name, $tracking_number, $permit_type, $requested_documents) {
    $docs_list = '';
    foreach ($requested_documents as $doc) {
        $docs_list .= "<li>" . htmlspecialchars($doc) . "</li>";
    }
    
    $content = "
        <h2>Additional Documents Required 📄</h2>
        <p>Dear {$user_name},</p>
        <p>We need additional documents to process your application.</p>
        
        <div class='info-box'>
            <strong>Application Details:</strong>
            <table>
                <tr>
                    <td>Tracking Number:</td>
                    <td><strong>{$tracking_number}</strong></td>
                </tr>
                <tr>
                    <td>Permit Type:</td>
                    <td>{$permit_type}</td>
                </tr>
            </table>
        </div>
        
        <p><strong>Required Documents:</strong></p>
        <ul style='line-height: 2;'>
            {$docs_list}
        </ul>
        
        <p>Please submit these documents as soon as possible to avoid delays in processing your application.</p>
        
        <div style='text-align: center;'>
            <a href='" . BASE_URL . "/user/view_application.php?id={$tracking_number}' class='button'>Upload Documents</a>
        </div>
    ";
    
    return getEmailTemplate('Additional Documents Required', $content);
}

// Approval notification email
function getApprovalEmail($user_name, $tracking_number, $permit_type, $release_date = '') {
    $release_info = $release_date 
        ? "<p><strong>Expected Release Date:</strong> {$release_date}</p>" 
        : "<p>You will be notified once your permit is ready for release.</p>";
    
    $content = "
        <h2>🎉 Application Approved!</h2>
        <p>Dear {$user_name},</p>
        <p>Congratulations! Your {$permit_type} application has been approved.</p>
        
        <div class='info-box' style='border-left-color: " . EMAIL_SUCCESS_COLOR . ";'>
            <strong>Application Details:</strong>
            <table>
                <tr>
                    <td>Tracking Number:</td>
                    <td><strong>{$tracking_number}</strong></td>
                </tr>
                <tr>
                    <td>Permit Type:</td>
                    <td>{$permit_type}</td>
                </tr>
                <tr>
                    <td>Status:</td>
                    <td><span class='status-badge status-approved'>Approved</span></td>
                </tr>
                <tr>
                    <td>Approved Date:</td>
                    <td>" . date('F d, Y') . "</td>
                </tr>
            </table>
        </div>
        
        {$release_info}
        
        <p><strong>Next Steps:</strong></p>
        <ol>
            <li>Wait for the completion notification</li>
            <li>Bring a valid ID when claiming your permit</li>
            <li>Pay any remaining fees if applicable</li>
        </ol>
        
        <div style='text-align: center;'>
            <a href='" . BASE_URL . "/user/view_application.php?id={$tracking_number}' class='button'>View Details</a>
        </div>
    ";
    
    return getEmailTemplate('Application Approved', $content);
}

// Rejection notification email
function getRejectionEmail($user_name, $tracking_number, $permit_type, $reason) {
    $content = "
        <h2>Application Update ⚠️</h2>
        <p>Dear {$user_name},</p>
        <p>We regret to inform you that your application has been rejected.</p>
        
        <div class='info-box' style='border-left-color: " . EMAIL_DANGER_COLOR . ";'>
            <strong>Application Details:</strong>
            <table>
                <tr>
                    <td>Tracking Number:</td>
                    <td><strong>{$tracking_number}</strong></td>
                </tr>
                <tr>
                    <td>Permit Type:</td>
                    <td>{$permit_type}</td>
                </tr>
                <tr>
                    <td>Status:</td>
                    <td><span class='status-badge status-rejected'>Rejected</span></td>
                </tr>
            </table>
        </div>
        
        <div class='info-box' style='border-left-color: " . EMAIL_DANGER_COLOR . ";'>
            <strong>Reason for Rejection:</strong>
            <p style='margin: 10px 0 0 0;'>" . nl2br(htmlspecialchars($reason)) . "</p>
        </div>
        
        <p><strong>What you can do:</strong></p>
        <ul>
            <li>Review the rejection reason carefully</li>
            <li>Address the issues mentioned</li>
            <li>Submit a new application when ready</li>
            <li>Contact us for clarification if needed</li>
        </ul>
        
        <div style='text-align: center;'>
            <a href='" . BASE_URL . "/user/apply.php' class='button'>Submit New Application</a>
        </div>
        
        <div class='divider'></div>
        
        <p>For questions, contact us at " . SMTP_FROM_EMAIL . "</p>
    ";
    
    return getEmailTemplate('Application Rejected', $content);
}

// Completion notification email
function getCompletionEmail($user_name, $tracking_number, $permit_type) {
    $content = "
        <h2>🎊 Permit Ready for Release!</h2>
        <p>Dear {$user_name},</p>
        <p>Great news! Your {$permit_type} is now ready for release.</p>
        
        <div class='info-box' style='border-left-color: " . EMAIL_SUCCESS_COLOR . ";'>
            <strong>Permit Details:</strong>
            <table>
                <tr>
                    <td>Tracking Number:</td>
                    <td><strong>{$tracking_number}</strong></td>
                </tr>
                <tr>
                    <td>Permit Type:</td>
                    <td>{$permit_type}</td>
                </tr>
                <tr>
                    <td>Status:</td>
                    <td><span class='status-badge status-completed'>Completed</span></td>
                </tr>
                <tr>
                    <td>Completion Date:</td>
                    <td>" . date('F d, Y') . "</td>
                </tr>
            </table>
        </div>
        
        <p><strong>How to Claim Your Permit:</strong></p>
        <ol>
            <li>Visit our office during business hours (Monday-Friday, 8:00 AM - 5:00 PM)</li>
            <li>Bring your tracking number: <strong>{$tracking_number}</strong></li>
            <li>Present a valid government-issued ID</li>
            <li>Sign the release form</li>
        </ol>
        
        <div class='info-box'>
            <strong>Office Address:</strong>
            <p style='margin: 10px 0 0 0;'>
                Local Government Office<br>
                Main Street, City Center<br>
                Contact: " . SMTP_FROM_EMAIL . "
            </p>
        </div>
        
        <div style='text-align: center;'>
            <a href='" . BASE_URL . "/user/view_application.php?id={$tracking_number}' class='button'>View Details</a>
        </div>
        
        <div class='divider'></div>
        
        <p><strong>Thank you for using our online permit system!</strong></p>
    ";
    
    return getEmailTemplate('Permit Ready for Release', $content);
}

// Password reset email
function getPasswordResetEmail($user_name, $email, $reset_token) {
    $reset_link = BASE_URL . "/reset_password.php?token=" . $reset_token;
    
    $content = "
        <h2>Password Reset Request 🔒</h2>
        <p>Dear {$user_name},</p>
        <p>We received a request to reset your password for your account ({$email}).</p>
        
        <p>Click the button below to reset your password:</p>
        
        <div style='text-align: center;'>
            <a href='{$reset_link}' class='button'>Reset Password</a>
        </div>
        
        <div class='divider'></div>
        
        <p><strong>Security Information:</strong></p>
        <ul>
            <li>This link will expire in 1 hour</li>
            <li>If you didn't request this, please ignore this email</li>
            <li>Your password won't change until you access the link and create a new one</li>
        </ul>
        
        <div class='info-box' style='border-left-color: " . EMAIL_WARNING_COLOR . ";'>
            <strong>⚠️ Important:</strong>
            <p style='margin: 10px 0 0 0;'>If you didn't request a password reset, your account may be at risk. Please contact us immediately at " . SMTP_FROM_EMAIL . "</p>
        </div>
    ";
    
    return getEmailTemplate('Password Reset Request', $content, 'This password reset link will expire in 1 hour.');
}

// Send email using PHPMailer (if available) or mail()
function sendTemplateEmail($to, $subject, $template_html, $to_name = '') {
    // Try to use PHPMailer if available
    if (class_exists('PHPMailer\PHPMailer\PHPMailer')) {
        return sendEmailPHPMailer($to, $subject, $template_html, $to_name);
    } else {
        // Fallback to PHP mail()
        return sendEmailNative($to, $subject, $template_html);
    }
}

// Send email using native PHP mail()
function sendEmailNative($to, $subject, $html_content) {
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=utf-8\r\n";
    $headers .= "From: " . SMTP_FROM_NAME . " <" . SMTP_FROM_EMAIL . ">\r\n";
    $headers .= "Reply-To: " . SMTP_FROM_EMAIL . "\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";
    
    return mail($to, $subject, $html_content, $headers);
}

// Send email using PHPMailer (SMTP)
function sendEmailPHPMailer($to, $subject, $html_content, $to_name = '') {
    return sendEmailNative($to, $subject, $html_content);
}
?>
