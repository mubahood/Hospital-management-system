<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Encore\Admin\Auth\Database\Administrator;

$admin = Administrator::where('email', 'admin@admin.com')->first();

if ($admin) {
    echo "✅ Admin user exists: " . $admin->email . PHP_EOL;
    echo "Username: " . $admin->username . PHP_EOL;
    echo "Name: " . $admin->name . PHP_EOL;
} else {
    echo "❌ No admin user found with email admin@admin.com" . PHP_EOL;
}
