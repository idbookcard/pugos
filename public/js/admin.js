/* public/js/admin.js */
document.addEventListener('DOMContentLoaded', function() {
    "use strict";

    // Toggle the side navigation
    const sidebarToggle = document.querySelector('#sidebarToggle');
    const sidebarToggleTop = document.querySelector('#sidebarToggleTop');
    const sidebar = document.querySelector('.sidebar');
    
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function(e) {
            e.preventDefault();
            document.querySelector('body').classList.toggle('sidebar-toggled');
            sidebar.classList.toggle('toggled');
            
            if (sidebar.classList.contains('toggled')) {
                document.querySelectorAll('.sidebar .collapse').forEach(function(el) {
                    el.classList.remove('show');
                });
            }
        });
    }
    
    if (sidebarToggleTop) {
        sidebarToggleTop.addEventListener('click', function(e) {
            e.preventDefault();
            document.querySelector('body').classList.toggle('sidebar-toggled');
            sidebar.classList.toggle('toggled');
        });
    }
    
    // Close any open menu accordions when window is resized
    window.addEventListener('resize', function() {
        const windowWidth = window.innerWidth;
        
        if (windowWidth < 768) {
            document.querySelectorAll('.sidebar .collapse').forEach(function(el) {
                el.classList.remove('show');
            });
        }
        
        // Toggle the side navigation when window is < 480px
        if (windowWidth < 480 && !sidebar.classList.contains('toggled')) {
            document.querySelector('body').classList.add('sidebar-toggled');
            sidebar.classList.add('toggled');
            document.querySelectorAll('.sidebar .collapse').forEach(function(el) {
                el.classList.remove('show');
            });
        }
    });
    
    // Smooth scrolling
    document.querySelectorAll('a.scroll-to-top').forEach(function(el) {
        el.addEventListener('click', function(e) {
            e.preventDefault();
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    });
    
    // Scroll to top button appear
    window.addEventListener('scroll', function() {
        const scrollToTop = document.querySelector('.scroll-to-top');
        
        if (scrollToTop) {
            if (window.pageYOffset > 100) {
                scrollToTop.style.display = 'block';
            } else {
                scrollToTop.style.display = 'none';
            }
        }
    });
    
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Initialize popovers
    var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });
    
    // Prevent the dropdown menu from closing when clicking inside it
    document.querySelectorAll('.dropdown-menu.keep-open').forEach(function(el) {
        el.addEventListener('click', function(e) {
            e.stopPropagation();
        });
    });
    
    // Initialize datepickers if available
    const datepickers = document.querySelectorAll('.datepicker');
    if (datepickers.length > 0 && typeof flatpickr !== 'undefined') {
        datepickers.forEach(function(el) {
            flatpickr(el, {
                dateFormat: "Y-m-d",
                allowInput: true
            });
        });
    }
    
    // Preview image upload
    document.querySelectorAll('.image-upload').forEach(function(uploadField) {
        const preview = uploadField.querySelector('.image-preview');
        const input = uploadField.querySelector('input[type="file"]');
        
        if (input && preview) {
            input.addEventListener('change', function() {
                if (this.files && this.files[0]) {
                    const reader = new FileReader();
                    
                    reader.onload = function(e) {
                        preview.style.backgroundImage = `url('${e.target.result}')`;
                        preview.classList.add('has-image');
                    }
                    
                    reader.readAsDataURL(this.files[0]);
                }
            });
        }
    });
    
    // Toggle password visibility
    document.querySelectorAll('.password-toggle').forEach(function(toggle) {
        toggle.addEventListener('click', function() {
            const input = document.querySelector(this.getAttribute('data-toggle'));
            if (input) {
                if (input.type === 'password') {
                    input.type = 'text';
                    this.innerHTML = '<i class="fas fa-eye-slash"></i>';
                } else {
                    input.type = 'password';
                    this.innerHTML = '<i class="fas fa-eye"></i>';
                }
            }
        });
    });
    
    // Handle bulk actions in tables
    const bulkActionCheckboxes = document.querySelectorAll('.bulk-select-checkbox');
    const bulkSelectAll = document.querySelector('.bulk-select-all');
    
    if (bulkSelectAll && bulkActionCheckboxes.length > 0) {
        bulkSelectAll.addEventListener('change', function() {
            const isChecked = this.checked;
            bulkActionCheckboxes.forEach(function(checkbox) {
                checkbox.checked = isChecked;
            });
            
            document.querySelector('.bulk-actions').classList.toggle('d-none', !isChecked);
        });
        
        bulkActionCheckboxes.forEach(function(checkbox) {
            checkbox.addEventListener('change', function() {
                const anyChecked = Array.from(bulkActionCheckboxes).some(c => c.checked);
                document.querySelector('.bulk-actions').classList.toggle('d-none', !anyChecked);
                
                const allChecked = Array.from(bulkActionCheckboxes).every(c => c.checked);
                if (bulkSelectAll) {
                    bulkSelectAll.checked = allChecked;
                }
            });
        });
    }
});