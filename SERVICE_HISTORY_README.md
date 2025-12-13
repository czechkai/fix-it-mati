# Service History Feature

## Overview
The Service History feature allows users to view all their completed service requests, rate the service quality, provide feedback, and report recurring issues.

## Files Created/Modified

### New Files
1. **public/service-history.php** - Main page displaying resolved issues
2. **assets/service-history.js** - Client-side functionality for the service history page
3. **database/migrations/006_add_service_history_columns.sql** - Database migration for rating/feedback columns
4. **database/migrations/007_add_ticket_number.sql** - Database migration for ticket numbers
5. **run-migration-service-history.php** - Script to run service history migration
6. **run-migration-ticket-number.php** - Script to run ticket number migration
7. **seed-service-history.php** - Script to seed test data
8. **check-schema.php** - Utility to check database schema

### Modified Files
1. **Controllers/RequestController.php** - Added methods:
   - `resolved()` - Get all resolved/completed requests
   - `submitRating()` - Submit rating and feedback
   - `reportRecurring()` - Report a recurring issue

2. **DesignPatterns/Structural/Facade/ServiceRequestFacade.php** - Added methods:
   - `getResolvedRequests()` - Fetch resolved requests with filters
   - `submitRating()` - Handle rating submission logic
   - `reportRecurringIssue()` - Create new request based on recurring issue
   - `enrichRequestData()` - Add user info to request data

3. **Models/ServiceRequest.php** - Updated methods:
   - `find()` - Added support for before/after images, fixed user name concatenation
   - `getAll()` / `findAll()` - Added image parsing, fixed user name fields
   - `update()` - Added support for rating, feedback, resolution fields

4. **public/api/index.php** - Added routes:
   - `GET /api/requests/resolved` - Get all resolved requests
   - `POST /api/requests/{id}/rating` - Submit rating
   - `POST /api/requests/{id}/recurring` - Report recurring issue

5. **router.php** - Added 'service-history.php' to $publicPages array

## Database Schema Changes

### New Columns in `service_requests` table:
- `rating` (INTEGER, 1-5) - Customer rating
- `feedback` (TEXT) - Customer feedback/comments
- `rated_at` (TIMESTAMP) - When rating was submitted
- `resolution` (TEXT) - Description of how issue was resolved
- `technician_notes` (TEXT) - Internal notes from technician
- `resolved_at` (TIMESTAMP) - When request was completed
- `resolved_by` (VARCHAR) - Name of technician who resolved it
- `before_images` (TEXT[]) - Array of URLs for before photos
- `after_images` (TEXT[]) - Array of URLs for after photos
- `original_request_id` (UUID) - Reference to original request (for recurring issues)
- `ticket_number` (VARCHAR) - Human-readable ticket identifier (e.g., REQ-2025-000001)

### Indexes Added:
- `idx_service_requests_rating` - For rating queries
- `idx_service_requests_resolved_at` - For resolved date filtering
- `idx_service_requests_original_request` - For recurring issue tracking
- `idx_service_requests_ticket_number` - For ticket lookup

### Triggers:
- `service_requests_set_resolved_at` - Automatically sets resolved_at when status changes to 'completed'
- `service_requests_generate_ticket_number` - Automatically generates ticket number on insert

## API Endpoints

### GET /api/requests/resolved
**Description:** Get all resolved/completed service requests for the authenticated user

