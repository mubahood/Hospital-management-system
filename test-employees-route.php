<?php

Route::get('test-employees', function (Request $request) {
    try {
        // Get pagination parameters
        $perPage = min((int) $request->get('per_page', 20), 100);
        $page = (int) $request->get('page', 1);
        $search = $request->get('search', '');
        
        // Build query for employees
        $query = \App\Models\User::where('user_type', 'employee')
            ->where('enterprise_id', 1);
        
        // Apply search if provided
        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone_number_1', 'like', "%{$search}%");
            });
        }
        
        // Get paginated results
        $employees = $query->orderBy('created_at', 'desc')->paginate($perPage, ['*'], 'page', $page);
        
        // Return in the same format as ApiResourceController
        return response()->json([
            'code' => 1,
            'message' => 'Data retrieved successfully',
            'data' => [
                'data' => $employees->items(),
                'pagination' => [
                    'current_page' => $employees->currentPage(),
                    'per_page' => $employees->perPage(),
                    'total' => $employees->total(),
                    'last_page' => $employees->lastPage(),
                    'from' => $employees->firstItem(),
                    'to' => $employees->lastItem(),
                    'has_more' => $employees->hasMorePages(),
                ],
                'meta' => [
                    'model' => 'User',
                    'table' => 'admin_users',
                    'user_id' => null,
                    'enterprise_id' => 1,
                    'filters_applied' => [],
                    'search_term' => $search,
                    'sort_by' => 'created_at',
                    'sort_order' => 'desc',
                ]
            ]
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'code' => 0,
            'message' => 'Error: ' . $e->getMessage(),
            'data' => []
        ], 500);
    }
});
