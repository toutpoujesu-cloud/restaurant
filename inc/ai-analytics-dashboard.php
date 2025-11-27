<?php
/**
 * AI Analytics Dashboard
 * 
 * Comprehensive analytics dashboard for AI conversations
 * 
 * Features:
 * - Real-time metrics
 * - Conversation trends
 * - Popular questions
 * - Function usage stats
 * - Provider performance comparison
 * - Export capabilities
 * 
 * @package Uncle_Chans_Chicken
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Add Analytics submenu
 */
add_action('admin_menu', 'ucfc_add_analytics_menu', 99);
function ucfc_add_analytics_menu() {
    add_submenu_page(
        'restaurant-settings',
        'AI Analytics',
        '📊 AI Analytics',
        'manage_options',
        'ucfc-ai-analytics',
        'ucfc_ai_analytics_page'
    );
}

/**
 * Analytics page content
 */
function ucfc_ai_analytics_page() {
    global $ucfc_conversation_logger;
    
    // Get date range from query params
    $date_from = isset($_GET['date_from']) ? sanitize_text_field($_GET['date_from']) : date('Y-m-d', strtotime('-30 days'));
    $date_to = isset($_GET['date_to']) ? sanitize_text_field($_GET['date_to']) : date('Y-m-d');
    
    // Get analytics data
    $analytics = $ucfc_conversation_logger->get_analytics($date_from, $date_to);
    $trends = $ucfc_conversation_logger->get_trends(30);
    $recent_conversations = $ucfc_conversation_logger->get_recent_conversations(20);
    
    ?>
    <div class="wrap ucfc-analytics-dashboard">
        <h1>🤖 AI Analytics Dashboard</h1>
        
        <!-- Date Range Filter -->
        <div class="analytics-filters">
            <form method="get" action="">
                <input type="hidden" name="page" value="ucfc-ai-analytics">
                <label>From: <input type="date" name="date_from" value="<?php echo esc_attr($date_from); ?>"></label>
                <label>To: <input type="date" name="date_to" value="<?php echo esc_attr($date_to); ?>"></label>
                <button type="submit" class="button">Apply Filter</button>
                <a href="?page=ucfc-ai-analytics&export=csv" class="button button-secondary">📥 Export CSV</a>
            </form>
        </div>
        
        <!-- Key Metrics -->
        <div class="analytics-metrics">
            <div class="metric-card">
                <div class="metric-icon">💬</div>
                <div class="metric-content">
                    <h3><?php echo number_format($analytics['total_conversations']); ?></h3>
                    <p>Total Conversations</p>
                </div>
            </div>
            
            <div class="metric-card">
                <div class="metric-icon">👥</div>
                <div class="metric-content">
                    <h3><?php echo number_format($analytics['unique_sessions']); ?></h3>
                    <p>Unique Sessions</p>
                </div>
            </div>
            
            <div class="metric-card">
                <div class="metric-icon">⚡</div>
                <div class="metric-content">
                    <h3><?php echo $analytics['avg_response_time']; ?>s</h3>
                    <p>Avg Response Time</p>
                </div>
            </div>
            
            <div class="metric-card">
                <div class="metric-icon">🎯</div>
                <div class="metric-content">
                    <h3><?php echo number_format($analytics['total_tokens']); ?></h3>
                    <p>Total Tokens Used</p>
                </div>
            </div>
            
            <div class="metric-card">
                <div class="metric-icon">⭐</div>
                <div class="metric-content">
                    <h3><?php echo $analytics['avg_satisfaction'] ? $analytics['avg_satisfaction'] . '/5' : 'N/A'; ?></h3>
                    <p>Avg Satisfaction</p>
                </div>
            </div>
            
            <div class="metric-card">
                <div class="metric-icon">💰</div>
                <div class="metric-content">
                    <h3>$<?php echo number_format($analytics['total_tokens'] * 0.0001, 2); ?></h3>
                    <p>Est. API Costs</p>
                </div>
            </div>
        </div>
        
        <!-- Charts Section -->
        <div class="analytics-charts">
            <div class="chart-container">
                <h2>📈 Conversation Trends (Last 30 Days)</h2>
                <div class="chart-wrapper">
                    <canvas id="trendsChart"></canvas>
                </div>
            </div>
            
            <div class="chart-container">
                <h2>🤖 Provider Performance</h2>
                <div class="provider-stats">
                    <?php foreach ($analytics['provider_stats'] as $stat): ?>
                        <div class="provider-stat-item">
                            <strong><?php echo esc_html($stat->ai_provider); ?>:</strong>
                            <span><?php echo number_format($stat->count); ?> conversations</span>
                            <div class="stat-bar" style="width: <?php echo ($stat->count / $analytics['total_conversations']) * 100; ?>%"></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        
        <!-- Popular Questions -->
        <div class="analytics-section">
            <h2>🔥 Most Common Questions</h2>
            <table class="widefat">
                <thead>
                    <tr>
                        <th>Rank</th>
                        <th>Question</th>
                        <th>Count</th>
                        <th>%</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $rank = 1;
                    foreach ($analytics['common_questions'] as $question): 
                        $percentage = ($question['count'] / $analytics['total_conversations']) * 100;
                    ?>
                        <tr>
                            <td><?php echo $rank++; ?></td>
                            <td><?php echo esc_html(substr($question['user_message'], 0, 100)); ?><?php echo strlen($question['user_message']) > 100 ? '...' : ''; ?></td>
                            <td><?php echo number_format($question['count']); ?></td>
                            <td><?php echo number_format($percentage, 1); ?>%</td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Function Usage -->
        <?php if (!empty($analytics['function_usage'])): ?>
        <div class="analytics-section">
            <h2>⚙️ Function Calling Usage</h2>
            <div class="function-usage-grid">
                <?php foreach ($analytics['function_usage'] as $func): ?>
                    <div class="function-card">
                        <h3><?php echo esc_html($func['function_called']); ?></h3>
                        <p class="function-count"><?php echo number_format($func['count']); ?> calls</p>
                        <div class="function-bar" style="width: <?php echo ($func['count'] / $analytics['total_conversations']) * 100; ?>%"></div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Hourly Distribution -->
        <div class="analytics-section">
            <h2>⏰ Conversation Activity by Hour</h2>
            <div class="chart-wrapper">
                <canvas id="hourlyChart"></canvas>
            </div>
        </div>
        
        <!-- Recent Conversations -->
        <div class="analytics-section">
            <h2>💬 Recent Conversations</h2>
            <div class="conversations-list">
                <?php foreach ($recent_conversations as $conv): ?>
                    <div class="conversation-item">
                        <div class="conversation-header">
                            <span class="conversation-time"><?php echo date('M j, Y g:i A', strtotime($conv->created_at)); ?></span>
                            <span class="conversation-provider"><?php echo esc_html($conv->ai_provider); ?></span>
                            <span class="conversation-model"><?php echo esc_html($conv->ai_model); ?></span>
                        </div>
                        <div class="conversation-user-message">
                            <strong>User:</strong> <?php echo esc_html($conv->user_message); ?>
                        </div>
                        <div class="conversation-ai-response">
                            <strong>AI:</strong> <?php echo esc_html(substr($conv->ai_response, 0, 200)); ?><?php echo strlen($conv->ai_response) > 200 ? '...' : ''; ?>
                        </div>
                        <div class="conversation-meta">
                            <span>⚡ <?php echo $conv->response_time; ?>s</span>
                            <span>🎯 <?php echo $conv->tokens_used; ?> tokens</span>
                            <?php if ($conv->function_called): ?>
                                <span>⚙️ <?php echo esc_html($conv->function_called); ?></span>
                            <?php endif; ?>
                            <?php if ($conv->user_satisfaction): ?>
                                <span>⭐ <?php echo $conv->user_satisfaction; ?>/5</span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    
    <style>
        .ucfc-analytics-dashboard {
            max-width: 1400px;
            margin: 20px 0;
        }
        
        .analytics-filters {
            background: white;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .analytics-filters form {
            display: flex;
            gap: 15px;
            align-items: center;
        }
        
        .analytics-filters label {
            display: flex;
            flex-direction: column;
            gap: 5px;
            font-weight: 600;
        }
        
        .analytics-filters input[type="date"] {
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        
        .analytics-metrics {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .metric-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 25px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            gap: 20px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }
        
        .metric-card:hover {
            transform: translateY(-5px);
        }
        
        .metric-card:nth-child(2) {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }
        
        .metric-card:nth-child(3) {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        }
        
        .metric-card:nth-child(4) {
            background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
        }
        
        .metric-card:nth-child(5) {
            background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
        }
        
        .metric-card:nth-child(6) {
            background: linear-gradient(135deg, #30cfd0 0%, #330867 100%);
        }
        
        .metric-icon {
            font-size: 40px;
        }
        
        .metric-content h3 {
            margin: 0;
            font-size: 32px;
            font-weight: 700;
        }
        
        .metric-content p {
            margin: 5px 0 0;
            opacity: 0.9;
            font-size: 14px;
        }
        
        .analytics-charts {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .chart-container {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .chart-container h2 {
            margin-top: 0;
            color: #333;
            font-size: 18px;
        }
        
        .chart-wrapper {
            position: relative;
            height: 300px;
        }
        
        .provider-stats {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        
        .provider-stat-item {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }
        
        .provider-stat-item strong {
            text-transform: capitalize;
            color: #333;
        }
        
        .stat-bar {
            height: 8px;
            background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
            border-radius: 4px;
            transition: width 0.5s ease;
        }
        
        .analytics-section {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        
        .analytics-section h2 {
            margin-top: 0;
            color: #333;
            font-size: 20px;
        }
        
        .analytics-section table {
            margin-top: 15px;
        }
        
        .function-usage-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 15px;
        }
        
        .function-card {
            padding: 20px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            transition: border-color 0.3s ease;
        }
        
        .function-card:hover {
            border-color: #667eea;
        }
        
        .function-card h3 {
            margin: 0 0 10px;
            font-size: 16px;
            color: #333;
        }
        
        .function-count {
            font-size: 24px;
            font-weight: 700;
            color: #667eea;
            margin-bottom: 10px;
        }
        
        .function-bar {
            height: 6px;
            background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
            border-radius: 3px;
        }
        
        .conversations-list {
            display: flex;
            flex-direction: column;
            gap: 15px;
            margin-top: 15px;
        }
        
        .conversation-item {
            padding: 20px;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            transition: box-shadow 0.3s ease;
        }
        
        .conversation-item:hover {
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        
        .conversation-header {
            display: flex;
            gap: 15px;
            margin-bottom: 10px;
            font-size: 12px;
            color: #666;
        }
        
        .conversation-time {
            font-weight: 600;
        }
        
        .conversation-provider,
        .conversation-model {
            padding: 3px 8px;
            background: #f0f0f0;
            border-radius: 4px;
        }
        
        .conversation-user-message,
        .conversation-ai-response {
            margin: 10px 0;
            padding: 10px;
            border-radius: 6px;
        }
        
        .conversation-user-message {
            background: #f0f4ff;
        }
        
        .conversation-ai-response {
            background: #f0fff4;
        }
        
        .conversation-meta {
            display: flex;
            gap: 15px;
            margin-top: 10px;
            font-size: 12px;
            color: #666;
        }
        
        @media (max-width: 1024px) {
            .analytics-charts {
                grid-template-columns: 1fr;
            }
        }
    </style>
    
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
    jQuery(document).ready(function($) {
        // Trends Chart
        const trendsData = <?php echo json_encode($trends); ?>;
        const trendsCtx = document.getElementById('trendsChart');
        
        if (trendsCtx) {
            new Chart(trendsCtx, {
                type: 'line',
                data: {
                    labels: trendsData.map(d => d.date),
                    datasets: [{
                        label: 'Conversations',
                        data: trendsData.map(d => d.count),
                        borderColor: '#667eea',
                        backgroundColor: 'rgba(102, 126, 234, 0.1)',
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
                    },
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        }
        
        // Hourly Chart
        const hourlyData = <?php echo json_encode($analytics['hourly_distribution']); ?>;
        const hourlyCtx = document.getElementById('hourlyChart');
        
        if (hourlyCtx) {
            new Chart(hourlyCtx, {
                type: 'bar',
                data: {
                    labels: hourlyData.map(d => d.hour + ':00'),
                    datasets: [{
                        label: 'Conversations by Hour',
                        data: hourlyData.map(d => d.count),
                        backgroundColor: 'rgba(102, 126, 234, 0.8)',
                        borderColor: '#667eea',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        }
    });
    </script>
    <?php
}

/**
 * Handle CSV export
 */
add_action('admin_init', 'ucfc_handle_export');
function ucfc_handle_export() {
    if (!isset($_GET['page']) || $_GET['page'] !== 'ucfc-ai-analytics') {
        return;
    }
    
    if (!isset($_GET['export']) || $_GET['export'] !== 'csv') {
        return;
    }
    
    if (!current_user_can('manage_options')) {
        return;
    }
    
    global $ucfc_conversation_logger;
    
    $date_from = isset($_GET['date_from']) ? sanitize_text_field($_GET['date_from']) : date('Y-m-d', strtotime('-30 days'));
    $date_to = isset($_GET['date_to']) ? sanitize_text_field($_GET['date_to']) : date('Y-m-d');
    
    $csv_url = $ucfc_conversation_logger->export_to_csv($date_from, $date_to);
    
    if ($csv_url) {
        wp_redirect($csv_url);
        exit;
    }
}
