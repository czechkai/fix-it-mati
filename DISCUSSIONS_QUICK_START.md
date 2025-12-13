# 🎯 Quick Start: Testing Discussions Feature

## Prerequisites
✅ Database is set up with migrations
✅ PHP server is running on port 8000
✅ Users exist in database

## Step 1: Ensure You're Logged In

Visit any of these pages (they'll redirect to login if needed):
- http://localhost:8000/login.php

Use one of the test accounts:
- Email: `test.customer@example.com`
- Password: `password123`

## Step 2: Access Discussions

Once logged in, visit:
```
http://localhost:8000/discussions.php
```

## Step 3: Explore Features

### View Discussions
- Browse the list of discussions
- See real-time upvote counts
- Notice which discussions you've upvoted (blue button)
- Check answered status badges

### Filter & Sort
- Click category buttons on the left sidebar
- Use sort tabs: Newest, Top Rated, Unanswered
- Try the search box

### Create Discussion
1. Click "New Discussion" button (green, top right)
2. Select category from dropdown
3. Enter title and content
4. Click "Post Discussion"
5. Your discussion appears immediately!

### View Discussion Details
1. Click any discussion card
2. You'll see:
   - Full discussion content
   - All comments
   - Upvote button
   - Comment form

### Interact with Discussions
- **Upvote**: Click thumbs-up button (toggles blue when active)
- **Comment**: Type in the comment box and click "Post Comment"
- **Mark Solution**: If you own the discussion, click "Mark as solution" on helpful comments
- **Delete**: If you own it, click the trash icon

## Step 4: Watch Real-Time Updates

### Auto-Refresh
- Discussions list refreshes every 30 seconds
- Discussion detail refreshes every 15 seconds
- Or click the refresh icon manually

### Instant Feedback
- Upvotes update immediately
- Comments appear right away
- Solution badges show instantly

## Testing Checklist

### Basic Operations
- [ ] View discussions list
- [ ] Filter by category
- [ ] Sort by different options
- [ ] Search for keywords
- [ ] Create new discussion
- [ ] View discussion detail
- [ ] Add comment
- [ ] Upvote discussion
- [ ] Mark comment as solution
- [ ] Delete own discussion

### Real-Time Features
- [ ] Auto-refresh shows new discussions
- [ ] Upvote state persists after refresh
- [ ] Comments appear without page reload
- [ ] Indicators update in real-time

## Sample Data

If you need test data, run:
```bash
# Create initial discussions
php seed-discussions.php

# Add comments and upvotes
php seed-discussion-interactions.php

# Check what's in the database
php check-discussions-tables.php
```

## Troubleshooting

### Not logged in?
- Clear sessionStorage in browser DevTools
- Go to login.php and sign in again

### No discussions showing?
- Check console for API errors
- Ensure PHP server is running
- Run seed scripts to create test data

### API errors?
- Check browser Network tab
- Verify JWT token in sessionStorage
- Check PHP error logs

## Database Verification

To check the database directly:
```bash
php check-discussions-tables.php
```

This shows:
- Total discussions count
- Total comments count
- Total upvotes count
- Recent discussions summary

## Features to Test

### Discussion List Page
1. ✅ Discussions load automatically
2. ✅ Real-time update indicator
3. ✅ Category filters work
4. ✅ Sorting changes order
5. ✅ Search filters results
6. ✅ Create new discussion modal
7. ✅ Upvote buttons highlight when clicked
8. ✅ Answered badges show correctly
9. ✅ Comment counts display
10. ✅ Clicking opens detail page

### Discussion Detail Page
1. ✅ Full discussion displays
2. ✅ All comments load
3. ✅ Can add new comment
4. ✅ Upvote toggles work
5. ✅ Solution badges show
6. ✅ Mark solution button (if owner)
7. ✅ Delete button (if owner)
8. ✅ Staff/Admin badges on comments
9. ✅ Time stamps show correctly
10. ✅ Auto-refresh updates comments

## Pro Tips

### For Testing
- Open two browser windows (different users)
- Create discussion in one, see it appear in the other
- Upvote from different accounts
- Comment from multiple users

### For Development
- Check browser console for logs
- Network tab shows API calls
- Vue DevTools can inspect state
- PHP error logs show backend issues

## Success Criteria

The feature is working correctly when:
- ✅ All discussions load and display
- ✅ Can create new discussions
- ✅ Comments post successfully
- ✅ Upvotes toggle and count correctly
- ✅ Solutions can be marked
- ✅ Real-time updates work
- ✅ All database operations persist
- ✅ User permissions respected

## 🎉 You're All Set!

The discussions feature is fully functional. All actions reflect in the database immediately, and real-time updates keep everyone in sync.

**Next Steps:**
- Invite team members to test
- Create real discussions
- Monitor performance
- Gather user feedback

---
**Documentation**: See `DISCUSSIONS_COMPLETE.md` for full technical details
