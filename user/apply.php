<?php
session_start();
require_once '../config.php';
require_once '../includes/functions.php';


if (!isLoggedIn() || $_SESSION['role'] !== 'user') {
    redirect('auth/login.php');
}


$user_id = $_SESSION['user_id'];
$user = getUserById($user_id);
// Department guide will be loaded via AJAX - no static loading needed
$departments_guide = []; // Empty array - will be populated via AJAX in the modal
include '../includes/header.php';
?>


<link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/apply-form.css">
<link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/user-responsive.css">

<style>
    /* Match the exact wrapper and banner styling from other pages */
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


    /* EXACT match from dashboard.php, applications.php, track.php */
    .dashboard-banner {
        background: linear-gradient(135deg, #7cb342 0%, #9ccc65 100%);
        border-radius: 30px;
        padding: 3rem 2rem;
        margin-bottom: 2rem;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
        position: relative;
        overflow: hidden;
        margin: 0 0 2rem 0;
        /* Explicit margin control */
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


    /* Disable hover states on section cards */
    .section-card {
        transition: none !important;
    }


    .section-card:hover {
        transform: none !important;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08) !important;
    }


    /* Unified button border-radius */
    .btn-primary-custom,
    .btn-secondary-custom {
        border-radius: 25px !important;
    }


    .btn-primary-custom {
        padding: 0.875rem 2rem;
        background: linear-gradient(135deg, #7cb342 0%, #9ccc65 100%);
        color: white;
        border: none;
        font-weight: 700;
        font-size: 1rem;
        cursor: pointer;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(124, 179, 66, 0.3);
    }


    .btn-primary-custom:hover {
        background: linear-gradient(135deg, #689f38 0%, #8bc34a 100%);
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(124, 179, 66, 0.4);
    }


    .btn-secondary-custom {
        padding: 0.875rem 2rem;
        background: white;
        color: #558b2f;
        border: 2px solid #dcedc8;
        font-weight: 700;
        font-size: 1rem;
        cursor: pointer;
        transition: all 0.3s ease;
        text-decoration: none;
        display: inline-flex;
        /* Change from inline-block to inline-flex */
        align-items: center;
        /* Add this */
        justify-content: center;
        /* Add this */
    }


    .btn-secondary-custom:hover {
        background: #f1f8e9;
        border-color: #7cb342;
        transform: translateY(-2px);
    }


    /* Form container styling */
    .form-container {
        background: white;
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
    }


    .form-body {
        padding: 2rem;
    }


    .section-card {
        background: white;
        border: 2px solid #f0f0f0;
        border-radius: 15px;
        padding: 2rem;
        margin-bottom: 1.5rem;
    }


    .section-header {
        display: flex;
        align-items: center;
        gap: 1rem;
        margin-bottom: 1.5rem;
        padding-bottom: 1rem;
        border-bottom: 2px solid #f0f0f0;
    }


    .section-icon-box {
        width: 50px;
        height: 50px;
        background: linear-gradient(135deg, #7cb342, #9ccc65);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        color: white;
    }


    .section-title {
        font-size: 1.3rem;
        color: #2d3748;
        font-weight: 700;
        margin: 0;
    }


    .form-label {
        display: block;
        margin-bottom: 0.5rem;
        color: #558b2f;
        font-weight: 600;
        font-size: 0.95rem;
    }


    .required-indicator {
        color: #dc3545;
    }


    .form-control-modern {
        width: 100%;
        padding: 0.875rem 1.25rem;
        border: 2px solid #dcedc8;
        border-radius: 12px;
        font-size: 1rem;
        transition: all 0.3s ease;
        background: white;
    }


    .form-control-modern:focus {
        outline: none;
        border-color: #7cb342;
        box-shadow: 0 0 0 4px rgba(124, 179, 66, 0.1);
    }


    .form-control-modern:read-only {
        background: #f1f8e9;
    }


    .form-control-modern:not(:disabled):not(:read-only) {
        cursor: pointer;
    }

    select.form-control-modern {
        cursor: pointer;
    }

    select.form-control-modern:not(:disabled) {
        cursor: pointer;
    }

    select.form-control-modern:disabled {
        cursor: not-allowed;
        opacity: 0.6;
    }

    .requirements-panel {
        background: #f1f8e9;
        border: 2px solid #dcedc8;
        border-radius: 12px;
        padding: 1.5rem;
        margin-top: 1rem;
    }


    .requirements-title {
        font-size: 1.1rem;
        font-weight: 700;
        color: #558b2f;
        margin-bottom: 1rem;
    }


    .info-badges {
        margin-top: 1rem;
        padding-top: 1rem;
        border-top: 2px solid #dcedc8;
    }


    .info-badge {
        display: inline-block;
        padding: 0.5rem 1rem;
        background: white;
        border-radius: 8px;
        color: #558b2f;
        font-weight: 600;
    }


    .alert-box {
        background: #fff3e0;
        border-left: 4px solid #ffc107;
        padding: 1.5rem;
        border-radius: 8px;
        margin-bottom: 1.5rem;
    }


    .alert-title {
        font-weight: 700;
        color: #856404;
        margin-bottom: 0.75rem;
    }


    .alert-list {
        margin: 0;
        padding-left: 1.5rem;
    }


    .alert-list li {
        margin-bottom: 0.5rem;
        color: #856404;
    }


    .upload-zone {
        border: 3px dashed #dcedc8;
        border-radius: 12px;
        padding: 2rem;
        text-align: center;
        background: #f8faf8;
        cursor: pointer;
        transition: all 0.3s ease;
    }


    .upload-zone:hover {
        border-color: #7cb342;
        background: #f1f8e9;
    }


    .upload-subtitle {
        color: #558b2f;
        margin: 0;
        font-size: 0.95rem;
    }


    .terms-box {
        display: flex;
        gap: 1rem;
        padding: 1.5rem;
        background: #f8faf8;
        border-radius: 12px;
        margin-bottom: 1.5rem;
    }


    .terms-checkbox {
        margin-top: 0.25rem;
        width: 20px;
        height: 20px;
        cursor: pointer;
    }


    .terms-text {
        color: #2d3748;
        line-height: 1.6;
        font-size: 0.95rem;
    }


    .button-group {
        display: flex;
        gap: 1rem;
        justify-content: flex-end;
    }


    .btn-loader {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }


    .spinner {
        width: 16px;
        height: 16px;
        border: 2px solid rgba(255, 255, 255, 0.3);
        border-top-color: white;
        border-radius: 50%;
        animation: spin 0.8s linear infinite;
    }


    @keyframes spin {
        to {
            transform: rotate(360deg);
        }
    }


    /* Sidebar widgets */
    .sidebar-widget {
        background: white;
        border: 2px solid #f0f0f0;
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
    }


    .widget-header {
        background: linear-gradient(135deg, #7cb342, #9ccc65);
        color: white;
        padding: 1rem 1.5rem;
        font-weight: 700;
        font-size: 1.1rem;
    }


    #howToApplyBox .widget-body {
        padding: 0.75rem;
        /* Decrease padding */
    }


    #howToApplyBox .widget-body {
        padding: 0.5rem;
        /* Decrease padding */
    }


    #howToApplyBox .step-list-item+div {
        margin-bottom: 0rem;
        margin-top: 0rem;
    }


    #howToApplyBox .step-text {
        font-size: 0.9rem;
        line-height: 1;
    }


    #howToApplyBox a {
        font-size: 0.85rem;
        line-height: 1.1;
    }


    #proTipsBox .widget-body {
        padding: 2rem;
        /* Increase padding */
    }


    #proTipsBox .tip-item {
        padding: 0.7rem;
    }


    .step-list-item .step-text,
    .tip-item,
    .detail-value,
    .terms-text,
    .upload-subtitle {
        color: #2d3748 !important;
    }


    .step-number-badge {
        width: 24px;
        height: 24px;
        background: #7cb342;
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 0.9rem;
        flex-shrink: 0;
    }


    .step-text {
        flex: 1;
        color: #2d3748;
        line-height: 1.2;
    }


    .tip-item {
        padding: 0.5rem 0;
        color: #2d3748;
        line-height: 1.6;
    }


    .modal-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.6);
        backdrop-filter: blur(4px);
        z-index: 10000;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 1rem;
        animation: fadeIn 0.3s ease;
    }


    @keyframes fadeIn {
        from {
            opacity: 0;
        }


        to {
            opacity: 1;
        }
    }


    .modal-container {
        width: 100%;
        max-width: 600px;
        animation: slideUp 0.4s ease;
    }


    @keyframes slideUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }


        to {
            opacity: 1;
            transform: translateY(0);
        }
    }


    .modal-content {
        background: white;
        border-radius: 24px;
        padding: 3rem 2.5rem;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        position: relative;
    }


    .success-icon-wrapper {
        display: flex;
        justify-content: center;
        margin-bottom: 2rem;
    }


    .success-checkmark {
        width: 100px;
        height: 100px;
    }


    .checkmark-svg {
        width: 100%;
        height: 100%;
    }


    .checkmark-circle {
        stroke: #7cb342;
        stroke-width: 2;
        stroke-dasharray: 166;
        stroke-dashoffset: 166;
        animation: stroke 0.6s cubic-bezier(0.65, 0, 0.45, 1) forwards;
    }


    .checkmark-check {
        stroke: #7cb342;
        stroke-width: 3;
        stroke-linecap: round;
        stroke-dasharray: 48;
        stroke-dashoffset: 48;
        animation: stroke 0.3s cubic-bezier(0.65, 0, 0.45, 1) 0.8s forwards;
    }


    @keyframes stroke {
        100% {
            stroke-dashoffset: 0;
        }
    }


    .modal-title {
        font-size: 1.75rem;
        font-weight: 800;
        color: #2d3748;
        text-align: center;
        margin: 0 0 0.5rem 0;
    }


    .modal-subtitle {
        text-align: center;
        color: #718096;
        font-size: 1rem;
        margin: 0 0 2rem 0;
    }


    .tracking-section {
        background: linear-gradient(135deg, #f1f8e9 0%, #e8f5e9 100%);
        border: 3px solid #7cb342;
        border-radius: 16px;
        padding: 1.5rem;
        margin-bottom: 2rem;
        text-align: center;
    }


    .tracking-label {
        font-size: 0.9rem;
        font-weight: 600;
        color: #558b2f;
        margin-bottom: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }


    .tracking-number-display {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.75rem;
        margin-bottom: 0.75rem;
    }


    .tracking-number-text {
        font-family: 'Courier New', monospace;
        font-size: 1.5rem;
        font-weight: 800;
        color: #7cb342;
        letter-spacing: 1px;
    }


    .copy-tracking-btn {
        padding: 0.5rem;
        background: white;
        border: 2px solid #7cb342;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
    }


    .copy-tracking-btn:hover {
        background: #7cb342;
        transform: scale(1.1);
    }


    .copy-tracking-btn:hover svg {
        stroke: white;
    }


    .copy-tracking-btn svg {
        stroke: #7cb342;
        transition: all 0.3s ease;
    }


    .tracking-hint {
        font-size: 0.875rem;
        color: #558b2f;
        margin: 0;
        font-weight: 600;
    }


    .modal-details-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 1rem;
        margin-bottom: 2rem;
    }


    .detail-item {
        background: #f8faf8;
        border: 2px solid #e0e0e0;
        border-radius: 12px;
        padding: 1rem;
        display: flex;
        align-items: center;
        gap: 1rem;
    }


    .detail-icon {
        font-size: 2rem;
        flex-shrink: 0;
    }


    .detail-label {
        font-size: 0.75rem;
        color: #718096;
        font-weight: 600;
        text-transform: uppercase;
        margin-bottom: 0.25rem;
    }


    .detail-value {
        font-size: 0.9rem;
        color: #2d3748;
        font-weight: 700;
    }


    .next-steps-section {
        background: #fff9e6;
        border-left: 4px solid #ffc107;
        border-radius: 12px;
        padding: 1.5rem;
        margin-bottom: 2rem;
    }


    .next-steps-title {
        font-size: 1.1rem;
        font-weight: 700;
        color: #856404;
        margin: 0 0 1rem 0;
    }


    .steps-list {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }


    .step-item {
        display: flex;
        align-items: flex-start;
        gap: 1rem;
    }


    .step-number {
        width: 32px;
        height: 32px;
        background: #7cb342;
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        flex-shrink: 0;
    }


    .step-text {
        flex: 1;
        color: #856404;
        line-height: 1.6;
        padding-top: 0.25rem;
    }


    .modal-actions {
        display: flex;
        gap: 1rem;
        flex-direction: column;
    }


    .btn-modal {
        padding: 1rem 1.5rem;
        border-radius: 12px;
        font-weight: 700;
        font-size: 1rem;
        cursor: pointer;
        transition: all 0.3s ease;
        border: none;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        text-decoration: none;
    }


    .btn-primary-modal {
        background: linear-gradient(135deg, #7cb342 0%, #9ccc65 100%);
        color: white;
        box-shadow: 0 4px 15px rgba(124, 179, 66, 0.3);
    }


    .btn-primary-modal:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(124, 179, 66, 0.4);
    }


    .btn-secondary-modal {
        background: white;
        color: #558b2f;
        border: 2px solid #dcedc8;
    }


    .btn-secondary-modal:hover {
        background: #f1f8e9;
        border-color: #7cb342;
        transform: translateY(-2px);
    }


    /* Search box styling */
    .dept-search-container {
        padding: 15px 20px;
        background: #f8faf8;
        border-bottom: 2px solid #dcedc8;
    }


    .dept-search-box {
        position: relative;
        width: 100%;
    }


    .dept-search-input {
        width: 100%;
        padding: 12px 45px 12px 15px;
        border: 2px solid #dcedc8;
        border-radius: 8px;
        font-size: 14px;
        transition: all 0.3s ease;
    }


    .dept-search-input:focus {
        outline: none;
        border-color: #7cb342;
        box-shadow: 0 0 0 3px rgba(124, 179, 66, 0.1);
    }


    .dept-search-icon {
        position: absolute;
        right: 15px;
        top: 50%;
        transform: translateY(-50%);
        color: #7cb342;
    }


    .dept-item {
        margin-bottom: 15px;
    }


    .dept-item.hidden {
        display: none;
    }


    .no-results {
        text-align: center;
        padding: 30px;
        color: #718096;
        display: none;
    }


    .no-results.show {
        display: block;
    }

    < !--========================================REQUIRED CSS (Add to your <style> tag)========================================--><style>

    /* Modal Base */
    .modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        backdrop-filter: blur(4px);
        z-index: 10000;
        align-items: center;
        /* ADD THIS LINE */
        justify-content: center;
        /* ADD THIS LINE */
    }

    .modal[style*="display: flex"],
    .modal.show {
        display: flex !important;
    }

    .modal-content {
        background: white;
        border-radius: 12px;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        animation: modalSlideIn 0.3s ease;
    }

    @keyframes modalSlideIn {
        from {
            transform: scale(0.9);
            opacity: 0;
        }

        to {
            transform: scale(1);
            opacity: 1;
        }
    }

    .modal-actions {
        display: grid;
        gap: 0.75rem;
        margin-top: 1.5rem;
    }

    .btn {
        padding: 0.875rem 1.5rem;
        border-radius: 8px;
        font-weight: 600;
        border: none;
        cursor: pointer;
        transition: all 0.3s ease;
        font-size: 1rem;
    }

    .btn-primary {
        background: linear-gradient(135deg, #8bc34a 0%, #689f38 100%);
        color: white;
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(139, 195, 74, 0.3);
    }

    #successModal button:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(139, 195, 74, 0.3);
    }

    #successModal .copy-btn:hover {
        background: #8bc34a !important;
        transform: scale(1.1);
    }

    #successModal .copy-btn:hover svg {
        stroke: white;
    }

    .modal-backdrop {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 9999;
    }

    .modal-content {
        position: relative;
        background: white;
        border-radius: 8px;
        padding: 2rem;
        max-width: 500px;
        z-index: 10000;
    }
