<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // Show loading screen
    function showLoading() {
        const loadingScreen = document.getElementById('loadingScreen');
        if (loadingScreen) {
            loadingScreen.classList.add('show');
        }
    }
    
    // Hide loading screen
    function hideLoading() {
        const loadingScreen = document.getElementById('loadingScreen');
        if (loadingScreen) {
            loadingScreen.classList.remove('show');
        }
    }

    // Page transition animation
    document.addEventListener('DOMContentLoaded', function() {
        // Hide loading screen when page is loaded
        setTimeout(hideLoading, 100);

        // Show loading on page navigation (excluding same page and external links)
        document.querySelectorAll('a:not([href^="#"]):not([href^="javascript:"]):not([target="_blank"])').forEach(link => {
            link.addEventListener('click', function(e) {
                // Don't show loading for dropdown items or same page links
                if (!this.closest('.dropdown-menu-custom') && this.href !== window.location.href) {
                    showLoading();
                }
            });
        });

        // Show loading on form submission
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', function(e) {
                // Only show loading if form is valid
                if (this.checkValidity()) {
                    showLoading();
                }
            });
        });

        // Auto hide alerts with smooth animation
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(function(alert) {
            setTimeout(function() {
                alert.style.transition = 'all 0.3s ease';
                alert.style.opacity = '0';
                alert.style.transform = 'translateY(-10px)';
                setTimeout(function() {
                    alert.remove();
                }, 300);
            }, 5000);
        });

        // Add loading states to buttons
        const buttons = document.querySelectorAll('button[type="submit"], .btn-submit');
        buttons.forEach(button => {
            button.addEventListener('click', function() {
                if (this.form && this.form.checkValidity()) {
                    this.disabled = true;
                    const originalText = this.innerHTML;
                    this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
                    
                    // Re-enable button after 5 seconds as fallback
                    setTimeout(() => {
                        this.disabled = false;
                        this.innerHTML = originalText;
                    }, 5000);
                }
            });
        });

        // Enhanced confirm delete actions
        const deleteButtons = document.querySelectorAll('.btn-delete, [onclick*="delete"], [href*="delete"]');
        deleteButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                
                // Create custom confirmation dialog
                const confirmDialog = document.createElement('div');
                confirmDialog.style.cssText = `
                    position: fixed;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    background: rgba(0,0,0,0.7);
                    display: flex;
                    justify-content: center;
                    align-items: center;
                    z-index: 10000;
                    backdrop-filter: blur(5px);
                `;
                
                confirmDialog.innerHTML = `
                    <div style="
                        background: white;
                        padding: 30px;
                        border-radius: 15px;
                        text-align: center;
                        max-width: 400px;
                        box-shadow: 0 20px 60px rgba(0,0,0,0.3);
                    ">
                        <div style="
                            width: 60px;
                            height: 60px;
                            background: #fee2e2;
                            border-radius: 50%;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            margin: 0 auto 20px;
                        ">
                            <i class="fas fa-exclamation-triangle" style="color: #ef4444; font-size: 24px;"></i>
                        </div>
                        <h3 style="margin-bottom: 15px; color: #333;">Konfirmasi Hapus</h3>
                        <p style="margin-bottom: 25px; color: #666;">Apakah Anda yakin ingin menghapus item ini? Tindakan ini tidak dapat dibatalkan.</p>
                        <div style="display: flex; gap: 10px; justify-content: center;">
                            <button id="cancelDelete" style="
                                padding: 10px 20px;
                                border: 2px solid #ddd;
                                background: white;
                                color: #666;
                                border-radius: 8px;
                                cursor: pointer;
                                font-weight: 500;
                            ">Batal</button>
                            <button id="confirmDelete" style="
                                padding: 10px 20px;
                                border: none;
                                background: #ef4444;
                                color: white;
                                border-radius: 8px;
                                cursor: pointer;
                                font-weight: 500;
                            ">Ya, Hapus</button>
                        </div>
                    </div>
                `;
                
                document.body.appendChild(confirmDialog);
                
                // Handle cancel
                confirmDialog.querySelector('#cancelDelete').addEventListener('click', () => {
                    document.body.removeChild(confirmDialog);
                });
                
                // Handle confirm
                confirmDialog.querySelector('#confirmDelete').addEventListener('click', () => {
                    document.body.removeChild(confirmDialog);
                    showLoading();
                    
                    // Proceed with original action
                    if (this.href) {
                        window.location.href = this.href;
                    } else if (this.onclick) {
                        this.onclick();
                    }
                });
                
                // Close on backdrop click
                confirmDialog.addEventListener('click', (e) => {
                    if (e.target === confirmDialog) {
                        document.body.removeChild(confirmDialog);
                    }
                });
            });
        });
    });

    // Enhanced form validation
    function validateForm(form) {
        const inputs = form.querySelectorAll('input[required], select[required], textarea[required]');
        let isValid = true;

        inputs.forEach(input => {
            if (!input.value.trim()) {
                input.style.borderColor = '#ef4444';
                input.style.boxShadow = '0 0 0 3px rgba(239, 68, 68, 0.1)';
                isValid = false;
                
                // Add error message if not exists
                if (!input.nextElementSibling || !input.nextElementSibling.classList.contains('error-message')) {
                    const errorMsg = document.createElement('div');
                    errorMsg.className = 'error-message';
                    errorMsg.style.cssText = 'color: #ef4444; font-size: 12px; margin-top: 5px;';
                    errorMsg.textContent = 'Field ini wajib diisi';
                    input.parentNode.insertBefore(errorMsg, input.nextSibling);
                }
            } else {
                input.style.borderColor = '#e2e8f0';
                input.style.boxShadow = '';
                
                // Remove error message
                const errorMsg = input.nextElementSibling;
                if (errorMsg && errorMsg.classList.contains('error-message')) {
                    errorMsg.remove();
                }
            }
        });

        return isValid;
    }

    // Add smooth scrolling to anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });

    // Page visibility API to hide loading when page becomes visible
    document.addEventListener('visibilitychange', function() {
        if (!document.hidden) {
            setTimeout(hideLoading, 100);
        }
    });

    // Hide loading on window load
    window.addEventListener('load', function() {
        setTimeout(hideLoading, 200);
    });

    // Show notification function
    function showNotification(message, type = 'success') {
        const notification = document.createElement('div');
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: ${type === 'success' ? '#10b981' : '#ef4444'};
            color: white;
            padding: 15px 20px;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            z-index: 10000;
            transform: translateX(100%);
            transition: transform 0.3s ease;
            max-width: 300px;
        `;
        notification.innerHTML = `
            <div style="display: flex; align-items: center; gap: 10px;">
                <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
                <span>${message}</span>
            </div>
        `;
        
        document.body.appendChild(notification);
        
        // Show notification
        setTimeout(() => {
            notification.style.transform = 'translateX(0)';
        }, 100);
        
        // Hide notification after 3 seconds
        setTimeout(() => {
            notification.style.transform = 'translateX(100%)';
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.parentNode.removeChild(notification);
                }
            }, 300);
        }, 3000);
    }

    // Make showNotification globally available
    window.showNotification = showNotification;
</script>
</body>
</html>
