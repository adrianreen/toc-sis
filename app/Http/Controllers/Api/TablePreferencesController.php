<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserTablePreference;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TablePreferencesController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Get user's table preferences
     */
    public function show(Request $request, $tableName)
    {
        $preference = UserTablePreference::getForUserAndTable(Auth::id(), $tableName);
        
        if (!$preference) {
            // Return default configuration
            $defaultColumns = UserTablePreference::getDefaultStudentColumns();
            $defaultVisible = UserTablePreference::getDefaultVisibleColumns();
            
            return response()->json([
                'columns' => $defaultColumns,
                'visible_columns' => $defaultVisible,
                'column_order' => $defaultVisible,
                'column_widths' => [],
                'sort_preferences' => ['field' => 'created_at', 'direction' => 'desc'],
            ]);
        }
        
        return response()->json([
            'columns' => UserTablePreference::getDefaultStudentColumns(),
            'visible_columns' => $preference->visible_columns,
            'column_order' => $preference->column_order,
            'column_widths' => $preference->column_widths ?? [],
            'sort_preferences' => $preference->sort_preferences ?? ['field' => 'created_at', 'direction' => 'desc'],
        ]);
    }

    /**
     * Save user's table preferences
     */
    public function store(Request $request, $tableName)
    {
        $validated = $request->validate([
            'visible_columns' => 'required|array',
            'column_order' => 'required|array',
            'column_widths' => 'nullable|array',
            'sort_preferences' => 'nullable|array',
        ]);

        $preference = UserTablePreference::savePreferences(
            Auth::id(),
            $tableName,
            $validated
        );

        return response()->json([
            'success' => true,
            'preference' => $preference,
        ]);
    }

    /**
     * Reset to default preferences
     */
    public function reset(Request $request, $tableName)
    {
        UserTablePreference::where('user_id', Auth::id())
            ->where('table_name', $tableName)
            ->delete();

        $defaultColumns = UserTablePreference::getDefaultStudentColumns();
        $defaultVisible = UserTablePreference::getDefaultVisibleColumns();
        
        return response()->json([
            'success' => true,
            'columns' => $defaultColumns,
            'visible_columns' => $defaultVisible,
            'column_order' => $defaultVisible,
            'column_widths' => [],
            'sort_preferences' => ['field' => 'created_at', 'direction' => 'desc'],
        ]);
    }

    /**
     * Get available column definitions
     */
    public function columns(Request $request, $tableName)
    {
        if ($tableName === 'students') {
            return response()->json([
                'columns' => UserTablePreference::getDefaultStudentColumns(),
            ]);
        }

        return response()->json(['columns' => []]);
    }
}