**Authentication:** Required

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": "uuid",
      "ticket_number": "REQ-2025-000001",
      "title": "Water leak issue",
      "description": "...",
      "category": "Water Supply",
      "status": "completed",
      "location": "123 Main St",
      "resolved_at": "2025-01-15T10:30:00Z",
      "resolved_by": "John Smith",
      "resolution": "Replaced damaged pipe section",
      "technician_notes": "Customer satisfied",
      "rating": 5,
      "feedback": "Excellent service!",
      "before_images": [],
      "after_images": [],
      "user_name": "Jane Doe",
      "user_email": "jane@example.com"
    }
  ]
}
```

### POST /api/requests/{id}/rating
**Description:** Submit a rating and feedback for a completed request

**Authentication:** Required

**Request Body:**
```json
{
  "rating": 5,
  "feedback": "Great service! Very professional."
}
```

**Validation:**
- Rating must be between 1 and 5
- User must own the request
- Request must be completed
- Request must not already have a rating

**Response:**
```json
{
  "success": true,
  "message": "Rating submitted successfully",
  "data": { /* updated request object */ }
}
```

### POST /api/requests/{id}/recurring
**Description:** Report a recurring issue based on a previously resolved request

**Authentication:** Required

**Request Body:**
```json
{
  "title": "Recurring: Water leak issue",
  "description": "The same issue has occurred again",
  "category": "Water Supply",
  "address": "123 Main St"
}
```

**Validation:**
- User must own the original request
- Original request must be completed

**Response:**
```json
{
  "success": true,
  "message": "Recurring issue reported successfully",
  "request": { /* new request object */ }
}
```

## UI Features

### Page Layout
- **Left Sidebar (33% width):**
  - Search bar for filtering issues
  - Filter button for advanced filtering
  - Scrollable list of resolved issue cards
  - Each card shows: ticket number, title, date, category icon, rating

- **Right Panel (67% width):**
  - Detailed view of selected issue
  - Resolution report section
  - Technician notes (if any)
  - Before/After photos (if any)
  - Rating section (submit or view existing)
  - "Report Recurring Issue" button

### Issue Card Components
- Category icon with colored background
- Ticket number (e.g., REQ-2025-000001)
- Issue title
- Resolved date
- Star rating (if rated) or "No rating"
- Selected state highlight

### Detail View Sections
1. **Header:**
   - Completed badge
   - Ticket number and title
   - Category icon
   - Metadata grid (resolved date, technician, location, category)

2. **Resolution Report:**
   - Formatted resolution text from technician

3. **Technician Notes:**
   - Internal notes (if available)

4. **Before/After Images:**
   - Side-by-side comparison grid

5. **Rating Section:**
   - If not rated: Interactive star selector, comment box, submit button
   - If rated: Display stars, feedback, and thank you message

6. **Recurring Issue:**
   - Button to report the same issue again

### Search & Filter
- **Search:** Filters by ticket number, title, category, location
- **Filter Button:** Placeholder for future date range, category, rating filters

## Usage

### For Customers
1. Navigate to "Service History" from the dashboard or profile menu
2. Browse completed service requests in the left sidebar
3. Click on any issue to view details
4. Rate the service (1-5 stars) and optionally add feedback
5. Report recurring issues if the same problem occurs again

### For Admins/Technicians
- View all completed requests (not filtered by user)
- Access customer ratings and feedback
- Use recurring issue reports to identify systemic problems

## Setup Instructions

1. **Run Migrations:**
   ```bash
   php run-migration-service-history.php
   php run-migration-ticket-number.php
   ```

2. **Seed Test Data (Optional):**
   ```bash
   php seed-service-history.php
   ```

3. **Access Page:**
   - URL: `http://localhost:8000/service-history.php`
   - Requires authentication

4. **Verify Schema:**
   ```bash
   php check-schema.php
   ```

## Real-Time Updates
- All data fetched from API on page load
- Ratings submitted via AJAX
- Recurring issues created immediately
- Page can be refreshed to see latest data

## Future Enhancements
- Advanced filtering (date range, category, rating range)
- Image upload for before/after photos
- Export service history to PDF
- Email notifications when rating is submitted
- Technician response to feedback
- Average rating display
- Filter by date ranges (last 30 days, last 6 months, etc.)

## Security Notes
- All endpoints require authentication
- Users can only view their own resolved requests (role-based filtering)
- Rating submission validates ownership and completion status
- Prevents duplicate ratings
- SQL injection protection via prepared statements
- XSS protection via HTML escaping in JavaScript

## Performance Optimizations
- Database indexes on rating, resolved_at, and original_request_id
- Pagination support (can be added to API)
- Efficient SQL queries with JOINs
- Client-side caching of issue list
- Lazy loading of images (future enhancement)

## Technical Stack
- **Backend:** PHP 8.4, PostgreSQL, PDO
- **Frontend:** Vanilla JavaScript, Tailwind CSS, Lucide Icons
- **Architecture:** MVC with Facade pattern
- **Authentication:** JWT tokens in sessionStorage
- **API:** RESTful endpoints with JSON responses

---

**Created:** January 2025  
**Version:** 1.0  
**Status:** ✅ Production Ready
