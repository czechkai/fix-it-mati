# Linked Meters Testing Guide

## Quick Test Steps

### 1. Start the Server
```bash
start.bat
```
or
```bash
php -S localhost:8000 router.php
```

### 2. Access the Page
Navigate to: `http://localhost:8000/linked-meters.php`

### 3. Test Create Functionality

**Test Case 1: Add Water Meter**
1. Click "Link New Meter" button
2. Select "Water" meter type
3. Provider: "Mati Water District"
4. Account Number: "WTR-2024-001"
5. Account Holder: "Test User"
6. Alias: "Home"
7. Address: "123 Test Street, Mati City"
8. Click "Verify & Link Meter"
9. ✅ Should show success toast
10. ✅ Meter should appear in grid
11. ✅ Statistics should update

**Test Case 2: Add Electricity Meter**
1. Click "Link New Meter" button
2. Select "Electricity" meter type
3. Provider: "Davao Light"
4. Account Number: "ELEC-2024-001"
5. Account Holder: "Test User"
6. Alias: "Office"
7. Click "Verify & Link Meter"
8. ✅ Should show success toast
9. ✅ Meter should appear in grid with amber color
10. ✅ Statistics should show 1 electric meter

### 4. Test Read Functionality

**Test Case 3: View All Meters**
1. After adding meters, they should automatically display
2. ✅ Statistics show correct counts
3. ✅ Each meter card shows all details
4. ✅ Icons are correct (droplet for water, lightning for electric)

**Test Case 4: Filter Meters**
1. Click "Water" filter button
2. ✅ Only water meters shown
3. Click "Electric" filter button
4. ✅ Only electric meters shown
5. Click "All" filter button
6. ✅ All meters shown

**Test Case 5: Search Meters**
1. Type "Home" in search box
2. ✅ Only meters with "Home" in any field shown
3. Type account number
4. ✅ Specific meter found

### 5. Test Update Functionality

**Test Case 6: Edit Meter**
1. Click "Edit" button on any meter card
2. ✅ Modal opens with pre-filled data
3. ✅ Meter type radio buttons are disabled
4. ✅ Account number field is read-only
5. ✅ Account holder field is read-only
6. Change alias to "Updated Home"
7. Change address
8. Click "Update Meter"
9. ✅ Success toast appears
10. ✅ Card updates with new information
11. ✅ Database persists changes

**Test Case 7: Toggle Status**
1. Click play/pause icon on meter card
2. ✅ Status changes (active ↔ inactive)
3. ✅ Visual feedback (opacity changes)
4. ✅ Status badge updates
5. Click again
6. ✅ Status toggles back
7. ✅ Database reflects status change

### 6. Test Delete Functionality

**Test Case 8: Delete Meter**
1. Click trash icon on a meter card
2. ✅ Confirmation dialog appears
3. Click "Cancel"
4. ✅ Meter remains
5. Click trash icon again
6. Click "OK"
7. ✅ Success toast appears
8. ✅ Meter removed from grid
9. ✅ Statistics updated
10. ✅ Database record deleted

### 7. Test Edge Cases

**Test Case 9: Empty State**
1. Delete all meters
2. ✅ Empty state message appears
3. ✅ "Link Your First Meter" button shown
4. ✅ Statistics section hidden

**Test Case 10: Duplicate Prevention**
1. Try to add meter with same account number
2. ✅ Error message shown
3. ✅ Meter not created

**Test Case 11: Validation**
1. Try to submit form without required fields
2. ✅ Browser validation prevents submit
3. Fill only meter type
4. ✅ Server returns validation error

**Test Case 12: Responsive Design**
1. Resize browser to mobile size
2. ✅ Floating action button appears
3. ✅ Grid becomes single column
4. ✅ Stats cards stack vertically
5. ✅ Modal is mobile-friendly

## Database Verification

### Check Data Persistence

After performing operations, verify in database:

```sql
-- View all linked meters
SELECT * FROM linked_meters ORDER BY created_at DESC;

-- Count meters by type
SELECT meter_type, COUNT(*) as count 
FROM linked_meters 
GROUP BY meter_type;

-- Check specific user's meters
SELECT * FROM linked_meters 
WHERE user_id = 'your-user-id';
```

## Expected Behavior Summary

| Action | Database | UI | API |
|--------|----------|----|----|
| Create | INSERT | Add card | POST 201 |
| Read | SELECT | Display grid | GET 200 |
| Update | UPDATE | Refresh card | PUT 200 |
| Delete | DELETE | Remove card | DELETE 200 |
| Filter | SELECT | Filter display | GET 200 |
| Search | - | Client-side | - |
| Toggle | UPDATE | Update badge | PUT 200 |

## Success Criteria

- ✅ All CRUD operations work without errors
- ✅ UI updates reflect database changes immediately
- ✅ No console errors
- ✅ Toast notifications appear for all actions
- ✅ Data persists after page refresh
- ✅ Filters and search work correctly
- ✅ Responsive design works on mobile
- ✅ Loading states show appropriately
- ✅ Form validation prevents invalid data

## Common Issues & Solutions

### Issue: Meters not loading
**Solution**: 
- Check browser console for errors
- Verify logged in user
- Check database connection
- Ensure `linked_meters` table exists

### Issue: Create fails
**Solution**:
- Check form validation
- Verify all required fields filled
- Check for duplicate account numbers
- Review server error logs

### Issue: Update not persisting
**Solution**:
- Hard refresh browser (Ctrl+F5)
- Check network tab for API response
- Verify database UPDATE query runs
- Check user permissions

### Issue: UI not updating
**Solution**:
- Check if `lucide.createIcons()` is called
- Verify event listeners attached
- Check for JavaScript errors
- Clear browser cache

## API Testing with cURL

```bash
# Create meter
curl -X POST http://localhost:8000/api/linked-meters \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -d '{
    "meter_type": "water",
    "provider": "Mati Water District",
    "account_number": "TEST-001",
    "account_holder_name": "Test User",
    "alias": "Test Home",
    "status": "active"
  }'

# Get all meters
curl http://localhost:8000/api/linked-meters \
  -H "Authorization: Bearer YOUR_TOKEN"

# Update meter
curl -X PUT http://localhost:8000/api/linked-meters/METER_ID \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -d '{"alias": "Updated Name"}'

# Delete meter
curl -X DELETE http://localhost:8000/api/linked-meters/METER_ID \
  -H "Authorization: Bearer YOUR_TOKEN"
```

## Performance Considerations

- Page should load within 2 seconds
- API responses should be under 500ms
- UI should update within 100ms after API response
- No memory leaks with repeated operations
- Smooth animations and transitions

## Conclusion

If all test cases pass, the Linked Meters functionality is fully operational and ready for production use. The page provides a complete meter management system with all CRUD operations properly integrated with the database.
