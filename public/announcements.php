<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Announcements - FixItMati</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <link rel="stylesheet" href="../assets/announcements.css">
</head>
<body>
    <div class="page-container animate-fade-in">
        
        <!-- HEADER -->
        <header>
            <div class="header-left">
                <div class="logo-icon">
                    <i data-lucide="megaphone" class="text-white" style="width: 20px; height: 20px;"></i>
                </div>
                <span class="logo-text">FixItMati</span>
            </div>
            <div class="header-right">
                <button class="notification-btn">
                    <i data-lucide="bell" style="width: 20px; height: 20px;"></i>
                </button>
                <div class="user-avatar"></div>
            </div>
        </header>

        <!-- MAIN CONTENT -->
        <div class="main-content">
            
            <!-- Navigation Breadcrumb -->
            <div class="breadcrumb">
                <a href="user-dashboard.php" class="back-btn">
                    <i data-lucide="arrow-left" style="width: 16px; height: 16px;"></i>
                    Back to Dashboard
                </a>
                <span class="breadcrumb-separator">/</span>
                <span class="breadcrumb-current">Announcements</span>
            </div>

            <div class="layout-container">
                
                <!-- LEFT SIDEBAR: FILTERS -->
                <div class="sidebar">
                    <div class="sidebar-sticky">
                        <h3 class="sidebar-title">
                            <i data-lucide="filter" style="width: 14px; height: 14px;"></i>
                            Filter By Utility
                        </h3>
                        <nav class="filter-nav">
                            <button class="filter-btn active" data-value="All">
                                <span class="filter-icon">
                                    <i data-lucide="megaphone" style="width: 14px; height: 14px;"></i>
                                </span>
                                <span class="filter-label">All Updates</span>
                                <span class="check-icon">
                                    <i data-lucide="check-circle-2" style="width: 14px; height: 14px;"></i>
                                </span>
                            </button>
                            <button class="filter-btn" data-value="Water Supply">
                                <span class="filter-icon">
                                    <i data-lucide="droplets" style="width: 14px; height: 14px;"></i>
                                </span>
                                <span class="filter-label">Water Supply</span>
                                <span class="check-icon">
                                    <i data-lucide="check-circle-2" style="width: 14px; height: 14px;"></i>
                                </span>
                            </button>
                            <button class="filter-btn" data-value="Electricity">
                                <span class="filter-icon">
                                    <i data-lucide="zap" style="width: 14px; height: 14px;"></i>
                                </span>
                                <span class="filter-label">Electricity</span>
                                <span class="check-icon">
                                    <i data-lucide="check-circle-2" style="width: 14px; height: 14px;"></i>
                                </span>
                            </button>
                            <button class="filter-btn" data-value="Urgent">
                                <span class="filter-icon">
                                    <i data-lucide="alert-triangle" style="width: 14px; height: 14px;"></i>
                                </span>
                                <span class="filter-label">Urgent Alerts</span>
                                <span class="check-icon">
                                    <i data-lucide="check-circle-2" style="width: 14px; height: 14px;"></i>
                                </span>
                            </button>
                        </nav>

                        <div class="sms-alert-box">
                            <div class="sms-alert-card">
                                <h4 class="sms-alert-title">
                                    <i data-lucide="mail" style="width: 12px; height: 12px;"></i>
                                    SMS Alerts
                                </h4>
                                <p class="sms-alert-text">Get notified instantly about interruptions.</p>
                                <button class="sms-alert-btn">Subscribe</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- CENTER: NEWS FEED -->
                <div class="feed-container">
                    
                    <!-- Context Header -->
                    <div class="feed-header">
                        <h2 class="feed-title" id="feedTitle">Latest Announcements</h2>
                        <span class="feed-count" id="feedCount">Showing 4 posts</span>
                    </div>

                    <!-- List -->
                    <div class="announcements-list" id="announcementsList">
                        <!-- Announcements will be dynamically inserted here by JavaScript -->
                    </div>
                </div>
            </div>
        </div>

        <!-- MODAL: POST TO DISCUSSIONS -->
        <div id="discussionModal" class="hidden"></div>

    </div>

    <script src="../assets/announcements.js"></script>
    <script>lucide.createIcons();</script>
</body>
</html>
