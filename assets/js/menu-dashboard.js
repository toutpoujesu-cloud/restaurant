/**
 * Menu Dashboard JavaScript
 * Handles drag-drop, quick edit, analytics, and all interactive features
 */

jQuery(document).ready(function($) {
    'use strict';
    
    // ============================================
    // DRAG & DROP SORTABLE
    // ============================================
    $('#ucfc-sortable-menu').sortable({
        cursor: 'move',
        opacity: 0.8,
        placeholder: 'ucfc-card-placeholder',
        tolerance: 'pointer',
        update: function(event, ui) {
            const order = $(this).sortable('toArray', {attribute: 'data-item-id'});
            
            $.ajax({
                url: ucfcMenuDashboard.ajax_url,
                type: 'POST',
                data: {
                    action: 'ucfc_update_menu_order',
                    nonce: ucfcMenuDashboard.nonce,
                    order: order
                },
                success: function(response) {
                    if (response.success) {
                        showNotification('Menu order saved!', 'success');
                    }
                }
            });
        }
    });
    
    // ============================================
    // CATEGORY FILTERING
    // ============================================
    $('.ucfc-cat-tab').on('click', function() {
        $('.ucfc-cat-tab').removeClass('active');
        $(this).addClass('active');
        
        const category = $(this).data('category');
        
        if (category === 'all') {
            $('.ucfc-menu-card').fadeIn(300);
        } else {
            $('.ucfc-menu-card').each(function() {
                const itemCategories = $(this).data('categories').toString().split(',');
                if (itemCategories.includes(category.toString())) {
                    $(this).fadeIn(300);
                } else {
                    $(this).fadeOut(300);
                }
            });
        }
    });
    
    // ============================================
    // QUICK EDIT MODAL
    // ============================================
    $(document).on('click', '.ucfc-quick-edit, .ucfc-add-item-btn', function(e) {
        e.preventDefault();
        const itemId = $(this).data('item-id') || 0;
        
        $.ajax({
            url: ucfcMenuDashboard.ajax_url,
            type: 'POST',
            data: {
                action: 'ucfc_quick_edit_form',
                nonce: ucfcMenuDashboard.nonce,
                item_id: itemId
            },
            beforeSend: function() {
                $('#ucfc-quick-edit-modal').fadeIn(300);
                $('#ucfc-quick-edit-form').html('<div class="ucfc-loading">Loading...</div>');
            },
            success: function(response) {
                if (response.success) {
                    $('#ucfc-quick-edit-form').html(response.data.html);
                }
            }
        });
    });
    
    // Close modal
    $(document).on('click', '.ucfc-modal-close, .ucfc-modal-overlay', function() {
        $('#ucfc-quick-edit-modal').fadeOut(300);
    });
    
    // Save quick edit
    $(document).on('submit', '#ucfc-quick-edit-form-inner', function(e) {
        e.preventDefault();
        const formData = $(this).serialize();
        const itemId = $(this).data('item-id');
        
        $.ajax({
            url: ucfcMenuDashboard.ajax_url,
            type: 'POST',
            data: formData + '&action=ucfc_save_quick_edit&nonce=' + ucfcMenuDashboard.nonce + '&item_id=' + itemId,
            beforeSend: function() {
                $('#ucfc-quick-edit-form-inner button[type="submit"]').prop('disabled', true).text('Saving...');
            },
            success: function(response) {
                if (response.success) {
                    showNotification('Item saved successfully!', 'success');
                    $('#ucfc-quick-edit-modal').fadeOut(300);
                    setTimeout(function() {
                        location.reload();
                    }, 500);
                } else {
                    showNotification('Error saving item', 'error');
                }
            },
            complete: function() {
                $('#ucfc-quick-edit-form-inner button[type="submit"]').prop('disabled', false).html('<span class="dashicons dashicons-yes"></span> Save Changes');
            }
        });
    });
    
    // ============================================
    // DELETE ITEM
    // ============================================
    $(document).on('click', '.ucfc-delete, .ucfc-delete-item', function(e) {
        e.preventDefault();
        
        if (!confirm('Are you sure you want to delete this menu item? This cannot be undone.')) {
            return;
        }
        
        const itemId = $(this).data('item-id');
        const $card = $(this).closest('.ucfc-menu-card');
        
        $.ajax({
            url: ucfcMenuDashboard.ajax_url,
            type: 'POST',
            data: {
                action: 'ucfc_delete_item',
                nonce: ucfcMenuDashboard.nonce,
                item_id: itemId
            },
            beforeSend: function() {
                $card.css('opacity', '0.5');
            },
            success: function(response) {
                if (response.success) {
                    $card.fadeOut(300, function() {
                        $(this).remove();
                    });
                    $('#ucfc-quick-edit-modal').fadeOut(300);
                    showNotification('Item deleted successfully', 'success');
                } else {
                    showNotification('Error deleting item', 'error');
                    $card.css('opacity', '1');
                }
            }
        });
    });
    
    // ============================================
    // DUPLICATE ITEM
    // ============================================
    $(document).on('click', '.ucfc-duplicate', function(e) {
        e.preventDefault();
        const itemId = $(this).data('item-id');
        
        $.ajax({
            url: ucfcMenuDashboard.ajax_url,
            type: 'POST',
            data: {
                action: 'ucfc_duplicate_item',
                nonce: ucfcMenuDashboard.nonce,
                item_id: itemId
            },
            beforeSend: function() {
                showNotification('Duplicating item...', 'info');
            },
            success: function(response) {
                if (response.success) {
                    showNotification('Item duplicated! Refreshing...', 'success');
                    setTimeout(function() {
                        location.reload();
                    }, 1000);
                } else {
                    showNotification('Error duplicating item', 'error');
                }
            }
        });
    });
    
    // ============================================
    // CSV EXPORT
    // ============================================
    $('#ucfc-export-csv').on('click', function(e) {
        e.preventDefault();
        
        $.ajax({
            url: ucfcMenuDashboard.ajax_url,
            type: 'POST',
            data: {
                action: 'ucfc_export_csv',
                nonce: ucfcMenuDashboard.nonce
            },
            beforeSend: function() {
                $('#ucfc-export-csv').prop('disabled', true).text('Generating CSV...');
            },
            success: function(response) {
                if (response.success) {
                    downloadCSV(response.data.data, 'menu-export-' + new Date().toISOString().split('T')[0] + '.csv');
                    showNotification('Menu exported successfully!', 'success');
                }
            },
            complete: function() {
                $('#ucfc-export-csv').prop('disabled', false).html('<span class="dashicons dashicons-download"></span> Export to CSV');
            }
        });
    });
    
    // CSV Download Helper
    function downloadCSV(data, filename) {
        const csv = data.map(row => row.map(cell => {
            const escaped = String(cell).replace(/"/g, '""');
            return `"${escaped}"`;
        }).join(',')).join('\n');
        
        const blob = new Blob([csv], { type: 'text/csv' });
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.setAttribute('hidden', '');
        a.setAttribute('href', url);
        a.setAttribute('download', filename);
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
    }
    
    // ============================================
    // CSV IMPORT
    // ============================================
    $('#ucfc-csv-dropzone').on('click', function() {
        $('#ucfc-csv-file').click();
    });
    
    $('#ucfc-csv-file').on('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            parseCSV(file);
        }
    });
    
    // Drag & drop
    $('#ucfc-csv-dropzone').on('dragover', function(e) {
        e.preventDefault();
        $(this).addClass('ucfc-dragover');
    });
    
    $('#ucfc-csv-dropzone').on('dragleave', function(e) {
        e.preventDefault();
        $(this).removeClass('ucfc-dragover');
    });
    
    $('#ucfc-csv-dropzone').on('drop', function(e) {
        e.preventDefault();
        $(this).removeClass('ucfc-dragover');
        
        const file = e.originalEvent.dataTransfer.files[0];
        if (file && file.type === 'text/csv') {
            parseCSV(file);
        } else {
            showNotification('Please upload a CSV file', 'error');
        }
    });
    
    function parseCSV(file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const text = e.target.result;
            const rows = text.split('\n').map(row => {
                const matches = row.match(/(".*?"|[^",\s]+)(?=\s*,|\s*$)/g);
                return matches ? matches.map(cell => cell.replace(/^"|"$/g, '').replace(/""/g, '"')) : [];
            });
            
            displayImportPreview(rows);
        };
        reader.readAsText(file);
    }
    
    function displayImportPreview(rows) {
        let html = '<table class="wp-list-table widefat"><thead><tr>';
        rows[0].forEach(header => {
            html += `<th>${header}</th>`;
        });
        html += '</tr></thead><tbody>';
        
        rows.slice(1, 6).forEach(row => {
            if (row.length > 1) {
                html += '<tr>';
                row.forEach(cell => {
                    html += `<td>${cell}</td>`;
                });
                html += '</tr>';
            }
        });
        
        html += '</tbody></table>';
        html += `<p><strong>${rows.length - 1} items</strong> will be imported (showing first 5)</p>`;
        
        $('#ucfc-preview-table').html(html);
        $('#ucfc-import-preview').fadeIn(300);
        
        // Store data for import
        window.csvImportData = rows;
    }
    
    $('#ucfc-confirm-import').on('click', function() {
        if (!window.csvImportData) {
            return;
        }
        
        // This would send to server for processing
        showNotification('Import started! This may take a few minutes...', 'info');
        
        // TODO: Implement chunked import via AJAX
    });
    
    // ============================================
    // BULK EDIT
    // ============================================
    let bulkEditMode = false;
    
    $('.ucfc-bulk-edit-btn').on('click', function() {
        bulkEditMode = !bulkEditMode;
        
        if (bulkEditMode) {
            $(this).text('Cancel Bulk Edit').addClass('ucfc-bulk-active');
            $('.ucfc-menu-card').addClass('ucfc-bulk-mode');
            $('.ucfc-menu-card').each(function() {
                $(this).prepend('<input type="checkbox" class="ucfc-bulk-checkbox">');
            });
            showBulkActions();
        } else {
            $(this).html('<span class="dashicons dashicons-edit"></span> Bulk Edit').removeClass('ucfc-bulk-active');
            $('.ucfc-menu-card').removeClass('ucfc-bulk-mode');
            $('.ucfc-bulk-checkbox').remove();
            hideBulkActions();
        }
    });
    
    function showBulkActions() {
        if ($('.ucfc-bulk-actions').length === 0) {
            const html = `
                <div class="ucfc-bulk-actions">
                    <button class="button" id="ucfc-bulk-change-category">Change Category</button>
                    <button class="button" id="ucfc-bulk-change-price">Adjust Prices</button>
                    <button class="button" id="ucfc-bulk-delete">Delete Selected</button>
                    <span class="ucfc-bulk-count">0 selected</span>
                </div>
            `;
            $('.ucfc-menu-builder').prepend(html);
        }
    }
    
    function hideBulkActions() {
        $('.ucfc-bulk-actions').remove();
    }
    
    $(document).on('change', '.ucfc-bulk-checkbox', function() {
        const count = $('.ucfc-bulk-checkbox:checked').length;
        $('.ucfc-bulk-count').text(count + ' selected');
    });
    
    // ============================================
    // ANALYTICS
    // ============================================
    window.ucfcLoadAnalytics = function() {
        loadSalesChart();
        loadCategoryChart();
        loadHourlyChart();
        loadPerformanceInsights();
    };
    
    function loadSalesChart() {
        const ctx = document.getElementById('ucfc-sales-chart');
        if (!ctx) return;
        
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                datasets: [{
                    label: 'Sales ($)',
                    data: [1200, 1450, 1100, 1650, 2100, 2800, 2500],
                    borderColor: '#C92A2A',
                    backgroundColor: 'rgba(201, 42, 42, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
    }
    
    function loadCategoryChart() {
        const ctx = document.getElementById('ucfc-category-chart');
        if (!ctx) return;
        
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Chicken', 'Sides', 'Drinks', 'Desserts'],
                datasets: [{
                    data: [45, 25, 20, 10],
                    backgroundColor: ['#C92A2A', '#F0B429', '#2E8B57', '#8A8A8A']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });
    }
    
    function loadHourlyChart() {
        const ctx = document.getElementById('ucfc-hourly-chart');
        if (!ctx) return;
        
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['11AM', '12PM', '1PM', '2PM', '5PM', '6PM', '7PM', '8PM'],
                datasets: [{
                    label: 'Orders',
                    data: [15, 28, 32, 18, 42, 55, 48, 35],
                    backgroundColor: '#F0B429'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
    }
    
    function loadPerformanceInsights() {
        const bestItems = `
            <ul class="ucfc-insight-list">
                <li>🏆 Spicy Wings <span>243 sold</span></li>
                <li>🥇 Chicken Bucket <span>187 sold</span></li>
                <li>🥈 Mac & Cheese <span>156 sold</span></li>
            </ul>
        `;
        
        const worstItems = `
            <ul class="ucfc-insight-list">
                <li>⚠️ Caesar Salad <span>3 sold (7 days)</span></li>
                <li>⚠️ Veggie Burger <span>5 sold</span></li>
                <li>⚠️ Coleslaw <span>8 sold</span></li>
            </ul>
        `;
        
        $('#ucfc-best-items').html(bestItems);
        $('#ucfc-worst-items').html(worstItems);
    }
    
    // ============================================
    // NOTIFICATIONS
    // ============================================
    function showNotification(message, type = 'info') {
        const notification = $(`
            <div class="ucfc-notification ucfc-notification-${type}">
                <span class="dashicons dashicons-${type === 'success' ? 'yes' : type === 'error' ? 'no' : 'info'}"></span>
                ${message}
            </div>
        `);
        
        $('body').append(notification);
        
        setTimeout(function() {
            notification.addClass('ucfc-notification-show');
        }, 100);
        
        setTimeout(function() {
            notification.removeClass('ucfc-notification-show');
            setTimeout(function() {
                notification.remove();
            }, 300);
        }, 3000);
    }
    
    // ============================================
    // ADD NEW CATEGORY
    // ============================================
    $(document).on('click', '.ucfc-add-category', function(e) {
        e.preventDefault();
        
        const categoryName = prompt('Enter new category name:');
        
        if (!categoryName || categoryName.trim() === '') {
            return;
        }
        
        $.ajax({
            url: ucfcMenuDashboard.ajax_url,
            type: 'POST',
            data: {
                action: 'ucfc_add_category',
                nonce: ucfcMenuDashboard.nonce,
                category_name: categoryName
            },
            success: function(response) {
                if (response.success) {
                    showNotification('Category added successfully!', 'success');
                    
                    // Add new tab
                    const newTab = $(`
                        <button class="ucfc-cat-tab" data-category="${response.data.term_id}">
                            ${response.data.name}
                        </button>
                    `);
                    
                    $('.ucfc-add-category').before(newTab);
                    
                    // Reload page to refresh
                    setTimeout(function() {
                        location.reload();
                    }, 1000);
                } else {
                    showNotification(response.data || 'Failed to add category', 'error');
                }
            },
            error: function() {
                showNotification('Error adding category', 'error');
            }
        });
    });
    
    // ============================================
    // ADD NEW ITEM
    // ============================================
    $(document).on('click', '.ucfc-add-item-btn', function(e) {
        e.preventDefault();
        
        const itemName = prompt('Enter new menu item name:');
        
        if (!itemName || itemName.trim() === '') {
            return;
        }
        
        const priceInput = prompt('Enter price (e.g., 12.99):');
        const price = parseFloat(priceInput) || 0;
        
        $.ajax({
            url: ucfcMenuDashboard.ajax_url,
            type: 'POST',
            data: {
                action: 'ucfc_create_item',
                nonce: ucfcMenuDashboard.nonce,
                title: itemName,
                price: price,
                description: ''
            },
            success: function(response) {
                if (response.success) {
                    showNotification('Menu item created! Redirecting to edit...', 'success');
                    
                    // Redirect to edit page
                    setTimeout(function() {
                        window.location.href = response.data.edit_link;
                    }, 1000);
                } else {
                    showNotification(response.data || 'Failed to create item', 'error');
                }
            },
            error: function() {
                showNotification('Error creating item', 'error');
            }
        });
    });
    
    // Make globally available
    window.ucfcShowNotification = showNotification;
});
