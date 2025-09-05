<?php

use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Support\Facades\Hash;

// Create test admin user if not exists
$email = 'admin@example.com';
$admin = Administrator::where('email', $email)->first();

if (!$admin) {
    $admin = new Administrator();
    $admin->username = 'admin';
    $admin->name = 'Admin User';
    $admin->email = $email;
    $admin->password = Hash::make('password');
    $admin->company_id = 1;
    $admin->save();
    echo "Admin user created: {$email} / password\n";
} else {
    echo "Admin user already exists: {$email}\n";
}

echo "Total admin users: " . Administrator::count() . "\n";
