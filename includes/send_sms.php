<?php
require_once __DIR__ . '/../config.php';

/**
 * Send SMS via Semaphore API
 */
function sendSMS($phoneNumber, $message, $user_id = null, $application_id = null, $department_id = null)
{
    global $pdo;

    // Check if SMS is enabled
    if (!defined('SMS_ENABLED') || !SMS_ENABLED) {
        error_log("SMS disabled in config");
        return false;
    }

    // Check if API key is configured
    if (!defined('SEMAPHORE_API_KEY')) {
        error_log("SEMAPHORE_API_KEY constant not defined");
        logSMS($phoneNumber, $message, 'failed', 'API key not configured', $user_id, $application_id, $department_id);
        return false; 
    }

    if (
        SEMAPHORE_API_KEY === 'e57e3ac833f5121582d1dc49295f8b4c' ||
        empty(SEMAPHORE_API_KEY) ||
        strlen(SEMAPHORE_API_KEY) < 10
    ) {
        error_log("Semaphore API key not properly configured: " . SEMAPHORE_API_KEY);
        logSMS($phoneNumber, $message, 'failed', 'Invalid API key', $user_id, $application_id, $department_id);
        return false;
    }

    // Format and validate phone number
    $phoneNumber = formatPhoneNumberForSMS($phoneNumber);

    if (!$phoneNumber) {
        error_log("Invalid phone number format");
        logSMS($phoneNumber, $message, 'failed', 'Invalid phone number format', $user_id, $application_id, $department_id);
        return false;
    }

    // Truncate message if too long (160 chars = 1 SMS)
    if (strlen($message) > 160) {
        $message = substr($message, 0, 157) . '...';
    }

    // Prepare API request
    $ch = curl_init();
    $senderName = defined('SEMAPHORE_SENDER_NAME') ? SEMAPHORE_SENDER_NAME : 'LGU';

    $parameters = [
        'apikey' => SEMAPHORE_API_KEY,
        'number' => $phoneNumber,
        'message' => $message,
        'sendername' => $senderName
    ];

    curl_setopt_array($ch, [
        CURLOPT_URL => 'https://api.semaphore.co/api/v4/messages',
        CURLOPT_POST => 1,
        CURLOPT_POSTFIELDS => http_build_query($parameters),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => true, 
        CURLOPT_TIMEOUT => 30,
        CURLOPT_CONNECTTIMEOUT => 10
    ]);

    // Execute request
    $output = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if (curl_errno($ch)) {
        $error = curl_error($ch);
        curl_close($ch);
        error_log("SMS CURL Error: " . $error);
        logSMS($phoneNumber, $message, 'failed', $error, $user_id, $application_id, $department_id);
        return false;
    }

    curl_close($ch);

    // Parse API response
    $response = json_decode($output, true);

    // Determine status based on response
    $status = 'failed';
    $error = null;

    if ($httpCode == 200) {
        // Semaphore returns array of message objects
        if (is_array($response) && isset($response[0]['message_id'])) {
            $status = 'sent';
            error_log("SMS sent successfully to $phoneNumber (ID: {$response[0]['message_id']})");
        } elseif (isset($response['message'])) {
            $error = $response['message'];
            error_log("SMS API error: $error");
        } else {
            $error = 'Unknown response format: ' . $output;
            error_log("SMS unknown response: $output");
        }
    } else {
        $error = "HTTP $httpCode";
        if (isset($response['message'])) {
            $error .= ': ' . $response['message'];
        } elseif (isset($response['error'])) {
            $error .= ': ' . $response['error'];
        } else {
            $error .= ': ' . $output;
        }
        error_log("SMS HTTP error: $error");
    }

    // Log SMS attempt
    logSMS($phoneNumber, $message, $status, $error, $user_id, $application_id, $department_id);

    return $status === 'sent';
}

/**
 * Format phone number to 09XXXXXXXXX format
 */
function formatPhoneNumberForSMS($phone)
{
    // Remove all non-numeric characters
    $phone = preg_replace('/[^0-9]/', '', $phone);

    // Convert +63XXXXXXXXXX to 09XXXXXXXXX (12 digits starting with 63)
    if (strlen($phone) == 12 && substr($phone, 0, 2) == '63') {
        $phone = '0' . substr($phone, 2);
    }

    // Convert 639XXXXXXXXX to 09XXXXXXXXX (12 digits starting with 639)
    if (strlen($phone) == 12 && substr($phone, 0, 3) == '639') {
        $phone = '0' . substr($phone, 2);
    }

    // Validate: Must be exactly 11 digits starting with 09
    if (strlen($phone) == 11 && substr($phone, 0, 2) == '09') {
        return $phone;
    }

    // Invalid format
    return false;
}

/**
 * Log SMS to database
 */
function logSMS($phoneNumber, $message, $status, $error = null, $user_id = null, $application_id = null, $department_id = null)
{
    global $pdo;

    // Check if PDO connection exists
    if (!isset($pdo)) {
        error_log("Cannot log SMS: PDO connection not available");
        return false;
    }

    try {
        $stmt = $pdo->prepare("
            INSERT INTO sms_logs (user_id, application_id, department_id, phone_number, message, status, error_message, sent_at, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, " . ($status == 'sent' ? 'NOW()' : 'NULL') . ", NOW())
        ");
        $stmt->execute([$user_id, $application_id, $department_id, $phoneNumber, $message, $status, $error]);
        return true;
    } catch (PDOException $e) {
        error_log("Failed to log SMS: " . $e->getMessage());
        return false;
    }
}

/**
 * Get SMS template for application status updates
 */
function getSMSTemplate($application, $status)
{
    $tracking = $application['tracking_number'];
    $senderName = defined('SEMAPHORE_SENDER_NAME') ? SEMAPHORE_SENDER_NAME : 'LGU';

    $messages = [
        'Processing' => "$senderName: Your application $tracking is now being processed. We'll notify you of updates.",
        'Approved' => "$senderName: Good news! Your application $tracking has been APPROVED.",
        'Rejected' => "$senderName: Your application $tracking requires revision. Check your email for details.",
        'Completed' => "$senderName: Your application $tracking is COMPLETED and ready for pickup!"
    ];

    // Return specific message or generic status update
    return $messages[$status] ?? "$senderName: Application $tracking status updated to $status";
}

/**
 * Send bulk SMS notifications
 */
function sendBulkSMS($recipients, $message)
{
    $results = ['sent' => 0, 'failed' => 0];

    foreach ($recipients as $phone) {
        if (sendSMS($phone, $message)) {
            $results['sent']++;
        } else {
            $results['failed']++;
        }

        sleep(1);
    }

    return $results;
}
