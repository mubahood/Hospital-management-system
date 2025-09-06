<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Support\Facades\Hash;

// Check if admin already exists
$existingAdmin = Administrator::where('email', 'admin@admin.com')->first();

if ($existingAdmin) {
    echo "✅ Admin user already exists: " . $existingAdmin->email . PHP_EOL;
    exit;
}

// Create new admin user
$admin = new Administrator();
$admin->username = 'admin';
$admin->name = 'Administrator';
$admin->email = 'admin@admin.com';
$admin->password = Hash::make('password');
$admin->avatar = null;
$admin->save();

echo "✅ Admin user created successfully!" . PHP_EOL;
echo "Email: admin@admin.com" . PHP_EOL;
echo "Password: password" . PHP_EOL;
