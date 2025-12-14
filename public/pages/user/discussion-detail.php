<?php
// Discussion Detail Page for FixItMati
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Discussion - FixItMati</title>
  <!-- Check authentication client-side -->
  <script>
    // Redirect to login if not authenticated
    (function() {
      const token = sessionStorage.getItem('auth_token');
      if (!token) {
        window.location.replace('login.php');
        throw new Error('Not authenticated');
      }
    })();
  </script>
  <!-- Tailwind CSS via CDN -->
  <script src="https://cdn.tailwindcss.com"></script>
  <!-- Lucide Icons CDN -->
  <script src="https://unpkg.com/lucide@latest"></script>
  <!-- App styles -->
  <link rel="stylesheet" href="/assets/style.css" />
</head>
<body class="min-h-screen bg-slate-50 font-sans text-slate-800">

  <!-- HEADER -->
  <header class="bg-white border-b border-slate-200 sticky top-0 z-30">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex justify-between items-center h-16">
        <!-- Logo & Mobile Menu -->
        <div class="flex items-center gap-4">
          <button id="mobileMenuBtn" class="lg:hidden p-2 rounded-md text-slate-500 hover:bg-slate-100" aria-label="Toggle Menu">
            <i data-lucide="menu" class="w-6 h-6"></i>
          </button>
          <a href="user-dashboard.php" class="flex items-center gap-2 cursor-pointer hover:opacity-80 transition-opacity">
            <div class="bg-blue-600 p-1.5 rounded-lg">
              <i data-lucide="hammer" class="text-white w-5 h-5"></i>
            </div>
            <span class="text-xl font-bold tracking-tight text-blue-900">FixItMati</span>
          </a>
        </div>
        <!-- Right Actions -->
        <div class="flex items-center gap-3">
          <a href="user-dashboard.php" class="p-2 text-slate-500 hover:bg-slate-100 rounded-full cursor-pointer transition-colors" title="Go to Dashboard">
            <i data-lucide="home" class="w-5 h-5"></i>
          </a>
          <div class="relative p-2 text-slate-500 hover:bg-slate-100 rounded-full cursor-pointer" id="notificationBtn">
            <i data-lucide="bell" class="w-5 h-5"></i>
            <span class="absolute top-1.5 right-1.5 block h-2 w-2 rounded-full bg-red-500 ring-2 ring-white" id="notificationDot"></span>
          </div>
          <!-- Profile Dropdown -->
          <div class="relative">
            <div class="h-8 w-8 rounded-full bg-gradient-to-tr from-blue-500 to-cyan-400 border-2 border-white shadow-sm cursor-pointer" id="profileBtn"></div>
            
            <!-- Profile Dropdown Menu -->
            <div id="profileDropdown" class="hidden absolute right-0 mt-2 w-72 bg-white rounded-lg shadow-lg border border-slate-200 z-50">
              <div class="p-4 border-b border-slate-100">
                <div class="flex items-center gap-3">
                  <div class="h-12 w-12 rounded-full bg-gradient-to-tr from-blue-500 to-cyan-400 flex items-center justify-center text-white font-bold text-lg" id="profileAvatarLarge"></div>
                  <div class="flex-1 min-w-0">
                    <p class="font-semibold text-slate-900 truncate" id="profileName">Loading...</p>
                    <p class="text-sm text-slate-500 truncate" id="profileEmail">Loading...</p>
                  </div>
                </div>
              </div>
              
              <div class="p-2">
                <button id="profileEditBtn" class="w-full flex items-center gap-3 px-3 py-2 text-sm text-slate-700 hover:bg-slate-50 rounded-md transition-colors">
                  <i data-lucide="user-pen" class="w-4 h-4"></i>
                  <span>Edit Profile</span>
                </button>
                <button id="logoutBtn" class="w-full flex items-center gap-3 px-3 py-2 text-sm text-red-600 hover:bg-red-50 rounded-md transition-colors">
                  <i data-lucide="log-out" class="w-4 h-4"></i>
                  <span>Logout</span>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </header>

  <!-- SUB-NAV -->
  <div class="bg-white border-b border-slate-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex items-center justify-between h-16">
        <div class="flex items-center gap-2">
          <a href="discussions.php" class="text-slate-500 hover:text-blue-600 transition-colors">
            <i data-lucide="arrow-left" class="w-5 h-5"></i>
          </a>
          <div>
            <h1 class="text-lg font-bold text-slate-800">Discussion</h1>
            <p class="text-xs text-slate-500">View and participate</p>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- MAIN CONTENT -->
  <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    
    <!-- Loading State -->
    <div id="loadingState" class="text-center py-12">
      <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto mb-4"></div>
      <p class="text-slate-500 text-sm">Loading discussion...</p>
    </div>

    <!-- Discussion Content -->
    <div id="discussionContent" class="hidden space-y-6">
      
      <!-- Main Discussion Card -->
      <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6">
        <!-- Header -->
        <div class="flex items-start gap-4 mb-6">
          <div class="h-12 w-12 rounded-full bg-slate-100 flex items-center justify-center text-slate-400">
            <i data-lucide="user" class="w-6 h-6"></i>
          </div>
          <div class="flex-1">
            <div class="flex items-center gap-3 mb-2">
              <span id="discussionCategory" class="bg-blue-100 text-blue-700 text-xs font-bold px-3 py-1 rounded-full"></span>
              <span id="discussionAnswered" class="hidden bg-green-100 text-green-700 text-xs font-bold px-3 py-1 rounded-full flex items-center gap-1">
                <i data-lucide="check-circle-2" class="w-3 h-3"></i> Answered
              </span>
            </div>
            <h1 id="discussionTitle" class="text-2xl font-bold text-slate-800 mb-2"></h1>
            <p class="text-sm text-slate-500">
              Posted by <span id="discussionAuthor" class="font-medium"></span> • 
              <span id="discussionTime"></span>
            </p>
          </div>
          
          <!-- Actions -->
          <div class="flex items-center gap-2">
            <button id="upvoteBtn" class="flex flex-col items-center gap-1 bg-slate-50 px-4 py-2 rounded-lg border border-slate-200 hover:bg-blue-50 hover:border-blue-200 transition-colors">
              <i data-lucide="thumbs-up" class="w-5 h-5 text-slate-600"></i>
              <span id="upvoteCount" class="font-bold text-slate-700">0</span>
            </button>
            <button id="deleteDiscussionBtn" class="hidden p-2 text-red-500 hover:bg-red-50 rounded-lg transition-colors" title="Delete discussion">
              <i data-lucide="trash-2" class="w-5 h-5"></i>
            </button>
          </div>
        </div>

        <!-- Content -->
        <div id="discussionBody" class="prose prose-slate max-w-none mb-6 text-slate-700 whitespace-pre-wrap"></div>

        <!-- Stats -->
        <div class="flex items-center gap-6 pt-4 border-t border-slate-100 text-sm text-slate-500">
          <div class="flex items-center gap-2">
            <i data-lucide="message-circle" class="w-4 h-4"></i>
            <span><span id="commentsCount">0</span> comments</span>
          </div>
          <div class="flex items-center gap-2">
            <i data-lucide="eye" class="w-4 h-4"></i>
            <span>Public</span>
          </div>
        </div>
      </div>

      <!-- Comments Section -->
      <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6">
        <h2 class="text-lg font-bold text-slate-800 mb-4 flex items-center gap-2">
          <i data-lucide="message-square" class="w-5 h-5"></i>
          Comments
        </h2>

        <!-- Add Comment Form -->
        <form id="addCommentForm" class="mb-6">
          <textarea 
            id="commentContent"
            rows="3"
            placeholder="Share your thoughts or provide a solution..."
            class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none mb-2"
            required
          ></textarea>
          <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition-colors">
            Post Comment
          </button>
        </form>

        <!-- Comments List -->
        <div id="commentsList" class="space-y-4">
          <p class="text-slate-400 text-sm text-center py-4">No comments yet. Be the first to comment!</p>
        </div>
      </div>

    </div>

    <!-- Error State -->
    <div id="errorState" class="hidden text-center py-12">
      <div class="text-red-500 mb-4">
        <i data-lucide="alert-circle" class="w-16 h-16 mx-auto"></i>
      </div>
      <h2 class="text-xl font-bold text-slate-800 mb-2">Discussion not found</h2>
      <p class="text-slate-500 mb-4">This discussion may have been deleted or doesn't exist.</p>
      <a href="discussions.php" class="text-blue-600 hover:text-blue-700 font-medium">
        ← Back to discussions
      </a>
    </div>

  </div>

  <!-- API Client -->
  <script src="/assets/api-client.js"></script>
  <!-- Dashboard JS -->
  <script src="/assets/dashboard.js"></script>
  <!-- Discussion Detail JS -->
  <script src="/assets/discussion-detail.js"></script>
</body>
</html>
