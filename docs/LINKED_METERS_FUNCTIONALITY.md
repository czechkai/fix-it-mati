# Linked Meters Page - Full Functionality Documentation

## Overview
The Linked Meters page is now fully functional with complete database integration. Users can manage their water and electricity meters with full CRUD operations.

## Features Implemented

### ✅ 1. View All Linked Meters
- Display all meters in a responsive grid layout
- Show meter details: type, provider, account number, holder name, last bill, address
- Visual distinction between water (blue) and electricity (amber) meters
- Real-time data loaded from PostgreSQL database

### ✅ 2. Add New Meter
- Modal form to link new meters
- Required fields:
  - Meter type (Water/Electricity)
  - Provider (dropdown selection)
  - Account/Meter number
  - Account holder full name
- Optional fields:
  - Alias (with preset buttons for "Home" and "Business")
  - Service address
- Form validation
- Duplicate account number prevention
- Database insertion on submit

### ✅ 3. Edit Meter
- Edit existing meter details
- Loads current data into modal form
- Account number and holder name locked (read-only) during edit
- Can update: provider, alias, address, status
- Database update on submit

### ✅ 4. Delete Meter
- Unlink meter with confirmation dialog
- Permanent deletion from database
- Automatic UI refresh after deletion

### ✅ 5. Toggle Meter Status
- Activate/deactivate meters
- Visual indicator for active/inactive status
- Updates database status field
- Useful for temporarily disabling meters without deletion

### ✅ 6. Statistics Dashboard
- Total meters count
- Water meters count
- Electricity meters count
- Color-coded cards with icons

### ✅ 7. Filter & Search
- Filter by type: All, Water, Electricity
- Real-time search across:
  - Alias
  - Account number
  - Provider
  - Account holder name
  - Address
- Live count updates on filter buttons

### ✅ 8. Responsive Design
- Mobile-friendly layout
- Floating action button on mobile
- Collapsible mobile menu
- Touch-optimized buttons

### ✅ 9. User Experience
- Loading states
- Empty state with call-to-action
- Success/error toast notifications
- Smooth transitions and animations
- Icon-based visual hierarchy

## Database Operations

### Create (POST /api/linked-meters)
```javascript
{
  "meter_type": "water",
  "provider": "Mati Water District",
  "account_number": "092-221-55",
  "account_holder_name": "Juan Dela Cruz",
  "alias": "Home",
  "address": "123 Main St, Mati City",
  "status": "active"
}
```

### Read (GET /api/linked-meters)
- Returns all meters for authenticated user
- Includes all meter details and metadata

### Update (PUT /api/linked-meters/{id})
```javascript
{
  "provider": "Updated Provider",
  "alias": "New Alias",
  "address": "New Address",
  "status": "inactive"
}
```

### Delete (DELETE /api/linked-meters/{id})
- Soft or hard delete depending on configuration
- Verifies user ownership before deletion

## API Endpoints Used

| Method | Endpoint | Purpose |
|--------|----------|---------|
| GET | `/api/linked-meters` | Get all user's meters |
| GET | `/api/linked-meters/{id}` | Get single meter details |
| POST | `/api/linked-meters` | Create new meter link |
| PUT | `/api/linked-meters/{id}` | Update meter details |
| DELETE | `/api/linked-meters/{id}` | Unlink meter |

## Security Features

1. **Authentication Required**: All endpoints require valid user session
2. **Ownership Verification**: Users can only access/modify their own meters
3. **Input Validation**: Server-side validation for all fields
4. **SQL Injection Protection**: Parameterized queries via PDO
5. **XSS Protection**: Output escaping in JavaScript

## Files Modified/Created

### Modified:
- `public/pages/user/linked-meters.php` - Enhanced UI with stats and filters
- `assets/linked-meters.js` - Full CRUD functionality

### Existing (Used):
- `Models/LinkedMeter.php` - Database model
- `Controllers/LinkedMeterController.php` - API controller
- `database/migrations/005_create_linked_meters.sql` - Database schema

## Database Schema

```sql
CREATE TABLE linked_meters (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    user_id UUID NOT NULL REFERENCES users(id),
    provider VARCHAR(100) NOT NULL,
    meter_type VARCHAR(20) NOT NULL CHECK (meter_type IN ('water', 'electricity')),
    account_number VARCHAR(50) NOT NULL,
    account_holder_name VARCHAR(100) NOT NULL,
    alias VARCHAR(100),
    address TEXT,
    status VARCHAR(20) DEFAULT 'active',
    last_reading NUMERIC(10,2),
    last_bill_amount NUMERIC(10,2),
    last_bill_date DATE,
    metadata JSONB,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

## Usage Instructions

### For Users:

1. **Link a New Meter**:
   - Click "Link New Meter" button
   - Select meter type (Water/Electricity)
   - Choose provider from dropdown
   - Enter account number and holder name
   - Optional: Add alias and address
   - Click "Verify & Link Meter"

2. **Edit Meter**:
   - Click "Edit" button on any meter card
   - Update editable fields
   - Click "Update Meter"

3. **Toggle Status**:
   - Click play/pause icon to activate/deactivate
   - Inactive meters shown with reduced opacity

4. **Delete Meter**:
   - Click trash icon
   - Confirm deletion in dialog
   - Meter removed from database

5. **Filter Meters**:
   - Click "All", "Water", or "Electric" filter buttons
   - Use search box to find specific meters

### For Developers:

```javascript
// Example: Programmatically add a meter
const newMeter = await ApiClient.post('/linked-meters', {
    meter_type: 'water',
    provider: 'Mati Water District',
    account_number: '123-456-78',
    account_holder_name: 'Test User',
    alias: 'Test Property',
    status: 'active'
});

// Example: Update meter
const updated = await ApiClient.put('/linked-meters/meter-id', {
    alias: 'Updated Name',
    status: 'inactive'
});

// Example: Delete meter
await ApiClient.delete('/linked-meters/meter-id');
```

## Testing Checklist

- [x] Create new water meter
- [x] Create new electricity meter
- [x] Edit meter details
- [x] Toggle meter status
- [x] Delete meter
- [x] Filter by type
- [x] Search meters
- [x] View statistics
- [x] Responsive on mobile
- [x] Toast notifications work
- [x] Form validation works
- [x] Duplicate prevention works
- [x] Empty state displays correctly
- [x] Loading states show properly
- [x] Database operations persist

## Future Enhancements (Optional)

1. **Bill History**: Track and display bill payment history per meter
2. **Consumption Graphs**: Visual charts showing usage over time
3. **Bill Reminders**: Notifications for upcoming bill due dates
4. **Multi-select Actions**: Bulk activate/deactivate/delete
5. **Export**: Download meter list as PDF/CSV
6. **QR Code**: Generate QR codes for quick meter access
7. **Meter Sharing**: Share meter access with family members
8. **Auto-sync**: Integration with actual utility provider systems

## Troubleshooting

### Meters not loading?
- Check browser console for errors
- Verify database connection in config
- Ensure `linked_meters` table exists
- Check API authentication

### Can't create meter?
- Verify all required fields filled
- Check for duplicate account numbers
- Review server logs for errors
- Ensure database permissions correct

### Filters not working?
- Hard refresh page (Ctrl+F5)
- Check browser console for JS errors
- Verify `lucide.createIcons()` called

## Conclusion

The Linked Meters page is now a fully functional, production-ready feature with complete database integration. All CRUD operations work seamlessly with proper error handling, validation, and user feedback.
