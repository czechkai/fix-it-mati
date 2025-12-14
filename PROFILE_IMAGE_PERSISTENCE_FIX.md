# Profile Image Persistence Fix

## Problem
Profile images were disappearing when navigating between pages. The images would display on the edit-profile page and user-dashboard page, but not on other pages like active-requests, announcements, discussions, etc.

## Root Cause
The profile image display logic was embedded inside conditional blocks in `dashboard.js` that only executed under specific conditions. While the profile data was properly stored in `sessionStorage`, the code to read and display it wasn't running consistently across all pages.

## Solution Applied

### 1. Modified `assets/dashboard.js`
Extracted the profile display logic into a standalone function `loadProfileDisplay()` that:
- Reads user data from `sessionStorage`
- Extracts first name, last name, and email
- Handles profile images (both base64 and file paths)
- Constructs proper API URLs for uploaded images (`/api/uploads/profiles/{filename}`)
- Updates both `profileBtn` (header icon) and `profileAvatarLarge` (dropdown avatar)
- Displays user initials as fallback when no image exists

### 2. Modified `assets/app.js`
Added the same `loadProfileDisplay()` function with automatic execution:
- Runs immediately on page load
- Uses `DOMContentLoaded` event if page is still loading
- Ensures profile is displayed before any other page logic runs

## How It Works

### Profile Image Flow:
1. User uploads image in edit-profile.php
2. Image saved to `uploads/profiles/` directory
3. Filename stored in database (e.g., `profile_1_1234567890.jpg`)
4. User data updated in `sessionStorage` including `profile_image` field
5. When any page loads, `loadProfileDisplay()` runs
6. Function reads `profile_image` from `sessionStorage`
7. Constructs URL: `/api/uploads/profiles/{filename}`
8. Updates profile button and avatar with image

### Pages Affected:
- ✅ user-dashboard.php
- ✅ active-requests.php
- ✅ announcements.php
- ✅ discussions.php
- ✅ discussion-detail.php
- ✅ edit-profile.php
- ✅ help-support.php
- ✅ linked-meters.php
- ✅ notifications.php
- ✅ payment-history.php
- ✅ payments.php
- ✅ service-addresses.php
- ✅ service-history.php
- ✅ settings.php
- ✅ create-request.php

All pages that include `dashboard.js` now properly display the profile image.

## Technical Details

### Image Path Handling:
```javascript
if (userData.profile_image.startsWith('data:')) {
  imageSrc = userData.profile_image; // Base64 image
} else {
  // Extract filename from path
  const filename = userData.profile_image.includes('/') || userData.profile_image.includes('\\')
    ? userData.profile_image.split(/[\\/]/).pop()
    : userData.profile_image;
  imageSrc = '/api/uploads/profiles/' + filename;
}
```

### Display Priority:
1. If `profile_image` exists → Display image via API endpoint
2. If no image → Display initials from first/last name
3. If no name → Format email username as display name

## Testing

### To Test:
1. Upload a profile image in edit-profile.php
2. Navigate to different pages (active-requests, announcements, etc.)
3. Profile image should persist in header and dropdown on all pages
4. Logout and login again - image should still be there

### Expected Behavior:
- Profile image appears in small circle in header (profileBtn)
- Profile image appears in larger circle in dropdown menu (profileAvatarLarge)
- Image persists across page navigation
- Image persists across logout/login (stored in database)

## Files Modified

1. **assets/dashboard.js** (lines 577-625)
   - Extracted `loadProfileDisplay()` function
   - Called immediately on script load
   - Removed duplicate logic from conditional blocks

2. **assets/app.js** (lines 1-66)
   - Added `loadProfileDisplay()` function
   - Ensures execution on all pages that load app.js
   - Handles both immediate and deferred execution

## Related Files

- **Controllers/AuthController.php** - Handles image upload and validation
- **public/api/index.php** - Serves images via API endpoint
- **assets/edit-profile.js** - Updates sessionStorage after profile save
- **uploads/profiles/** - Directory where images are stored

## Notes

- Images are stored outside public directory for security
- API endpoint at `/api/uploads/profiles/{filename}` serves images
- MIME type detection uses `getimagesize()` for PHP 8 compatibility
- Profile data in `sessionStorage` includes just the filename
- Full path constructed in JavaScript when needed
