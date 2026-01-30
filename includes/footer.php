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

<!-- Responsive Footer Styles -->
<style>
    @media (max-width: 768px) {
        .footer {
            padding: 1.5rem 0 !important;
        }
        
        .footer .container {
            padding: 0 1rem !important;
        }
        
        .footer-content {
            grid-template-columns: 1fr !important;
            gap: 1.5rem !important;
            margin-bottom: 1.5rem !important;
        }
        
        .footer-section h3 {
            font-size: 1rem !important;
            margin-bottom: 0.75rem !important;
        }
        
        .footer-section p,
        .footer-section ul li {
            font-size: 0.85rem !important;
        }
        
        .footer-bottom p {
            font-size: 0.8rem !important;
        }
    }
    
    @media (max-width: 480px) {
        .footer {
            padding: 1rem 0 !important;
        }
        
        .footer-content {
            gap: 1rem !important;
        }
    }
    
    .footer-link:hover {
        color: #7fb539 !important;
    }
    
    /* Shake animation for form validation */
    @keyframes shake {
        0%, 100% { transform: translateX(0); }
        25% { transform: translateX(-5px); }
        75% { transform: translateX(5px); }
    }
    
    /* Spinner animation for submit buttons */
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
</style>

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
    scrollBtn.setAttribute('aria-label', 'Scroll to top');
    scrollBtn.style.cssText = `
        position: fixed;
        bottom: 2rem;
        right: 2rem;
        width: 50px;
        height: 50px;
        border-radius: 50%;
        background: #7fb539;
        color: white;
        border: none;
        cursor: pointer;
        font-size: 1.5rem;
        display: none;
        z-index: 1000;
        box-shadow: 0 4px 12px rgba(127, 181, 57, 0.3);
        transition: all 0.3s ease;
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
        this.style.transform = 'scale(1.1) translateY(-3px)';
        this.style.boxShadow = '0 6px 16px rgba(127, 181, 57, 0.4)';
    });
    
    scrollBtn.addEventListener('mouseleave', function() {
        this.style.transform = 'scale(1)';
        this.style.boxShadow = '0 4px 12px rgba(127, 181, 57, 0.3)';
    });
    
    // Mobile responsiveness for scroll button
    const updateScrollBtnPosition = () => {
        if (window.innerWidth <= 768) {
            scrollBtn.style.bottom = '1.5rem';
            scrollBtn.style.right = '1.5rem';
            scrollBtn.style.width = '45px';
            scrollBtn.style.height = '45px';
            scrollBtn.style.fontSize = '1.3rem';
        } else {
            scrollBtn.style.bottom = '2rem';
            scrollBtn.style.right = '2rem';
            scrollBtn.style.width = '50px';
            scrollBtn.style.height = '50px';
            scrollBtn.style.fontSize = '1.5rem';
        }
    };
    
    updateScrollBtnPosition();
    window.addEventListener('resize', updateScrollBtnPosition);
}

// Form validation helper
function validateForm(formId) {
    const form = document.getElementById(formId);
    if (!form) return true;
    
    const inputs = form.querySelectorAll('input[required], select[required], textarea[required]');
    let isValid = true;
    
    inputs.forEach(input => {
        if (!input.value.trim()) {
            input.style.borderColor = '#ef4444';
            isValid = false;
            
            // Add shake animation
            input.style.animation = 'shake 0.3s';
            setTimeout(() => {
                input.style.animation = '';
            }, 300);
        } else {
            input.style.borderColor = '';
        }
    });
    
    return isValid;
}


</script>

<?php if (isset($additionalJS)): ?>
    <script src="<?php echo BASE_URL . '/assets/js/' . $additionalJS; ?>"></script>
<?php endif; ?>
</body>
</html>