</style>


<div class="wrapper">
    <div class="page-wrapper">
        <div class="container">


            <div class="dashboard-banner">
                <h1>New Service Application</h1>
                <p>Submit a new service request to the appropriate department</p>
            </div>


            <div class="row">
                <div class="col-lg-12">
                    <div class="form-container">


                        <div class="form-body">
                            <form id="applicationForm" enctype="multipart/form-data">
                                <!-- TOP SIDE-BY-SIDE BOXES -->
                                <div class="row" style="margin-bottom: 1.5rem;">


                                    <!-- How to Apply -->
                                    <div class="col-md-6">
                                        <div class="sidebar-widget h-100" id="howToApplyBox" style="height:450px;">
                                            <div class="widget-header">◉ How to Apply</div>
                                            <div class="widget-body">


                                                <div class="step-list-item">
                                                    <div class="step-number-badge">1</div>
                                                    <div class="step-text">Select the department handling your request
                                                    </div>
                                                </div>


                                                <div style="margin-left:35px; margin-top:5px;">
                                                    <a href="javascript:void(0);" onclick="openDeptGuideModal()"
                                                        style="font-size:14px; color:#6aa84f; text-decoration:underline;">
                                                        Not sure which department to choose? <strong>Click here</strong>
                                                    </a>
                                                </div>


                                                <div class="step-list-item">
                                                    <div class="step-number-badge">2</div>
                                                    <div class="step-text">Choose the specific service you need</div>
                                                </div>


                                                <div class="step-list-item">
                                                    <div class="step-number-badge">3</div>
                                                    <div class="step-text">Review the required documents list</div>
                                                </div>


                                                <div class="step-list-item">
                                                    <div class="step-number-badge">4</div>
                                                    <div class="step-text">Compile all documents into ONE PDF file</div>
                                                </div>


                                                <div class="step-list-item">
                                                    <div class="step-number-badge">5</div>
                                                    <div class="step-text">Upload the compiled PDF</div>
                                                </div>


                                                <div class="step-list-item">
                                                    <div class="step-number-badge">6</div>
                                                    <div class="step-text">Submit and receive tracking number</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>


                                    <!-- Pro Tips -->
                                    <div class="col-md-6">
                                        <div class="sidebar-widget h-100" id="proTipsBox" style="height:450px;">
                                            <div class="widget-header">◆ Pro Tips</div>
                                            <div class="widget-body">
                                                <div class="tip-item">✓ Scan documents in high quality</div>
                                                <div class="tip-item">✓ Ensure all text is readable</div>
                                                <div class="tip-item">✓ Arrange pages in correct order</div>
                                                <div class="tip-item">✓ Include all required documents</div>
                                                <div class="tip-item">✓ Check file size before uploading</div>
                                                <div class="tip-item">✓ Save your tracking number</div>
                                            </div>
                                        </div>
                                    </div>


                                </div>


                                <div class="section-card">
                                    <div class="section-header">
                                        <div class="section-icon-box">◉</div>
                                        <h2 class="section-title">Service Details</h2>
                                    </div>


                                    <div class="form-group">
                                        <label for="department_select" class="form-label">
                                            Select Department <span class="required-indicator">*</span>
                                        </label>
                                        <select class="form-control-modern" id="department_select"
                                            name="department_name" required>
                                            <option value="">-- Choose your department --</option>
                                        </select>
                                    </div>


                                    <div class="form-group">
                                        <label for="service_select" class="form-label">
                                            Select Service/Document <span class="required-indicator">*</span>
                                        </label>
                                        <select class="form-control-modern" id="service_select" name="service_name"
                                            required disabled>
                                            <option value="">-- First select a department --</option>
                                        </select>
                                    </div>


                                    <div id="requirementsBox" style="display: none;">
                                        <div class="requirements-panel">
                                            <div class="requirements-title">
                                                ▣ Required Documents
                                            </div>
                                            <div id="requirementsList"></div>
                                            <div class="info-badges">
                                                <div class="info-badge">
                                                    <span id="serviceFee"></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>


                                    <div class="form-group">
                                        <label for="purpose" class="form-label">
                                            Purpose of Application <span class="required-indicator">*</span>
                                        </label>
                                        <textarea class="form-control-modern" id="purpose" name="purpose" rows="4"
                                            required
                                            placeholder="Please provide a detailed description of your application purpose..."></textarea>
                                    </div>


                                    <div class="form-group">
                                        <label for="location" class="form-label">Location/Address (if
                                            applicable)</label>
                                        <input type="text" class="form-control-modern" id="location" name="location"
                                            placeholder="Enter location or property address if required">
                                    </div>
                                </div>


                                <div class="section-card">
                                    <div class="section-header">
                                        <div class="section-icon-box">◈</div>
                                        <h2 class="section-title">Document Upload</h2>
                                    </div>


                                    <div class="alert-box">
                                        <div class="alert-title">⚠ IMPORTANT INSTRUCTIONS:</div>
                                        <ul class="alert-list">
                                            <li>Compile ALL required documents into ONE PDF file</li>
                                            <li>Ensure all pages are clear and readable</li>
                                            <li>Arrange documents in the order listed above</li>
                                            <li>Maximum file size: 10MB</li>
                                            <li>Accepted format: PDF only</li>
                                        </ul>
                                    </div>
                                    <!-- NEW: Tutorial Help Box -->
                                    <div
                                        style="background: #e3f2fd; border: 2px solid #2196f3; border-radius: 12px; padding: 1rem 1.5rem; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 1rem;">
                                        <div
                                            style="width: 40px; height: 40px; background: #2196f3; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="white"
                                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <circle cx="12" cy="12" r="10"></circle>
                                                <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path>
                                                <line x1="12" y1="17" x2="12.01" y2="17"></line>
                                            </svg>
                                        </div>
                                        <div style="flex: 1;">
                                            <strong style="color: #1565c0; display: block; margin-bottom: 0.25rem;">Need
                                                Help?</strong>
                                            <span style="color: #1976d2; font-size: 0.95rem;">Don't know how to compile
                                                files into PDF?</span>
                                        </div>
                                        <a href="https://www.youtube.com/watch?v=wrVcc0-14Kc" target="_blank"
                                            rel="noopener noreferrer"
                                            style="padding: 0.75rem 1.5rem; background: #2196f3; color: white; border-radius: 8px; text-decoration: none; font-weight: 600; white-space: nowrap; transition: all 0.3s ease; display: inline-flex; align-items: center; gap: 0.5rem;"
                                            onmouseover="this.style.background='#1976d2'; this.style.transform='translateY(-2px)'"
                                            onmouseout="this.style.background='#2196f3'; this.style.transform='translateY(0)'">
                                            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                                                <path
                                                    d="M10 16.5l6-4.5-6-4.5v9zM12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8z" />
                                            </svg>
                                            Watch Tutorial
                                        </a>
                                    </div>
                                    <!-- Credit Footer -->
                                    <p
                                        style="font-size: 0.8rem; color: #718096; text-align: right; margin: -1rem 0 1rem 0; font-style: italic;">
                                        Tutorial by: <a href="https://www.youtube.com/@Tuto2InfoVideos" target="_blank"
                                            rel="noopener noreferrer" style="color: #2196f3; text-decoration: none;">
                                            Tuto2Info Videos</a>
                                    </p>
                                    <div class="upload-zone" id="uploadArea">
                                        <button type="button"
                                            onclick="document.getElementById('compiled_document').click()"
                                            style="padding: 0.75rem 2rem; border: 2px solid #7cb342; background: white; border-radius: 8px; font-size: 1rem; color: #558b2f; cursor: pointer; margin-bottom: 1rem; font-weight: 600;">
                                            Browse Files
                                        </button>
                                        <p class="upload-subtitle">Choose a file</p>
                                        <input type="file" style="display: none;" id="compiled_document"
                                            name="compiled_document" accept=".pdf">
                                    </div>


                                    <div id="filePreview" style="display: none;"></div>
                                </div>


                                <div class="section-card">
                                    <div class="section-header">
                                        <div class="section-icon-box">✎</div>
                                        <h2 class="section-title">Additional Information</h2>
                                    </div>
                                    <div class="form-group">
                                        <label for="remarks" class="form-label">Remarks/Notes (Optional)</label>
                                        <textarea class="form-control-modern" id="remarks" name="remarks" rows="3"
                                            placeholder="Any additional information or special requests..."></textarea>
                                    </div>
                                </div>


                                <div class="terms-box">
                                    <input type="checkbox" id="terms" name="terms" class="terms-checkbox" required>
                                    <label for="terms" class="terms-text">
                                        I certify that all information provided is true and correct.
                                        I have compiled all required documents into the uploaded PDF file.
                                        I understand that providing false information or incomplete documents
                                        may result in the rejection of my application.
                                    </label>
                                </div>


                                <div class="button-group">
                                    <a href="dashboard.php" class="btn-secondary-custom">Cancel</a>
                                    <button type="submit" class="btn-primary-custom" id="submitBtn">
                                        <span class="btn-text">➤ Submit Application</span>
                                        <span class="btn-loader" style="display:none;">
                                            <span class="spinner"></span> Processing...
                                        </span>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<!-- ✅ UPDATED MODAL WITH SEARCH -->
