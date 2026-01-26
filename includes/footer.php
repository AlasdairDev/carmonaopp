</main>


   <!-- Footer -->
   <footer class="footer" style="background: var(--bg-secondary); color: var(--text-secondary); padding: 1rem 0; margin-top: auto; border-top: 1px solid var(--border-color);">
       <div class="container">
           <!-- Footer Bottom -->
           <div style="text-align: center;">
               <!-- Footer content removed -->
           </div>
       </div>
   </footer>


   
   <script>
   // Auto-hide alerts after 5 seconds
   document.addEventListener('DOMContentLoaded', function() {
       const alerts = document.querySelectorAll('.alert');
       alerts.forEach(alert => {
           setTimeout(() => {
               alert.style.opacity = '0';
               alert.style.transition = 'opacity 0.5s';
               setTimeout(() => alert.remove(), 500);
           }, 5000);
       });
   });


   // Smooth scroll to top
   function scrollToTop() {
       window.scrollTo({
           top: 0,
           behavior: 'smooth'
       });
   }


   // Add scroll to top button if page is long
   if (document.body.scrollHeight > window.innerHeight * 2) {
       const scrollBtn = document.createElement('button');
       scrollBtn.innerHTML = '↑';
       scrollBtn.style.cssText = `
           position: fixed;
           bottom: 2rem;
           right: 2rem;
           width: 50px;
           height: 50px;
           border-radius: 50%;
           background: var(--primary);
           color: white;
           border: none;
           cursor: pointer;
           font-size: 1.5rem;
           display: none;
           z-index: 1000;
           box-shadow: var(--shadow-md);
           transition: var(--transition);
       `;
       scrollBtn.onclick = scrollToTop;
       document.body.appendChild(scrollBtn);


       // Show/hide scroll button
       window.addEventListener('scroll', function() {
           if (window.pageYOffset > 300) {
               scrollBtn.style.display = 'block';
           } else {
               scrollBtn.style.display = 'none';
           }
       });


       scrollBtn.addEventListener('mouseenter', function() {
           this.style.transform = 'scale(1.1)';
       });


       scrollBtn.addEventListener('mouseleave', function() {
           this.style.transform = 'scale(1)';
       });
   }


   // Form validation helper
   function validateForm(formId) {
       const form = document.getElementById(formId);
       if (!form) return true;


       const inputs = form.querySelectorAll('input[required], select[required], textarea[required]');
       let isValid = true;


       inputs.forEach(input => {
           if (!input.value.trim()) {
               input.style.borderColor = 'var(--danger)';
               isValid = false;
           } else {
               input.style.borderColor = '';
           }
       });


       return isValid;
   }


   // Prevent double form submission
   document.addEventListener('DOMContentLoaded', function() {
       const forms = document.querySelectorAll('form');
       forms.forEach(form => {
           form.addEventListener('submit', function(e) {
               const submitBtn = form.querySelector('button[type="submit"]');
               if (submitBtn && submitBtn.disabled) {
                   e.preventDefault();
                   return false;
               }
               if (submitBtn) {
                   submitBtn.disabled = true;
                   setTimeout(() => {
                       submitBtn.disabled = false;
                   }, 3000);
               }
           });
       });
   });
   </script>


   <?php if (isset($additionalJS)): ?>
       <script src="<?php echo BASE_URL . '/assets/js/' . $additionalJS; ?>"></script>
   <?php endif; ?>
</body>
</html>