/**
 * Main JavaScript File
 * AI Conference Summit - Beginner Friendly Code
 */

// DOM Content Loaded
document.addEventListener('DOMContentLoaded', function() {
    initializeApp();
});

/**
 * Initialize the application
 */
function initializeApp() {
    // Add smooth scrolling
    initSmoothScrolling();
    
    // Initialize navbar
    initNavbar();
    
    // Initialize animations
    initAnimations();
    
    // Initialize forms
    initForms();
    
    // Initialize tooltips
    initTooltips();
    
    // Initialize auto-dismiss alerts
    initAlerts();
}

/**
 * Smooth scrolling for anchor links
 */
function initSmoothScrolling() {
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                e.preventDefault();
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
}

/**
 * Navbar scroll effects
 */
function initNavbar() {
    const navbar = document.querySelector('.navbar');
    if (!navbar) return;
    
    let lastScrollTop = 0;
    
    window.addEventListener('scroll', function() {
        const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
        
        // Add/remove scrolled class
        if (scrollTop > 50) {
            navbar.classList.add('scrolled');
        } else {
            navbar.classList.remove('scrolled');
        }
        
        // Hide/show navbar on scroll (optional)
        if (scrollTop > lastScrollTop && scrollTop > 100) {
            // Scrolling down
            navbar.style.transform = 'translateY(-100%)';
        } else {
            // Scrolling up
            navbar.style.transform = 'translateY(0)';
        }
        
        lastScrollTop = scrollTop;
    });
}

/**
 * Initialize scroll animations
 */
function initAnimations() {
    // Intersection Observer for fade-in animations
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animate-in');
            }
        });
    }, observerOptions);
    
    // Observe elements with animation classes
    document.querySelectorAll('.event-card, .stat-card, .dashboard-card, .feature-item').forEach(el => {
        observer.observe(el);
    });
}

/**
 * Form enhancements
 */
function initForms() {
    // Add loading states to form submissions
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', function() {
            const submitBtn = this.querySelector('button[type="submit"], input[type="submit"]');
            if (submitBtn && !submitBtn.disabled) {
                const originalText = submitBtn.innerHTML || submitBtn.value;
                
                if (submitBtn.tagName === 'BUTTON') {
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
                } else {
                    submitBtn.value = 'Processing...';
                }
                
                submitBtn.disabled = true;
                
                // Re-enable after timeout as fallback
                setTimeout(() => {
                    if (submitBtn.tagName === 'BUTTON') {
                        submitBtn.innerHTML = originalText;
                    } else {
                        submitBtn.value = originalText;
                    }
                    submitBtn.disabled = false;
                }, 10000);
            }
        });
    });
    
    // Real-time form validation
    const inputs = document.querySelectorAll('input[required], textarea[required], select[required]');
    inputs.forEach(input => {
        input.addEventListener('blur', validateField);
        input.addEventListener('input', function() {
            if (this.classList.contains('error')) {
                validateField.call(this);
            }
        });
    });
}

/**
 * Validate individual form field
 */
function validateField() {
    const field = this;
    const value = field.value.trim();
    let isValid = true;
    let message = '';
    
    // Remove existing error states
    field.classList.remove('error', 'success');
    removeFieldMessage(field);
    
    // Required field check
    if (field.hasAttribute('required') && !value) {
        isValid = false;
        message = 'This field is required';
    }
    
    // Email validation
    else if (field.type === 'email' && value) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(value)) {
            isValid = false;
            message = 'Please enter a valid email address';
        }
    }
    
    // Password validation
    else if (field.type === 'password' && value) {
        if (value.length < 6) {
            isValid = false;
            message = 'Password must be at least 6 characters';
        }
    }
    
    // Phone validation
    else if (field.type === 'tel' && value) {
        const phoneRegex = /^[\+]?[1-9][\d]{0,15}$/;
        if (!phoneRegex.test(value.replace(/[\s\-\(\)]/g, ''))) {
            isValid = false;
            message = 'Please enter a valid phone number';
        }
    }
    
    // Number validation
    else if (field.type === 'number' && value) {
        const num = parseFloat(value);
        const min = field.getAttribute('min');
        const max = field.getAttribute('max');
        
        if (min && num < parseFloat(min)) {
            isValid = false;
            message = `Value must be at least ${min}`;
        } else if (max && num > parseFloat(max)) {
            isValid = false;
            message = `Value must be no more than ${max}`;
        }
    }
    
    // Apply validation state
    if (isValid) {
        field.classList.add('success');
    } else {
        field.classList.add('error');
        showFieldMessage(field, message, 'error');
    }
    
    return isValid;
}

/**
 * Show field validation message
 */
