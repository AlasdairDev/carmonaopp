<?php
session_start();
require_once '../config.php';
require_once '../includes/functions.php';


if(!isLoggedIn() || $_SESSION['role'] !== 'user') {
  redirect('auth/login.php');
}


$user_id = $_SESSION['user_id'];
$user = getUserById($user_id);
include '../includes/header.php';
?>


<link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/apply-form.css">

<style>
.wrapper {
    background: linear-gradient(135deg, #f5f7fa 0%, #e8f0f7 100%);
    min-height: calc(100vh - 40px);
    border-radius: 24px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    position: relative;
}

.dashboard-banner {
    background: linear-gradient(135deg, #7cb342 0%, #9ccc65 100%);
    border-radius: 30px;
    padding: 3rem 2rem;
    margin-bottom: 2rem;
    box-shadow: 0 20px 60px rgba(0,0,0,0.15);
    position: relative;
    overflow: hidden;
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
</style>

<div class="wrapper" style="padding: 4rem 2rem;">
    <div class="page-wrapper" style="padding-top: 0;">
        <div class="container" style="max-width:1400px;">

<div class="dashboard-banner">
<h1>New Service Application</h1>
<p>Submit a new service request to the appropriate department</p>
</div>

<div class="row">
<div class="col-lg-12">
<div class="form-container">


<div class="form-body">


<!-- TOP SIDE-BY-SIDE BOXES -->
<div class="row" style="margin-bottom: 1.5rem;">


<!-- How to Apply -->
<div class="col-md-6">
  <div class="sidebar-widget h-100" id="howToApplyBox" style="min-height:520px;">
<div class="widget-header">◉ How to Apply</div>
<div class="widget-body">


<div class="step-list-item">
<div class="step-number-badge">1</div>
<div class="step-text">Select the department handling your request</div>
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
  <div class="sidebar-widget h-100" id="proTipsBox" style="min-height:520px;">
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








                          <form id="applicationForm" enctype="multipart/form-data">
                              <div class="section-card">
                                  <div class="section-header">
                                      <div class="section-icon-box">👤</div>
                                      <h2 class="section-title">Applicant Information</h2>
                                  </div>
                                  <div class="row">
                                      <div class="col-md-12">
                                          <div class="form-group">
                                              <label class="form-label">Full Name</label>
                                              <input type="text" class="form-control-modern"
                                                  value="<?php echo htmlspecialchars($user['name']); ?>" readonly>
                                          </div>
                                      </div>
                                      <div class="col-md-6">
                                          <div class="form-group">
                                              <label class="form-label">Email Address</label>
                                              <input type="email" class="form-control-modern"
                                                  value="<?php echo htmlspecialchars($user['email']); ?>" readonly>
                                          </div>
                                      </div>
                                      <div class="col-md-6">
                                          <div class="form-group">
                                              <label class="form-label">Mobile Number</label>
                                              <input type="text" class="form-control-modern"
                                                  value="<?php echo htmlspecialchars($user['mobile']); ?>" readonly>
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
                                      <select class="form-control-modern" id="department_select" name="department_name" required>
                                          <option value="">-- Choose your department --</option>
                                      </select>
                                  </div>








                                  <div class="form-group">
                                      <label for="service_select" class="form-label">
                                          Select Service/Document <span class="required-indicator">*</span>
                                      </label>
                                      <select class="form-control-modern" id="service_select" name="service_name" required disabled>
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
                                                  💰 <span id="serviceFee"></span>
                                              </div>
                                          </div>
                                      </div>
                                  </div>








                                  <div class="form-group">
                                      <label for="purpose" class="form-label">
                                          Purpose of Application <span class="required-indicator">*</span>
                                      </label>
                                      <textarea class="form-control-modern" id="purpose" name="purpose"
                                              rows="4" required
                                              placeholder="Please provide a detailed description of your application purpose..."></textarea>
                                  </div>








                                  <div class="form-group">
                                      <label for="location" class="form-label">Location/Address (if applicable)</label>
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








                                  <div class="upload-zone" id="uploadArea" onclick="document.getElementById('compiled_document').click()">
                                      <button type="button" style="padding: 0.75rem 2rem; border: 2px solid #7cb342; background: white; border-radius: 8px; font-size: 1rem; color: #558b2f; cursor: pointer; margin-bottom: 1rem; font-weight: 600;">
                                          Browse Files
                                      </button>
                                      <p class="upload-subtitle" style="color: #558b2f; margin: 0;">Choose a file</p>
                                      <input type="file"
                                          style="display: none;"
                                          id="compiled_document"
                                          name="compiled_document"
                                          accept=".pdf"
                                          required>
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
                                      <textarea class="form-control-modern" id="remarks" name="remarks"
                                              rows="3" placeholder="Any additional information or special requests..."></textarea>
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
                                  <button type="submit" class="btn-primary-custom" id="submitBtn">
                                      <span class="btn-text">➤ Submit Application</span>
                                      <span class="btn-loader" style="display:none;">
                                          <span class="spinner"></span> Processing...
                                      </span>
                                  </button>
                                  <a href="dashboard.php" class="btn-secondary-custom">Cancel</a>
                              </div>
                          </form>
                      </div>
                  </div>
              </div>
          </div>
      </div>
  </div>
</div>








<!-- ✅ ADDED MODAL (FULL 20 SERVICES) -->
<div id="deptGuideModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:9999;">
<div style="background:#fff; width:90%; max-width:650px; margin:80px auto; border-radius:8px; overflow:hidden;">
<div style="background:#6aa84f; color:#fff; padding:15px; display:flex; justify-content:space-between; align-items:center;">
<strong>Which Department Handles Your Document?</strong>
<button onclick="closeDeptGuideModal()" style="background:none; border:none; color:#fff; font-size:22px; cursor:pointer;">&times;</button>
</div>


<div style="padding:20px; max-height:420px; overflow-y:auto;">
<ul style="padding-left:18px; line-height:1.6;">
<li><b>Office of the City Mayor (OCM)</b>
<ul>
<li>Mayor’s Endorsement</li>
<li>Certification of No Major Source of Income / No Business</li>
<li>Permit for Motorcade</li>
<li>Occupational Tax Receipt (OTR)</li>
</ul>
</li>


<li><b>Office of the City Vice Mayor / Sangguniang Panlungsod (OCVM)</b>
<ul>
<li>Issuance of Legislative Documents</li>
<li>Sorteo ng Carmona Registration</li>
</ul>
</li>


<li><b>Office of the City Treasurer (OCT)</b>
<ul>
<li>Real Property Tax Payment</li>
<li>Issuance of Official Receipt</li>
<li>Transfer Tax</li>
<li>Professional Tax Receipt</li>
</ul>
</li>


<li><b>Office of the City Human Resource Management Officer (CHRMO)</b>
<ul>
<li>Feedback Processing</li>
<li>Request Data for Thesis / Research</li>
</ul>
</li>


<li><b>Office of the City Tricycle Franchise & Regulation Board (CTFRB)</b>
<ul>
<li>Tricycle Franchise Dropping</li>
</ul>
</li>


<li><b>Office of the City Planning & Development Coordinating Officer (CPDCO)</b>
<ul>
<li>Issuance of Certification</li>
</ul>
</li>


<li><b>Office of the City Urban Development & Housing Officer (CUDHO)</b>
<ul>
<li>Evaluation for Electrical and Water Connection</li>
</ul>
</li>


<li><b>Pagamutang Bayan ng Carmona (PBC)</b>
<ul>
<li>Hospital Record Services</li>
</ul>
</li>


<li><b>Local Economic Development & Investment Promotions Office (LEDIPO)</b>
<ul>
<li>DTI Business Name Registration (New / Renewal)</li>
<li>BMBE Registration</li>
<li>Request for Business Name Certification</li>
<li>Manila Southwoods RFID Endorsement</li>
</ul>
</li>
</ul>
</div>
</div>
</div>








<script src="<?php echo BASE_URL; ?>/assets/js/apply-form.js"></script>


<script>
function openDeptGuideModal() {
 document.getElementById('deptGuideModal').style.display = 'block';
}
function closeDeptGuideModal() {
 document.getElementById('deptGuideModal').style.display = 'none';
}
</script>



<script>
document.addEventListener("DOMContentLoaded", function () {
  const leftBox = document.getElementById("howToApplyBox");
  const rightBox = document.getElementById("proTipsBox");

  if (leftBox && rightBox) {
    const maxHeight = Math.max(
      leftBox.offsetHeight,
      rightBox.offsetHeight
    );

    leftBox.style.minHeight = maxHeight + "px";
    rightBox.style.minHeight = maxHeight + "px";
  }
});
</script>





<?php include '../includes/footer.php'; ?>



