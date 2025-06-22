# Route Conflict Resolution Documentation

## Issue Resolution: Student Dashboard Route Conflict

**Date**: 2025-06-20  
**Issue**: `Illuminate\Routing\Exceptions\UrlGenerationException` - Missing required parameter for students.progress route

### Root Cause
Route name collision between two different routes both named `students.progress`:

1. **Student Route**: `/my-progress` (no parameters) - for students to view their own progress
2. **Admin Route**: `students/{student}/progress` (requires student parameter) - for admins to view specific student progress

### Solution Applied
Changed the admin route name to avoid conflict:

**Before**:
```php
Route::get('students/{student}/progress', [StudentController::class, 'progress'])->name('students.progress');
```

**After**:
```php
Route::get('students/{student}/progress', [StudentController::class, 'progress'])->name('students.show-progress');
```

### Result
- ✅ Student dashboard now works correctly
- ✅ Student progress route: `students.progress` → `/my-progress`
- ✅ Admin progress route: `students.show-progress` → `students/{student}/progress`
- ✅ All other student dashboard routes functioning properly

### Verified Routes
All student dashboard routes now working:
- `students.progress` → `/my-progress`
- `students.enrolments` → `/my-enrolments`  
- `students.grades` → `/my-grades`
- `extension-requests.index` → `/my-extensions`
- `students.profile` → `/my-profile`

### Prevention
To avoid similar issues in the future:
1. Use consistent naming patterns for student vs admin routes
2. Consider prefixing admin routes with `admin.` or similar
3. Use descriptive route names that indicate their purpose
4. Run `php artisan route:list` to check for duplicate route names

### Files Modified
- `/routes/web.php` (line 212) - Changed route name from `students.progress` to `students.show-progress`

### Commands Used
```bash
php artisan route:clear  # Clear route cache after changes
php artisan route:list | grep progress  # Verify route changes
```