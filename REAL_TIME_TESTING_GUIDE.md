# Real-Time Data Testing Guide

## Overview
All pages now fetch and display real-time data from the database. This guide will help you verify everything works correctly.

## Pre-Test Checklist

### 1. Start the Server
```powershell
php -S localhost:8000
```

### 2. Ensure Database Has Data
Run this command to verify:
```powershell
php check-users.php
```

You should see users with payments like:
- test.customer@example.com: 3 bills, ₱2,921.00 due
- jaysonB354@gmail.com: 3 bills, ₱2,193.00 due

## Testing Real-Time Data Flow

### Test 1: Dashboard Cards Load Real Data

**URL:** http://localhost:8000/public/user-dashboard.php

**Expected Behavior:**
1. **Active Requests Card:**
   - Shows actual count of pending/in_progress requests from database
   - Click should navigate to active-requests.php

2. **Total Amount Due Card:**
   - Shows actual total from payments table (e.g., ₱2,193.00)
   - Shows actual earliest due date
   - Click should navigate to payments.php

3. **Announcements Card:**
   - Shows count of new/urgent announcements (within 7 days or urgent priority)
   - Shows latest announcement title as subtitle
   - Click should navigate to announcements.php

4. **Resolved Issues Card:**
   - Shows actual count of completed requests
   - Number updates when requests are completed

**How to Verify:**
1. Open browser console (F12)
2. Watch for API calls:
   - `/api/requests` - loads requests
   - `/api/announcements` - loads announcements
   - `/api/payments/current` - loads payment data
3. Card numbers should match database values
4. Refresh page - numbers should persist

---

### Test 2: Payments Page Real-Time Display

**URL:** http://localhost:8000/public/payments.php

**Expected Behavior:**

**User Payment Information Card:**
- Amount Due: Shows actual total from database (not hardcoded ₱1,250.00)
- Due Date: Shows actual earliest due date
- Account Status:
  - "Paid" (green) if total = ₱0.00
  - "Overdue" (red) if any bill is overdue
  - "Payment Required" (amber) if bills are unpaid

**Current Charges Section:**
- Shows list of unpaid bills with:
  - Bill month (e.g., "December 2024")
  - Amount per bill
  - Due date
  - Bill items (Water: ₱450.00, Electricity: ₱800.00)
- If no bills: Shows "No pending bills" message

**Transaction History:**
- Shows recent completed payments
- Empty if no payment history

**How to Verify:**
1. Login with test user (jaysonB354@gmail.com or test.customer@example.com)
2. Navigate to Payments page
3. Check console for `/api/payments/current` API call
4. Verify displayed amounts match database values from `check-users.php`
5. Click "Pay Bill Now" - should show modal with correct total

---

### Test 3: Active Requests Page Real-Time Display

**URL:** http://localhost:8000/public/active-requests.php

**Expected Behavior:**
- Shows only requests with status='pending' or status='in_progress'
- Left panel lists all active requests
- Right panel shows selected request details
- Status badges show correct colors:
  - Pending: Yellow
  - In Progress: Blue
  - Completed: Green

**How to Verify:**
1. Check console for `/api/requests` API call
2. Count should match Active Requests card on dashboard
3. Click different requests - detail view should update
4. No hardcoded dummy data visible

---

### Test 4: Announcements Page Real-Time Display

**URL:** http://localhost:8000/public/announcements.php

**Expected Behavior:**
- Loads announcements from `/api/announcements`
- Shows actual announcements from database
- Filter buttons work:
  - All: Shows all announcements
  - Water Supply: Shows water category only
  - Electricity: Shows electricity category only
  - Urgent: Shows urgent/high priority only

**How to Verify:**
1. Check console for `/api/announcements` API call
2. Announcements should have:
   - Real titles from database
   - Correct categories (Water/Electricity)
   - Real dates
   - Author names
3. Click expand button - should show full content
4. Count should match Announcements card on dashboard

---

### Test 5: Cross-Page Data Consistency

**Test Flow:**
1. Login at http://localhost:8000/public/login.php
2. View Dashboard - note all card numbers
3. Click "Active Requests" card → Should show same count
4. Go back, click "Payments" card → Should show same amount
5. Go back, click "Announcements" card → Should show same count

**Expected Result:**
- All numbers match across pages
- No page shows hardcoded dummy data
- Refreshing any page maintains correct data

---

## Console Verification

### Check API Calls in Browser Console

After loading each page, you should see:

**Dashboard (user-dashboard.php):**
```
GET /api/requests
GET /api/announcements  
GET /api/payments/current
```

**Payments (payments.php):**
```
GET /api/payments/current
GET /api/payments/history?limit=5
```

**Active Requests (active-requests.php):**
```
GET /api/requests
```

**Announcements (announcements.php):**
```
GET /api/announcements
```

### Check for Errors

In console, look for:
- ❌ "Error loading payment data" - Payment API failed
- ❌ "Error loading dashboard data" - General API failure
- ❌ "Failed to load announcements" - Announcements API failed
- ✅ No errors = All working correctly

---

## Common Issues & Solutions

### Issue: Cards Show "Loading..." or Old Numbers

**Solution:**
1. Clear browser cache (Ctrl+Shift+Delete)
2. Hard refresh (Ctrl+F5)
3. Check console for API errors

### Issue: "API Client failed to load"

**Solution:**
1. Verify server is running on http://localhost:8000
2. Check all asset paths are absolute (/assets/...)
3. Clear browser cache

### Issue: Wrong Amount Showing

**Solution:**
1. Run `php check-users.php` to verify database values
2. Check which user you're logged in as
3. Verify API returns correct user_id

### Issue: No Announcements/Requests Showing

**Solution:**
1. Check if database has data for that user
2. Run seed scripts if needed:
   ```powershell
   php seed-all-data.php
   ```
3. Verify user role is 'customer' not 'user'

---

## Success Criteria

✅ Dashboard cards show real numbers from database
✅ Payments page shows actual bill amounts (not ₱1,250.00)
✅ Current Charges section populates with real bills
✅ Active Requests count matches dashboard card
✅ Announcements count matches dashboard card  
✅ All pages load data via API (check console)
✅ Refreshing pages maintains correct data
✅ Cross-page navigation shows consistent numbers

---

## Next Steps After Testing

If all tests pass:
1. ✅ Real-time data flow is working
2. ✅ All pages are synchronized
3. ✅ Ready for production use

If any tests fail:
1. Check browser console for errors
2. Verify API endpoints are responding
3. Check authentication token is valid
4. Review error messages and fix accordingly
