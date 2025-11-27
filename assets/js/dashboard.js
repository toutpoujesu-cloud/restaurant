/**
 * Modern Dashboard JavaScript
 * Handles real-time updates and interactions
 */

(function($) {
    'use strict';
    
    const Dashboard = {
        refreshInterval: 60000, // 1 minute
        
        init: function() {
            this.bindEvents();
            this.startAutoRefresh();
        },
        
        bindEvents: function() {
            // Refresh button click
            $(document).on('click', '.ucfc-refresh-btn', function(e) {
                e.preventDefault();
                Dashboard.loadStats();
            });
            
            // Chart filter buttons
            $(document).on('click', '.ucfc-filter-btn', function(e) {
                e.preventDefault();
                $('.ucfc-filter-btn').removeClass('active');
                $(this).addClass('active');
                
                const period = $(this).text();
                Dashboard.updateChart(period);
            });
        },
        
        loadStats: function() {
            $.ajax({
                url: ucfcDashboard.ajax_url,
                type: 'POST',
                data: {
                    action: 'ucfc_get_dashboard_stats',
                    nonce: ucfcDashboard.nonce
                },
                beforeSend: function() {
                    // Show loading indicator
                    $('.ucfc-stat-value').addClass('loading');
                },
                success: function(response) {
                    if (response.success) {
                        Dashboard.updateStats(response.data);
                    }
                },
                complete: function() {
                    $('.ucfc-stat-value').removeClass('loading');
                }
            });
        },
        
        updateStats: function(data) {
            // Update stat cards with animation
            $.each(data, function(key, value) {
                const $element = $('[data-stat="' + key + '"]');
                if ($element.length) {
                    Dashboard.animateValue($element, value);
                }
            });
        },
        
        animateValue: function($element, newValue) {
            const currentValue = parseFloat($element.text().replace(/[^0-9.-]+/g, ''));
            const prefix = $element.text().replace(/[0-9.-]+/g, '').trim();
            
            $({count: currentValue}).animate({count: newValue}, {
                duration: 1000,
                easing: 'swing',
                step: function() {
                    $element.text(prefix + Math.floor(this.count));
                },
                complete: function() {
                    $element.text(prefix + newValue);
                }
            });
        },
        
        updateChart: function(period) {
            // Load new chart data based on period
            $.ajax({
                url: ucfcDashboard.ajax_url,
                type: 'POST',
                data: {
                    action: 'ucfc_get_chart_data',
                    period: period,
                    nonce: ucfcDashboard.nonce
                },
                success: function(response) {
                    if (response.success && window.ucfcSalesChart) {
                        // Update chart data
                        window.ucfcSalesChart.data.labels = response.data.labels;
                        window.ucfcSalesChart.data.datasets[0].data = response.data.values;
                        window.ucfcSalesChart.update();
                    }
                }
            });
        },
        
        startAutoRefresh: function() {
            // Auto-refresh stats every minute
            setInterval(function() {
                Dashboard.loadStats();
            }, this.refreshInterval);
        },
        
        showNotification: function(message, type) {
            const $notification = $('<div>', {
                class: 'ucfc-notification ucfc-notification-' + type,
                html: '<span class="ucfc-notification-icon">' + 
                      (type === 'success' ? '✓' : 'ℹ') + 
                      '</span><span class="ucfc-notification-message">' + message + '</span>'
            });
            
            $('body').append($notification);
            
            setTimeout(function() {
                $notification.addClass('show');
            }, 100);
            
            setTimeout(function() {
                $notification.removeClass('show');
                setTimeout(function() {
                    $notification.remove();
                }, 300);
            }, 3000);
        }
    };
    
    // Initialize on document ready
    $(document).ready(function() {
        if ($('.ucfc-modern-dashboard').length) {
            Dashboard.init();
        }
    });
    
})(jQuery);
