<?php
// Authentication check
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Active Requests - FixItMati</title>
    <!-- Check authentication client-side -->
    <script>
        (function() {
            const token = sessionStorage.getItem('auth_token');
            if (!token) {
                window.location.replace('login.php');
                throw new Error('Not authenticated');
            }
        })();
    </script>
    <link rel="stylesheet" href="assets/active-requests.css">
</head>
<body>
    <div class="page-container">
        
        <!-- HEADER -->
        <header>
            <div class="header-left">
                <button class="menu-btn" id="menuBtn">
                    <svg class="icon" viewBox="0 0 24 24">
                        <line x1="3" y1="12" x2="21" y2="12"></line>
                        <line x1="3" y1="6" x2="21" y2="6"></line>
                        <line x1="3" y1="18" x2="21" y2="18"></line>
                    </svg>
                </button>
                <div class="logo-container">
                    <div class="logo-icon">
                        <svg class="icon" viewBox="0 0 24 24">
                            <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"></path>
                        </svg>
                    </div>
                    <span class="logo-text">FixItMati</span>
                </div>
            </div>
            <div class="header-right">
                <button class="notification-btn" id="notificationBtn">
                    <svg class="icon" viewBox="0 0 24 24">
                        <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                        <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                    </svg>
                    <span class="notification-badge"></span>
                </button>
                <div class="user-avatar"></div>
            </div>
        </header>

        <!-- MAIN CONTENT AREA -->
        <div class="main-content">
            
            <!-- Breadcrumb / Back Navigation -->
            <div class="breadcrumb">
                <a href="user-dashboard.php" class="back-btn">
                    <svg class="icon icon-sm" viewBox="0 0 24 24">
                        <line x1="19" y1="12" x2="5" y2="12"></line>
                        <polyline points="12 19 5 12 12 5"></polyline>
                    </svg>
                    Back to Dashboard
                </a>
                <span class="breadcrumb-separator">/</span>
                <span class="breadcrumb-current">Active Requests</span>
            </div>

            <div class="layout-container">
                
                <!-- LEFT PANEL: LIST OF REQUESTS -->
                <div class="requests-panel">
                    <div class="panel-header">
                        <div class="panel-header-text">
                            <h2>Your Tickets</h2>
                            <p id="panelStats">Loading...</p>
                        </div>
                        <button class="filter-btn" id="filterBtn">
                            <svg class="icon" viewBox="0 0 24 24">
                                <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon>
                            </svg>
                        </button>
                    </div>
                    
                    <div class="requests-list" id="requestsList">
                        <div style="padding: 40px 20px; text-align: center; color: #94a3b8;">
                            <div style="width: 40px; height: 40px; margin: 0 auto 16px; border: 3px solid #e2e8f0; border-top-color: #3b82f6; border-radius: 50%; animation: spin 0.8s linear infinite;"></div>
                            <p style="font-size: 14px;">Loading requests...</p>
                        </div>
                    </div>
                    <style>
                        @keyframes spin { to { transform: rotate(360deg); } }
                    </style>
                </div>

                <!-- RIGHT PANEL: DETAILED TRACKING VIEW -->
                <div class="detail-panel" id="detailPanel" style="display: none;">
                    
                    <!-- Detail Header -->
                    <div class="detail-header">
                        <div class="detail-header-top">
                            <div class="detail-title-section">
                                <div class="detail-title-group">
                                    <h1 id="detailTitle"></h1>
                                    <span class="detail-status-badge" id="detailStatus"></span>
                                </div>
                                <div class="detail-meta">
                                    <div class="detail-meta-item" id="detailLocation"></div>
                                    <div class="detail-meta-item" id="detailDate"></div>
                                </div>
                            </div>
                            <button class="more-btn" id="moreBtn">
                                <svg class="icon" viewBox="0 0 24 24">
                                    <circle cx="12" cy="12" r="1"></circle>
                                    <circle cx="12" cy="5" r="1"></circle>
                                    <circle cx="12" cy="19" r="1"></circle>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Split Content: Timeline & Info -->
                    <div class="detail-content">
                        <div class="detail-layout">
                            
                            <!-- Timeline Section -->
                            <div class="timeline-section">
                                <h3 class="section-title">Progress Tracker</h3>
                                <div class="timeline" id="timeline"></div>
                            </div>

                            <!-- Details Sidebar -->
                            <div class="details-sidebar">
                                
                                <!-- Assigned Tech Card -->
                                <div class="info-card">
                                    <h4 class="info-card-title">Assigned Technician</h4>
                                    <div class="technician-info">
                                        <div class="technician-avatar">
                                            <svg class="icon" viewBox="0 0 24 24">
                                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                                <circle cx="12" cy="7" r="4"></circle>
                                            </svg>
                                        </div>
                                        <div class="technician-details">
                                            <p id="technicianName"></p>
                                            <p>Mati Water District</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Description Block -->
                                <div>
                                    <h4 class="info-card-title">Issue Description</h4>
                                    <p class="description-box" id="issueDescription"></p>
                                </div>
                                
                                <!-- Attachments -->
                                <div>
                                    <h4 class="info-card-title">Attachments</h4>
                                    <div class="attachments-grid">
                                        <div class="attachment-box">
                                            <svg class="icon" viewBox="0 0 24 24">
                                                <path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"></path>
                                            </svg>
                                        </div>
                                        <div class="attachment-image">
                                            <div class="attachment-image-overlay"></div>
                                            <img src="https://via.placeholder.com/64" alt="Evidence">
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>

                    <!-- Comment Input Footer -->
                    <div class="comment-footer">
                        <div class="comment-form">
                            <input 
                                type="text" 
                                id="messageInput"
                                placeholder="Type a message to support..." 
                                class="comment-input"
                            >
                            <button class="send-btn" id="sendBtn">
                                <svg class="icon" viewBox="0 0 24 24">
                                    <line x1="22" y1="2" x2="11" y2="13"></line>
                                    <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                                </svg>
                            </button>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <script src="assets/api-client.js?v=6"></script>
    <script src="assets/active-requests.js?v=5"></script>
</body>
</html>