function showFieldMessage(field, message, type) {
    const messageEl = document.createElement('div');
    messageEl.className = `field-message field-message-${type}`;
    messageEl.textContent = message;
    messageEl.style.cssText = `
        font-size: 0.8rem;
        margin-top: 5px;
        color: ${type === 'error' ? '#dc3545' : '#28a745'};
        display: flex;
        align-items: center;
        gap: 5px;
    `;
    
    const icon = document.createElement('i');
    icon.className = type === 'error' ? 'fas fa-exclamation-triangle' : 'fas fa-check-circle';
    messageEl.insertBefore(icon, messageEl.firstChild);
    
    field.parentNode.appendChild(messageEl);
}

/**
 * Remove field validation message
 */
function removeFieldMessage(field) {
    const existingMessage = field.parentNode.querySelector('.field-message');
    if (existingMessage) {
        existingMessage.remove();
    }
}

/**
 * Initialize tooltips
 */
function initTooltips() {
    const tooltips = document.querySelectorAll('[data-tooltip]');
    tooltips.forEach(element => {
        element.addEventListener('mouseenter', showTooltip);
        element.addEventListener('mouseleave', hideTooltip);
    });
}

/**
 * Show tooltip
 */
function showTooltip(e) {
    const element = e.target;
    const tooltipText = element.getAttribute('data-tooltip');
    
    const tooltip = document.createElement('div');
    tooltip.className = 'tooltip';
    tooltip.textContent = tooltipText;
    tooltip.style.cssText = `
        position: absolute;
        background: rgba(0, 0, 0, 0.9);
        color: white;
        padding: 8px 12px;
        border-radius: 6px;
        font-size: 0.8rem;
        white-space: nowrap;
        z-index: 1000;
        pointer-events: none;
        opacity: 0;
        transition: opacity 0.3s ease;
    `;
    
    document.body.appendChild(tooltip);
    
    const rect = element.getBoundingClientRect();
    tooltip.style.left = rect.left + (rect.width / 2) - (tooltip.offsetWidth / 2) + 'px';
    tooltip.style.top = rect.top - tooltip.offsetHeight - 8 + 'px';
    
    // Show tooltip
    setTimeout(() => {
        tooltip.style.opacity = '1';
    }, 10);
    
    element._tooltip = tooltip;
}

/**
 * Hide tooltip
 */
function hideTooltip(e) {
    const element = e.target;
    if (element._tooltip) {
        element._tooltip.remove();
        delete element._tooltip;
    }
}

/**
 * Initialize auto-dismiss alerts
 */
function initAlerts() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        // Add close button if not present
        if (!alert.querySelector('.alert-close')) {
            const closeBtn = document.createElement('button');
            closeBtn.className = 'alert-close';
            closeBtn.innerHTML = '&times;';
            closeBtn.style.cssText = `
                position: absolute;
                top: 10px;
                right: 15px;
                background: none;
                border: none;
                font-size: 1.5rem;
                color: inherit;
                cursor: pointer;
                opacity: 0.7;
                transition: opacity 0.3s ease;
            `;
            closeBtn.addEventListener('click', () => dismissAlert(alert));
            alert.style.position = 'relative';
            alert.appendChild(closeBtn);
        }
        
        // Auto-dismiss success alerts after 5 seconds
        if (alert.classList.contains('alert-success')) {
            setTimeout(() => {
                dismissAlert(alert);
            }, 5000);
        }
    });
}

/**
 * Dismiss alert
 */
function dismissAlert(alert) {
    alert.style.opacity = '0';
    alert.style.transform = 'translateY(-10px)';
    setTimeout(() => {
        alert.remove();
    }, 300);
}

/**
 * Show notification
 */
function showNotification(message, type = 'info', duration = 4000) {
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.innerHTML = `
        <div class="notification-content">
            <i class="fas fa-${getNotificationIcon(type)}"></i>
            <span>${message}</span>
        </div>
        <button class="notification-close">&times;</button>
    `;
    
    notification.style.cssText = `
        position: fixed;
        top: 100px;
        right: 20px;
        background: ${getNotificationColor(type)};
        color: white;
        padding: 15px 20px;
        border-radius: 10px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
        z-index: 10000;
        transform: translateX(400px);
        transition: transform 0.3s ease;
        max-width: 350px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 15px;
    `;
    
    document.body.appendChild(notification);
    
    // Animate in
    setTimeout(() => {
        notification.style.transform = 'translateX(0)';
    }, 10);
    
    // Close button
    const closeBtn = notification.querySelector('.notification-close');
    closeBtn.style.cssText = `
        background: none;
        border: none;
        color: white;
        font-size: 1.2rem;
        cursor: pointer;
        opacity: 0.8;
        padding: 0;
        width: 20px;
        height: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
    `;
    
    closeBtn.addEventListener('click', () => {
        dismissNotification(notification);
    });
    
    // Auto dismiss
    setTimeout(() => {
        dismissNotification(notification);
    }, duration);
}

