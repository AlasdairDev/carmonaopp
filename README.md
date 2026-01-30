# Carmona Online Permit Portal (COPP)

The **Carmona Online Permit Portal (COPP)** is a centralized digital document request and tracking system designed to streamline government permit processing for the City of Carmona. This project supports the city's "Smart City" transformation by migrating paper-heavy, manual processes into a digital ecosystem to enhance urban functionality and transparency.

---

## Project Purpose

The purpose of the COPP is to provide a digital platform that simplifies the permit application process for residents of Carmona. It aims to reduce processing time and minimize the need for in-person visits by allowing users to submit requests, upload requirements, and track application status in real time. This system promotes good governance by increasing accountability through structured payment verification logs.

---

## Technical Stack

The portal is developed using a reliable, industry-standard stack optimized for local government infrastructure.

* **Front-End Technologies**: **HTML and CSS** are used for a government-branded interface with responsive layouts, while **JavaScript** handles frontend form validation and dynamic UI updates.
* **Back-End Technologies**: **PHP** serves as the core server-side language for business logic and database communication. **MariaDB (MySQL)** is used to organize complex data, including status history, user credentials, and audit logs.
* **Development Tools**: **XAMPP** was utilized as the primary local development and testing environment.
  
**Libraries and APIs**:
* **PHPMailer**: Integrated to manage automated email notifications for milestones and password resets.
* **TCPDF/FPDF**: Utilized for the automated generation of official, downloadable PDF reports and permit documents.
* **Semaphore API**: Connected via a dedicated API (`send_sms.php`) to send real-time mobile alerts to applicants.



---

## File Structure

```text
CARMONAOPP/
├── admin/
│   ├── activity_logs.php
│   ├── applications.php
│   ├── check_email_and_sms_logs.php
│   ├── dashboard.php
│   ├── manage_departments.php
│   ├── notifications.php
│   ├── profile.php
│   ├── reports.php
│   ├── users.php
│   ├── verify_payments.php
│   └── view_application.php
│
├── api/
│   ├── approve_with_payment.php
│   ├── cancel_application.php
│   ├── clear_logs.php
│   ├── create_notification.php
│   ├── deactivate_user.php
│   ├── export_email_sms_logs.php
│   ├── export_logs.php
│   ├── export_report_pdf.php
│   ├── get_departments.php
│   ├── get_notifications.php
│   ├── get_services.php
│   ├── get_user.php
│   ├── mark_notification_read.php
│   ├── notification_stream.php
│   ├── reactivate_user.php
│   ├── save_user.php
│   ├── submit_department_application.php
│   ├── submit_payment.php
│   ├── toggle_department.php
│   ├── toggle_service.php
│   ├── update_application.php
│   └── verify_payment.php
│
├── assets/
│   ├── css/
│   │   ├── admin/
│   │   │   ├── activity_logs_styles.css
│   │   │   ├── applications_styles.css
│   │   │   ├── check_email_and_sms_logs_styles.css
│   │   │   ├── dashboard_styles.css
│   │   │   ├── manage_departments_styles.css
│   │   │   ├── notifications_styles.css
│   │   │   ├── profile_styles.css
│   │   │   ├── reports_styles.css
│   │   │   ├── users_styles.css
│   │   │   ├── verify_payments_syles.css
│   │   │   └── view_applications_styles.css
│   │   ├── user/
│   │   │   ├── applications_styles.css
│   │   │   ├── apply_styles.css
│   │   │   ├── dashboard_styles.css
│   │   │   ├── notifications_styles.css
│   │   │   ├── profile_styles.css
│   │   │   ├── submit_payment_styles.css
│   │   │   ├── track_styles.css
│   │   │   └── view_application_styles.css
│   │   ├── admin-responsive.css
│   │   ├── admin.css
│   │   ├── apply-form.css
│   │   ├── style.css
│   │   └── user-responsive.css
│   │
│   ├── js/
│   │   ├── admin-script.js
│   │   ├── apply-form.js
│   │   ├── datepicker-init.js
│   │   └── main.js
│   │
│   └── uploads/
│   │   └── payments/
│   ├── carmona-city.jpg
│   ├── carmona-logo.png
│   ├── favicon.png
│   └── gcash-qr-code.png
│
├── auth/
│   ├── forgot_password.php
│   ├── login.php
│   ├── logout.php
│   ├── register.php
│   ├── reset_password.php
│   └── verify_email.php
│
├── database/
│   └── carmonaopp_db.sql
│
├── includes/
│   ├── email_templates.php
│   ├── footer.php
│   ├── functions.php
│   ├── header.php
│   ├── security.php
│   ├── send_email.php
│   └── send_sms.php
│
├── logs/
│
├── user/
│   ├── applications.php
│   ├── apply.php
│   ├── dashboard.php
│   ├── notifications.php
│   ├── profile.php
│   ├── submit_payment.php
│   ├── track.php
│   └── view_application.php
│
├── vendor/
│   ├── phpmailer/
│   ├── tecnickcom/
│   └── autoload.php
│
├── composer.json
├── composer.lock
├── config.php
├── generate_password.php
├── index.php
└── test_config.php

```

---

## Key Features

### For Residents (Applicants)

* **Application Submission**: Users can submit requests for various permits and upload required documents digitally.
* **PDF Compilation**: Supporting requirements must be compiled into a single PDF file for submission.
* **Real-Time Tracking**: A unique tracking number is generated upon submission to monitor application status.
* **Security Protocols**: Accounts are temporarily locked for 15 minutes after multiple unsuccessful login attempts.

### For LGU Personnel

* **Administrative Oversight**: Authorized staff can review applications, verify payments, and manage departments.
* **Audit Trail**: The system provides access to audit logs, activity logs, and communication logs to track all events.
* **Financial Tracking**: Structured verification logs ensure municipal revenue from permit fees is traceable.

---

## Contributors

**Group 11 | PUP COMP 016 (S.Y. 2025-2026)**

* Keith S. Ababao
* Kyla J. Barbin
* Roje Alasdair T. Evangelista
* Pauline R. Lacanilao

**Institution**: Polytechnic University of the Philippines

**Course**: COMP 016: Web Development
