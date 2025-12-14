/**
 * DATABASE STORAGE & FETCH IMPLEMENTATION
 * User Profile Updates - Fully Implemented
 */

// ============================================================================
// 1. PROFILE DATA STORAGE (Database → Users Table)
// ============================================================================

STORED FIELDS:
  - first_name       (VARCHAR 255)      // User first name
  - last_name        (VARCHAR 255)      // User last name
  - email            (VARCHAR 255)      // Email address (unique)
  - phone            (VARCHAR 20)       // Phone number
  - address          (TEXT)             // Physical address
  - profile_image    (VARCHAR 255)      // Filename of profile picture
  - password_hash    (VARCHAR 255)      // Hashed password
  - updated_at       (TIMESTAMP)        // Last update timestamp

// ============================================================================
// 2. API SAVE ENDPOINT (Edit Profile)
// ============================================================================

ENDPOINT: PUT /api/auth/profile
LOCATION: Controllers/AuthController.php → updateProfile()
METHOD:   POST (with file upload support)

FLOW:
  1. Frontend (edit-profile.js) submits form with:
     - first_name, last_name, email, phone, address
     - profile_image (file upload) - OPTIONAL
     - current_password + new_password - OPTIONAL
  
  2. Backend (AuthController.updateProfile):
     - Validates all inputs
     - Handles file upload → saves to /uploads/profiles/
     - Calls AuthService.updateProfile()
  
  3. AuthService.updateProfile():
     - Builds dynamic SQL UPDATE query
     - Updates ONLY provided fields in users table
     - Returns updated user object
  
  4. Database:
     - Updates users table WHERE id = ?
     - Sets updated_at = CURRENT_TIMESTAMP
     - Persists all changes

// ============================================================================
// 3. API FETCH ENDPOINT (Get Current User)
// ============================================================================

ENDPOINT: GET /api/auth/me
LOCATION: Controllers/AuthController.php → getMe()

RETURNS: Complete user object with all fields:
{
  "success": true,
  "data": {
    "id": "uuid",
    "email": "user@example.com",
    "first_name": "John",
    "last_name": "Doe",
    "full_name": "John Doe",
    "phone": "+63917123456",
    "address": "123 Main St, City, Country",
    "profile_image": "profile_user123_1702567890.jpg",
    "account_number": "ACC202412345",
    "role": "customer",
    "created_at": "2024-01-15T10:30:00Z",
    "updated_at": "2024-12-15T14:45:00Z"
  }
}

// ============================================================================
// 4. FRONTEND STORAGE (LocalStorage → Easy Access)
// ============================================================================

localStorage.setItem('user', JSON.stringify(userObject))
localStorage.setItem('auth_token', 'JWT_TOKEN')

FETCHED BY: app.js → loadProfileDisplay()
UPDATED BY: edit-profile.js → After successful profile save

// ============================================================================
// 5. NAVBAR SYNC (Real-time Updates)
// ============================================================================

TRIGGER: When user updates profile:
  → localStorage.setItem('profile_updated_event', Date.now().toString())

LISTENER: app.js → window.addEventListener('storage', ...)
ACTION:   Automatically re-renders navbar with updated profile

// ============================================================================
// 6. VERIFICATION STEPS (Ensure Data Persists)
// ============================================================================

TO TEST:
  1. Register new user or login
  2. Go to Edit Profile page
  3. Update first_name, last_name, profile picture
  4. Click "Save Changes"
  5. Refresh page → data persists ✓
  6. Open new tab → profile shows in navbar ✓
  7. Check browser DevTools:
     - localStorage → "user" key shows updated values
     - Network tab → PUT /api/auth/profile returns success
     - Database query → SELECT * FROM users WHERE email = ? shows updates

// ============================================================================
// 7. DATA FLOW DIAGRAM
// ============================================================================

SAVE FLOW:
  Edit Profile Form
       ↓
  fetch('/api/auth/profile', { method: 'POST', body: FormData })
       ↓
  Controllers/AuthController.updateProfile()
       ↓
  Services/AuthService.updateProfile()
       ↓
  Database: UPDATE users SET ... WHERE id = ?
       ↓
  localStorage.setItem('user', JSON.stringify(result))
  localStorage.setItem('profile_updated_event', timestamp)
       ↓
  window.dispatchEvent(storage event)
       ↓
  app.js listener → loadProfileDisplay()
       ↓
  Navbar updated ✓

FETCH FLOW:
  fetch('/api/auth/me')
       ↓
  Controllers/AuthController.getMe()
       ↓
  User::find(userId) → queries database
       ↓
  Returns User.toArray() with all fields
       ↓
  Frontend updates localStorage['user']
       ↓
  Display in navbar/profile pages

// ============================================================================
// 8. MIGRATION APPLIED
// ============================================================================

File: database/migrations/010_add_profile_fields.sql

Adds columns if they don't exist:
  - first_name VARCHAR(255)
  - last_name VARCHAR(255)
  - profile_image VARCHAR(255)
  - phone VARCHAR(20)
  - address TEXT
  - updated_at TIMESTAMP

Run this in Supabase SQL Editor to ensure schema is up-to-date.

// ============================================================================
// 9. KEY IMPLEMENTATIONS
// ============================================================================

✓ Profile Update API       → Saves to database
✓ Profile Fetch API        → Gets from database
✓ LocalStorage Sync        → Client-side caching
✓ Navbar Listener          → Real-time updates across tabs
✓ Image Upload Handler     → Stores files + database reference
✓ Password Change Support  → Hashes and stores securely
✓ Validation              → Email uniqueness, image type/size
✓ Error Handling          → Detailed error messages returned
✓ Timestamp Tracking      → updated_at field auto-updated
✓ Database Schema         → Migration file created

// ============================================================================
// 10. TESTING CHECKLIST
// ============================================================================

[ ] User can update first_name and see it in navbar immediately
[ ] User can update last_name and see it in navbar immediately  
[ ] User can upload profile image and see it in navbar
[ ] Refresh page and profile data persists
[ ] Open new tab with same account and see updated profile
[ ] Logout and login → profile data loads from database
[ ] Update password and can login with new password
[ ] Database shows updated_at timestamp on changes
[ ] Payment history and settings still load correctly
[ ] Profile changes don't affect other user functionality
