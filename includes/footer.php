</main>

<!-- Footer -->
<footer class="footer" style="background: var(--bg-secondary); color: var(--text-secondary); padding: 2rem 0; margin-top: auto; border-top: 1px solid var(--border-color);">
    <div class="container" style="max-width: 1200px; margin: 0 auto; padding: 0 2rem;">
        <!-- Footer Content -->
        <div class="footer-content" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 2rem; margin-bottom: 2rem;">
            <!-- About Section -->
            <div class="footer-section">
                <h3 style="color: #7fb539; margin-bottom: 1rem; font-size: 1.1rem; font-weight: 600;">About Us</h3>
                <p style="line-height: 1.6; color: #6b7280; font-size: 0.9rem;">
                    Carmona Online Permit Portal streamlines the permit application process, making it faster and more convenient for residents and businesses.
                </p>
            </div>
            
            <!-- Quick Links -->
            <div class="footer-section">
                <h3 style="color: #7fb539; margin-bottom: 1rem; font-size: 1.1rem; font-weight: 600;">Quick Links</h3>
                <ul style="list-style: none; padding: 0; margin: 0;">
                    <li style="margin-bottom: 0.5rem;">
                        <a href="<?php echo BASE_URL; ?>/index.php" style="color: #6b7280; text-decoration: none; font-size: 0.9rem; transition: color 0.3s;" class="footer-link">Home</a>
                    </li>
                    <li style="margin-bottom: 0.5rem;">
                        <a href="#" style="color: #6b7280; text-decoration: none; font-size: 0.9rem; transition: color 0.3s;" class="footer-link">How It Works</a>
                    </li>
                    <li style="margin-bottom: 0.5rem;">
                        <a href="#" style="color: #6b7280; text-decoration: none; font-size: 0.9rem; transition: color 0.3s;" class="footer-link">FAQs</a>
                    </li>
                    <li style="margin-bottom: 0.5rem;">
                        <a href="#" style="color: #6b7280; text-decoration: none; font-size: 0.9rem; transition: color 0.3s;" class="footer-link">Contact Support</a>
                    </li>
                </ul>
            </div>
            
            <!-- Contact Info -->
            <div class="footer-section">
                <h3 style="color: #7fb539; margin-bottom: 1rem; font-size: 1.1rem; font-weight: 600;">Contact</h3>
                <ul style="list-style: none; padding: 0; margin: 0;">
                    <li style="margin-bottom: 0.5rem; color: #6b7280; font-size: 0.9rem;">
                        <svg style="width: 16px; height: 16px; display: inline-block; vertical-align: middle; margin-right: 0.5rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                        support@carmona.gov.ph
                    </li>
                    <li style="margin-bottom: 0.5rem; color: #6b7280; font-size: 0.9rem;">
                        <svg style="width: 16px; height: 16px; display: inline-block; vertical-align: middle; margin-right: 0.5rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                        </svg>
                        (046) 123-4567
                    </li>
                    <li style="margin-bottom: 0.5rem; color: #6b7280; font-size: 0.9rem;">
                        <svg style="width: 16px; height: 16px; display: inline-block; vertical-align: middle; margin-right: 0.5rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        Carmona, Cavite
                    </li>
                </ul>
            </div>
        </div>
        
        <!-- Footer Bottom -->
        <div class="footer-bottom" style="text-align: center; padding-top: 1.5rem; border-top: 1px solid rgba(107, 114, 128, 0.2);">
            <p style="margin: 0; color: #6b7280; font-size: 0.85rem;">
                &copy; <?php echo date('Y'); ?> Carmona Online Permit Portal. All rights reserved.
            </p>
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

// Shake animation CSS
const style = document.createElement('style');
style.textContent = `
    @keyframes shake {
        0%, 100% { transform: translateX(0); }
        25% { transform: translateX(-5px); }
        75% { transform: translateX(5px); }
    }
`;
document.head.appendChild(style);

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
                const originalText = submitBtn.innerHTML;
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span style="display: inline-block; animation: spin 1s linear infinite;">⏳</span> Processing...';
                
                setTimeout(() => {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                }, 3000);
            }
        });
    });
});

// Add spinner animation
const spinnerStyle = document.createElement('style');
spinnerStyle.textContent = `
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
`;
document.head.appendChild(spinnerStyle);
</script>

<?php if (isset($additionalJS)): ?>
    <script src="<?php echo BASE_URL . '/assets/js/' . $additionalJS; ?>"></script>
<?php endif; ?>
</body>
</html>