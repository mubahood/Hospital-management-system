<?php
require __DIR__ . '/bootstrap/app.php';

use App\Models\User;
use Illuminate\Support\Facades\DB;

// Test the User model and see the structure
echo "=== Testing Employee/User Model ===\n";

try {
    // Get first few users with user_type = employee
    $employees = User::where('user_type', 'employee')
        ->where('enterprise_id', 1)
        ->orderBy('created_at', 'desc')
        ->limit(5)
        ->get();
    
    echo "Found " . $employees->count() . " employees\n";
    
    if ($employees->count() > 0) {
        $first = $employees->first();
        echo "First employee structure:\n";
        echo "ID: " . $first->id . "\n";
        echo "Name: " . $first->name . "\n";
        echo "First Name: " . $first->first_name . "\n";
        echo "Last Name: " . $first->last_name . "\n";
        echo "Email: " . $first->email . "\n";
        echo "Phone: " . $first->phone_number_1 . "\n";
        echo "Sex: " . $first->sex . "\n";
        echo "User Type: " . $first->user_type . "\n";
        echo "Status: " . $first->status . "\n";
        echo "Created At: " . $first->created_at . "\n";
    }
    
    // Test pagination
    echo "\n=== Testing Pagination ===\n";
    $paginatedEmployees = User::where('user_type', 'employee')
        ->where('enterprise_id', 1)
        ->orderBy('created_at', 'desc')
        ->paginate(20);
    
    echo "Current Page: " . $paginatedEmployees->currentPage() . "\n";
    echo "Per Page: " . $paginatedEmployees->perPage() . "\n";
    echo "Total: " . $paginatedEmployees->total() . "\n";
    echo "Last Page: " . $paginatedEmployees->lastPage() . "\n";
    echo "From: " . $paginatedEmployees->firstItem() . "\n";
    echo "To: " . $paginatedEmployees->lastItem() . "\n";
    echo "Has More Pages: " . ($paginatedEmployees->hasMorePages() ? 'Yes' : 'No') . "\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

echo "\n=== Test Complete ===\n";
