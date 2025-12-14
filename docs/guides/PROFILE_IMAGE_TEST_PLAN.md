# Profile Image Persistence - Test Plan

## Test Environment
- PHP Server: Running on localhost:8000
- Browser: Any modern browser
- User Account: Test with account that has profile image uploaded

## Pre-Test Setup

1. **Ensure PHP Server is Running**
   ```powershell
   Get-Process php
   ```
   Should show PHP process running

2. **Clear Browser Cache** (Optional but recommended)
   - Press F12 to open Developer Tools
   - Right-click refresh button → "Empty Cache and Hard Reload"

## Test Cases

### Test 1: Initial Profile Image Display

**Steps:**
1. Navigate to http://localhost:8000/public/edit-profile.php
2. Login if not already logged in
3. Upload a profile image
4. Click "Save Changes"
5. Wait for success message

**Expected Result:**
- ✅ Profile image appears in header (small circle)
- ✅ Profile image appears in left sidebar (larger circle)
- ✅ Success toast notification appears
- ✅ No console errors

### Test 2: Profile Image Persistence - Active Requests Page

**Steps:**
1. From edit-profile page, navigate to http://localhost:8000/public/active-requests.php
2. Observe the header profile button (top right corner)
3. Click on the profile button to open dropdown

**Expected Result:**
- ✅ Profile image appears in header button
- ✅ Profile image appears in dropdown avatar
- ✅ User name and email are displayed correctly

### Test 3: Profile Image Persistence - Announcements Page

**Steps:**
1. Navigate to http://localhost:8000/public/announcements.php
2. Observe the header profile button
3. Open the profile dropdown

**Expected Result:**
- ✅ Profile image appears in header button
- ✅ Profile image appears in dropdown avatar
- ✅ Image is the same as uploaded in Test 1

### Test 4: Profile Image Persistence - Discussions Page

**Steps:**
1. Navigate to http://localhost:8000/public/discussions.php
2. Check header profile button
3. Open dropdown menu

**Expected Result:**
- ✅ Profile image displays correctly
- ✅ No default gradient background visible
- ✅ Image loads without delay

### Test 5: Profile Image Persistence - Dashboard Page

**Steps:**
1. Navigate to http://localhost:8000/public/user-dashboard.php
2. Check all profile instances:
   - Header button (top right)
   - Dropdown avatar (when clicked)

**Expected Result:**
- ✅ Profile image displays in all locations
- ✅ Consistent image across all instances
- ✅ No broken image icons

### Test 6: Profile Image Persistence After Logout/Login

**Steps:**
1. Click profile button → Logout
2. Login again with same credentials
3. Navigate to any page (e.g., active-requests.php)
4. Check profile button

**Expected Result:**
- ✅ Profile image persists after logout/login
- ✅ Image loaded from database
- ✅ SessionStorage updated with profile_image field

### Test 7: Profile Image in Multiple Pages (Navigation Test)

**Steps:**
1. Start at user-dashboard.php
2. Navigate through these pages in sequence:
   - active-requests.php
   - announcements.php
   - discussions.php
   - help-support.php
   - linked-meters.php
   - notifications.php
   - payments.php
   - service-addresses.php
   - service-history.php
   - edit-profile.php (back to start)
3. On each page, verify profile image in header

**Expected Result:**
- ✅ Profile image appears on ALL pages
- ✅ No pages show initials instead of image
- ✅ No loading delays or flickering
- ✅ Image URL correct: `/api/uploads/profiles/{filename}`

### Test 8: Console and Network Verification

**Steps:**
1. Open Browser DevTools (F12)
2. Navigate to any page with profile image
3. Check Console tab for errors
4. Check Network tab for image request

**Expected Result:**
- ✅ No JavaScript errors in console
- ✅ No 404 errors for profile image
- ✅ Image request returns 200 OK status
- ✅ Image loads via `/api/uploads/profiles/` endpoint
- ✅ Correct MIME type (image/jpeg, image/png, etc.)

### Test 9: SessionStorage Verification

**Steps:**
1. Open DevTools → Application/Storage tab
2. Navigate to Session Storage → http://localhost:8000
3. Check for 'user' key
4. View the value

**Expected Result:**
- ✅ 'user' key exists
- ✅ JSON contains `profile_image` field
- ✅ Value is filename only (e.g., "profile_1_1234567890.jpg")
- ✅ No full path stored (no forward/backward slashes)

### Test 10: Fallback to Initials (No Image)

**Steps:**
1. Login with an account that has NO profile image
2. Navigate to any page
3. Check profile button

**Expected Result:**
- ✅ Profile button shows initials (e.g., "JD" for John Doe)
- ✅ Gradient background visible
- ✅ Initials are uppercase
- ✅ Maximum 2 letters displayed

## Debugging

### If Profile Image Doesn't Show:

1. **Check SessionStorage:**
   ```javascript
   // In browser console:
   JSON.parse(sessionStorage.getItem('user'))
   ```
   Should show user object with `profile_image` field

2. **Check Image URL:**
   ```javascript
   // In browser console, on any page:
   document.getElementById('profileBtn').querySelector('img')?.src
   ```
   Should show: `http://localhost:8000/api/uploads/profiles/{filename}`

3. **Check File Exists:**
   - Navigate directly to the image URL in browser
   - Should display the image, not 404

4. **Check Console for Errors:**
   - Look for JavaScript errors
   - Look for network errors (404, 500, etc.)

5. **Verify loadProfileDisplay() Runs:**
   ```javascript
   // Add this to dashboard.js temporarily for debugging:
   console.log('loadProfileDisplay() executed');
   console.log('User data:', JSON.parse(sessionStorage.getItem('user')));
   ```

### Common Issues:

| Issue | Cause | Solution |
|-------|-------|----------|
| Image shows on one page but not others | `loadProfileDisplay()` not running on all pages | Verify `dashboard.js` included on all pages |
| 404 error for image | Wrong path or missing file | Check filename in database and file in `uploads/profiles/` |
| Initials show instead of image | `profile_image` field empty or null | Re-upload image in edit-profile page |
| Image doesn't update after upload | SessionStorage not updated | Verify line 301 in edit-profile.js runs |
| Broken image icon | Invalid image format or corrupted file | Re-upload valid image file |

## Success Criteria

✅ **PASS** if ALL of the following are true:
- Profile image displays on ALL pages listed in Test 7
- Image persists after logout/login (Test 6)
- No JavaScript console errors (Test 8)
- SessionStorage contains correct data (Test 9)
- Image loads via correct API endpoint (Test 8)
- Fallback to initials works when no image (Test 10)

❌ **FAIL** if ANY of the following occur:
- Image missing on any page
- JavaScript errors in console
- 404 errors for image requests
- Image doesn't persist after navigation
- Image lost after logout/login

## Test Results

Date: _______________
Tester: _______________

| Test Case | Result | Notes |
|-----------|--------|-------|
| Test 1 | ☐ Pass ☐ Fail | |
| Test 2 | ☐ Pass ☐ Fail | |
| Test 3 | ☐ Pass ☐ Fail | |
| Test 4 | ☐ Pass ☐ Fail | |
| Test 5 | ☐ Pass ☐ Fail | |
| Test 6 | ☐ Pass ☐ Fail | |
| Test 7 | ☐ Pass ☐ Fail | |
| Test 8 | ☐ Pass ☐ Fail | |
| Test 9 | ☐ Pass ☐ Fail | |
| Test 10 | ☐ Pass ☐ Fail | |

**Overall Result:** ☐ PASS ☐ FAIL

**Additional Notes:**
_______________________________________________________________
_______________________________________________________________
_______________________________________________________________