/**
 * Get notification icon
 */
function getNotificationIcon(type) {
    const icons = {
        success: 'check-circle',
        error: 'exclamation-triangle',
        warning: 'exclamation-triangle',
        info: 'info-circle'
    };
    return icons[type] || 'info-circle';
}

/**
 * Get notification color
 */
function getNotificationColor(type) {
    const colors = {
        success: '#28a745',
        error: '#dc3545',
        warning: '#ffc107',
        info: '#00d4ff'
    };
    return colors[type] || '#00d4ff';
}

/**
 * Dismiss notification
 */
function dismissNotification(notification) {
    notification.style.transform = 'translateX(400px)';
    setTimeout(() => {
        notification.remove();
    }, 300);
}

/**
 * Confirm dialog
 */
function confirmDialog(message, callback) {
    const overlay = document.createElement('div');
    overlay.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.8);
        z-index: 10000;
        display: flex;
        align-items: center;
        justify-content: center;
        backdrop-filter: blur(5px);
    `;
    
    const dialog = document.createElement('div');
    dialog.style.cssText = `
        background: rgba(26, 26, 26, 0.95);
        padding: 30px;
        border-radius: 15px;
        max-width: 400px;
        width: 90%;
        text-align: center;
        backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.1);
    `;
    
    dialog.innerHTML = `
        <div style="color: #ffffff; margin-bottom: 25px; font-size: 1.1rem; line-height: 1.5;">
            ${message}
        </div>
        <div style="display: flex; gap: 15px; justify-content: center;">
            <button id="confirmYes" class="btn btn-primary">Yes, Continue</button>
            <button id="confirmNo" class="btn btn-secondary">Cancel</button>
        </div>
    `;
    
    overlay.appendChild(dialog);
    document.body.appendChild(overlay);
    
    // Event listeners
    dialog.querySelector('#confirmYes').addEventListener('click', () => {
        overlay.remove();
        if (callback) callback(true);
    });
    
    dialog.querySelector('#confirmNo').addEventListener('click', () => {
        overlay.remove();
        if (callback) callback(false);
    });
    
    // Close on overlay click
    overlay.addEventListener('click', (e) => {
        if (e.target === overlay) {
            overlay.remove();
            if (callback) callback(false);
        }
    });
}

/**
 * Format currency
 */
function formatCurrency(amount) {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
    }).format(amount);
}

/**
 * Format date
 */
function formatDate(date, options = {}) {
    const defaultOptions = {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    };
    
    return new Intl.DateTimeFormat('en-US', { ...defaultOptions, ...options }).format(new Date(date));
}

/**
 * Debounce function
 */
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

/**
 * Throttle function
 */
function throttle(func, limit) {
    let inThrottle;
    return function() {
        const args = arguments;
        const context = this;
        if (!inThrottle) {
            func.apply(context, args);
            inThrottle = true;
            setTimeout(() => inThrottle = false, limit);
        }
    }
}

/**
 * Copy to clipboard
 */
function copyToClipboard(text) {
    if (navigator.clipboard) {
        navigator.clipboard.writeText(text).then(() => {
            showNotification('Copied to clipboard!', 'success', 2000);
        });
    } else {
        // Fallback for older browsers
        const textArea = document.createElement('textarea');
        textArea.value = text;
        textArea.style.position = 'fixed';
        textArea.style.left = '-999999px';
        textArea.style.top = '-999999px';
        document.body.appendChild(textArea);
        textArea.focus();
        textArea.select();
        
        try {
            document.execCommand('copy');
            showNotification('Copied to clipboard!', 'success', 2000);
        } catch (err) {
            showNotification('Failed to copy to clipboard', 'error');
        }
        
        textArea.remove();
    }
}

/**
 * Load more content (for pagination)
 */
function loadMore(url, container, button) {
    const originalText = button.innerHTML;
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Loading...';
    button.disabled = true;
    
    fetch(url)
        .then(response => response.text())
        .then(html => {
            const tempDiv = document.createElement('div');
            tempDiv.innerHTML = html;
            
            const newItems = tempDiv.querySelectorAll('.item');
            newItems.forEach(item => {
                container.appendChild(item);
            });
            
            button.innerHTML = originalText;
            button.disabled = false;
        })
        .catch(error => {
            showNotification('Failed to load more content', 'error');
            button.innerHTML = originalText;
            button.disabled = false;
        });
}

// Global functions for external use
window.showNotification = showNotification;
window.confirmDialog = confirmDialog;
window.copyToClipboard = copyToClipboard;
window.formatCurrency = formatCurrency;
window.formatDate = formatDate;