<div id="deptGuideModal"
    style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:9999;">
    <div style="background:#fff; width:90%; max-width:650px; margin:80px auto; border-radius:8px; overflow:hidden;">
        <div
            style="background:#6aa84f; color:#fff; padding:15px; display:flex; justify-content:space-between; align-items:center;">
            <strong>Which Department Handles Your Document?</strong>
            <button onclick="closeDeptGuideModal()"
                style="background:none; border:none; color:#fff; font-size:22px; cursor:pointer;">&times;</button>
        </div>


        <!-- SEARCH BOX -->
        <div class="dept-search-container">
            <div class="dept-search-box">
                <input type="text" id="deptSearchInput" class="dept-search-input"
                    placeholder="Search for a service or department..." onkeyup="filterDepartments()">
                <span class="dept-search-icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8"></circle>
                        <path d="m21 21-4.35-4.35"></path>
                    </svg>
                </span>
            </div>
        </div>

        <div style="padding:20px; max-height:420px; overflow-y:auto;" id="deptListContainer">
            <ul style="padding-left:18px; line-height:1.6;" id="deptList">
                <!-- Will be populated via AJAX -->
                <li class="dept-item">
                    <div style="text-align: center; padding: 20px;">
                        <div class="spinner" style="margin: 0 auto 10px;"></div>
                        <p>Loading departments...</p>
                    </div>
                </li>
            </ul>

            <div class="no-results" id="noResults">
                <p><strong>No results found</strong></p>
                <p>Try searching with different keywords</p>
            </div>
        </div>
    </div>
