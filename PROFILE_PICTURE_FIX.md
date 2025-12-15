# Profile Picture Fix - README

## Issue

Profile pictures were not saving or displaying because the `profile_image` column was missing from the database.

## What Was Fixed

### 1. Database Schema Updated

- Added `profile_image` column to the `users` table in [schema.sql](database/schema.sql)
- Added `first_name` and `last_name` columns for better name handling
- Added `role` and `password_hash` columns that were being used but missing

### 2. Migration Created

- Created migration file: [database/migrations/add_profile_image_column.sql](database/migrations/add_profile_image_column.sql)
- This migration safely adds the column if it doesn't exist
- Run this migration on your existing database

### 3. User Model Updated

- Updated the `fill()` method in [Models/User.php](Models/User.php) to include `profile_image`, `first_name`, and `last_name`
- Now the model properly retrieves and stores the profile image filename

### 4. Upload Directory

- Verified that `uploads/profiles/` directory exists and is writable

## How to Apply the Fix

### Step 1: Run the Database Migration

If you're using **Supabase** (PostgreSQL):

1. Open your Supabase project
2. Go to the SQL Editor
3. Copy and paste the contents of `database/migrations/add_profile_image_column.sql`
4. Click "Run"

If you're using a **local PostgreSQL** database:

```bash
psql -U your_username -d your_database_name -f database/migrations/add_profile_image_column.sql
```

### Step 2: Test the Profile Picture Upload

1. Start your PHP server:

   ```bash
   php -S localhost:8000 -t public
   ```

2. Log in to your application

3. Go to Edit Profile page (`/pages/user/edit-profile.php`)

4. Click on the profile avatar to upload a picture

5. Click "Save Changes"

6. Refresh the page - your profile picture should now display in:
   - The profile avatar (left sidebar)
   - The header profile button (top right)
   - The profile dropdown

### Step 3: Verify in Other Pages

Navigate to other pages (dashboard, announcements, etc.) and verify that your profile picture displays in the header on all pages.

## How It Works

### Upload Process:

1. User selects an image in edit-profile.php
2. JavaScript validates the file (type, size)
3. On "Save Changes", the file is sent to `/api/auth/profile` endpoint
4. `AuthController->updateProfile()` handles the upload:
   - Validates file type and size (max 5MB)
   - Saves file to `uploads/profiles/` with unique name: `profile_{user_id}_{timestamp}.{ext}`
   - Stores the filename (not full path) in database
   - Deletes old profile image if exists

### Display Process:

1. When loading any page, `dashboard.js` calls `/api/auth/me`
2. API returns user data including `profile_image` filename
3. JavaScript constructs the image URL: `/api/uploads/profiles/{filename}`
4. The image is displayed in the header using this URL

### API Endpoint for Images:

- Route: `GET /api/uploads/profiles/{filename}`
- Defined in: `public/api/index.php`
- Serves images from `uploads/profiles/` directory
- Images are publicly accessible (no authentication required)

## File Structure

```
fix-it-mati/
├── database/
│   ├── schema.sql (updated with profile_image column)
│   └── migrations/
│       └── add_profile_image_column.sql (NEW)
├── Models/
│   └── User.php (updated fill() method)
├── Controllers/
│   └── AuthController.php (handles upload)
├── Services/
│   └── AuthService.php (updates database)
├── uploads/
│   └── profiles/ (stores uploaded images)
├── public/
│   ├── pages/
│   │   └── user/
│   │       └── edit-profile.php (upload UI)
│   └── api/
│       └── index.php (image serving endpoint)
└── assets/
    ├── edit-profile.js (handles upload)
    └── dashboard.js (displays images)
```

## Troubleshooting

### Profile picture not uploading:

1. Check browser console for errors
2. Check PHP error log
3. Verify `uploads/profiles/` directory exists and is writable:
   ```bash
   chmod 755 uploads/profiles/
   ```
4. Verify file size is under 5MB
5. Verify file type is JPG, PNG, GIF, or WebP

### Profile picture not displaying:

1. Check if the migration was run successfully
2. Verify the profile_image column exists:
   ```sql
   SELECT column_name FROM information_schema.columns
   WHERE table_name = 'users' AND column_name = 'profile_image';
   ```
3. Check if the image file exists in `uploads/profiles/`
4. Check browser console for image loading errors
5. Verify the image URL is correct: `/api/uploads/profiles/filename.jpg`

### Database Connection Issues:

1. Verify `config/database.php` has correct credentials
2. Test database connection with `scripts/check-db.php`
3. Ensure the users table exists

## Security Notes

- File uploads are validated for type (images only) and size (max 5MB)
- Uploaded files are stored outside the public directory
- Files are served through a controlled API endpoint
- Old profile images are automatically deleted when uploading new ones
- File names are generated with user ID and timestamp to prevent conflicts

## Next Steps

After applying this fix, you should be able to:

1. ✅ Upload profile pictures
2. ✅ See profile pictures persist after page refresh
3. ✅ See profile pictures in the header on all pages
4. ✅ See profile pictures in the profile dropdown
5. ✅ Have old profile pictures automatically deleted when uploading new ones

If you encounter any issues, check the browser console and PHP error logs for detailed error messages.