</div>

<!-- Requirement Image Modal -->
<div id="requirementImageModal"
    style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.8); z-index:10001;"
    onclick="closeRequirementImage()">
    <div onclick="event.stopPropagation()"
        style="position:relative; width:90%; max-width:800px; margin:5% auto; background:white; border-radius:12px; padding:2rem; box-shadow: 0 20px 60px rgba(0,0,0,0.5);">
        <button onclick="closeRequirementImage()"
            style="position:absolute; top:15px; right:15px; background:#dc3545; color:white; border:none; border-radius:50%; width:40px; height:40px; font-size:24px; cursor:pointer; font-weight:bold; z-index:10002; transition: all 0.2s ease;">
            &times;
        </button>
        <h3 id="requirementImageTitle"
            style="margin-bottom:1.5rem; color:#2d3748; font-size:1.5rem; padding-right: 50px;">Sample Document</h3>
        <div style="max-height: 70vh; overflow-y: auto;">
            <img id="requirementImage" src="" alt="Requirement Sample"
                style="width:100%; height:auto; border-radius:8px; border:2px solid #dcedc8; display:block;">
        </div>
        <p style="margin-top:1rem; color:#718096; font-size:0.9rem; text-align:center; font-style: italic;">Click
            outside the image or press the X button to close</p>
    </div>
</div>
<script src="<?php echo BASE_URL; ?>/assets/js/apply-form.js"></script>


<script>
    // Load department guide data via AJAX
    async function loadDepartmentGuide() {
        const deptList = document.getElementById('deptList');

        try {
            const response = await fetch('../api/get_departments.php');
            const data = await response.json();

            if (data.success && data.departments && data.departments.length > 0) {
                deptList.innerHTML = '';

                for (const dept of data.departments) {
                    // Fetch services for this department
                    const servicesResponse = await fetch(`../api/get_services.php?department_id=${dept.id}`);
                    const servicesData = await servicesResponse.json();

                    if (servicesData.success && servicesData.services && servicesData.services.length > 0) {
                        const deptItem = document.createElement('li');
                        deptItem.className = 'dept-item';

                        let servicesHTML = '<ul>';
                        servicesData.services.forEach(service => {
                            servicesHTML += `<li>${service.service_name}</li>`;
                        });
                        servicesHTML += '</ul>';

                        deptItem.innerHTML = `
                            <b>${dept.name} (${dept.code})</b>
                            ${servicesHTML}
                        `;

                        deptList.appendChild(deptItem);
                    }
                }

                if (deptList.children.length === 0) {
                    deptList.innerHTML = '<li class="dept-item" style="text-align: center; color: #6c757d;">No departments with services available</li>';
                }
            } else {
                deptList.innerHTML = '<li class="dept-item" style="text-align: center; color: #6c757d;">No departments available</li>';
            }
        } catch (error) {
            console.error('Error loading department guide:', error);
            deptList.innerHTML = '<li class="dept-item" style="text-align: center; color: #dc3545;">Error loading departments</li>';
        }
    }

    function openDeptGuideModal() {
        document.getElementById('deptGuideModal').style.display = 'block';
        loadDepartmentGuide();
    }

    function closeDeptGuideModal() {
        document.getElementById('deptGuideModal').style.display = 'none';
        document.getElementById('deptSearchInput').value = '';
        filterDepartments();
    }

    function filterDepartments() {
        const searchInput = document.getElementById('deptSearchInput');
        const filter = searchInput.value.toLowerCase();
        const deptItems = document.querySelectorAll('.dept-item');
        const noResults = document.getElementById('noResults');
        let visibleCount = 0;

        deptItems.forEach(item => {
            const text = item.textContent.toLowerCase();
            if (text.includes(filter)) {
                item.classList.remove('hidden');
                visibleCount++;
            } else {
                item.classList.add('hidden');
            }
        });

        if (visibleCount === 0) {
            noResults.classList.add('show');
        } else {
            noResults.classList.remove('show');
        }
    }
</script>

<!-- SUCCESS MODAL - Clean Design -->
<div id="successModal" class="modal"
    style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.5); backdrop-filter: blur(4px); align-items: center; justify-content: center; z-index: 9999;">
    <div class="modal-content" style="max-width: 500px; text-align: center; padding: 2.5rem;">

        <!-- Success Icon -->
        <div
            style="width: 80px; height: 80px; margin: 0 auto 1.5rem; background: linear-gradient(135deg, #8bc34a, #689f38); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="3"
                stroke-linecap="round" stroke-linejoin="round">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                <polyline points="22 4 12 14.01 9 11.01"></polyline>
            </svg>
        </div>

        <!-- Title -->
        <h3 style="font-size: 1.5rem; font-weight: 700; color: #2c3e50; margin-bottom: 1.5rem;">
            Application Submitted Successfully
        </h3>

        <!-- Tracking Number Box -->
        <div
            style="background: linear-gradient(135deg, rgba(139, 195, 74, 0.1) 0%, rgba(102, 187, 106, 0.1) 100%); border: 2px solid #8bc34a; border-radius: 12px; padding: 1.5rem; margin-bottom: 1.5rem;">
            <label
                style="display: block; font-size: 0.75rem; font-weight: 700; color: #689f38; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 0.75rem;">
                Your Tracking Number
            </label>
            <div
                style="display: flex; align-items: center; justify-content: center; gap: 0.75rem; margin-bottom: 0.75rem;">
                <span id="modalTrackingNumber"
                    style="font-family: 'Courier New', monospace; font-size: 1.75rem; font-weight: 800; color: #558b2f; letter-spacing: 1px;">
                    CRMN-2026-000000
                </span>
                <button type="button" onclick="copyTrackingNumber()"
                    style="padding: 0.5rem; background: white; border: 2px solid #8bc34a; border-radius: 8px; cursor: pointer; transition: all 0.3s ease; display: flex; align-items: center; justify-content: center;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#8bc34a" stroke-width="2">
                        <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
                        <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
                    </svg>
                </button>
            </div>
            <p style="font-size: 0.875rem; color: #64748b; margin: 0;">
                Save this number to track your application
            </p>
        </div>

        <!-- OK Button -->
        <button onclick="document.getElementById('successModal').style.display='none'"
            style="width: auto; padding: 1rem 3rem; font-size: 1rem; font-weight: 700; background: linear-gradient(135deg, #8bc34a, #689f38); color: white; border: none; border-radius: 8px; cursor: pointer; transition: all 0.3s ease; font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;">
            OK
        </button>
    </div>
</div>



<?php include '../includes/footer.php'; ?>