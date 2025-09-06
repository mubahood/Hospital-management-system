<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['admin_user_id'])) {
    header('Location: login.php');
    exit;
}

// Bootstrap Laravel for Livewire components
require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';

// Create and handle HTTP request
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$request = Illuminate\Http\Request::capture();

// Set up the Laravel environment properly
$app->instance('request', $request);

// Start Laravel application
$response = $kernel->handle($request);

$userName = $_SESSION['admin_user_name'] ?? 'Admin User';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hospital Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <?php echo app('livewire')->styles(); ?>
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <style>
        :root {
            --primary-color: #3b82f6;
            --primary-dark: #1d4ed8;
            --secondary-color: #64748b;
            --success-color: #10b981;
            --warning-color: #f59e0b;
            --danger-color: #ef4444;
            --background: #f8fafc;
            --surface: #ffffff;
            --text-primary: #1e293b;
            --text-secondary: #64748b;
            --border-color: #e2e8f0;
        }

        body {
            background-color: var(--background);
            color: var(--text-primary);
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>

<body>
    <div x-data="{ sidebarOpen: false }" class="flex h-screen bg-gray-100">
        <!-- Sidebar -->
        <div :class="{'translate-x-0': sidebarOpen, '-translate-x-full': !sidebarOpen}"
            class="fixed inset-y-0 left-0 z-50 w-64 bg-white shadow-lg transform transition-transform duration-300 ease-in-out lg:translate-x-0 lg:static lg:inset-0">
            <div class="flex items-center justify-center h-16 bg-gradient-to-r from-blue-600 to-purple-600">
                <h1 class="text-xl font-bold text-white">Hospital Admin</h1>
            </div>

            <nav class="mt-8">
                <a href="#" class="flex items-center px-6 py-3 text-gray-700 bg-blue-50 border-r-4 border-blue-600">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"></path>
                    </svg>
                    Dashboard
                </a>

                <a href="#" class="flex items-center px-6 py-3 text-gray-600 hover:bg-gray-50 hover:text-gray-900">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                    Patients
                </a>

                <a href="#" class="flex items-center px-6 py-3 text-gray-600 hover:bg-gray-50 hover:text-gray-900">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3a2 2 0 012-2h4a2 2 0 012 2v4m-6 9l6-6m0 0v6m0-6H6"></path>
                    </svg>
                    Appointments
                </a>

                <a href="#" class="flex items-center px-6 py-3 text-gray-600 hover:bg-gray-50 hover:text-gray-900">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                    </svg>
                    Medical Records
                </a>

                <a href="events.php" class="flex items-center px-6 py-3 text-gray-600 hover:bg-gray-50 hover:text-gray-900">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16l2.879-2.879m0 0a3 3 0 104.243-4.242 3 3 0 00-4.243 4.242zM21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Events
                </a>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Top Header -->
            <header class="bg-white shadow-sm border-b border-gray-200">
                <div class="flex items-center justify-between px-6 py-4">
                    <div class="flex items-center">
                        <button @click="sidebarOpen = !sidebarOpen" class="lg:hidden">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                            </svg>
                        </button>
                        <h2 class="ml-4 text-xl font-semibold text-gray-900">Dashboard</h2>
                    </div>

                    <div class="flex items-center space-x-4">
                        <span class="text-gray-700">Welcome, <?php echo htmlspecialchars($userName); ?></span>
                        <a href="logout.php" class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition-colors">
                            Logout
                        </a>
                    </div>
                </div>
            </header>

            <!-- Dashboard Content - NOW WITH REAL LIVEWIRE! -->
            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-100">
                <?php echo app('livewire')->mount('admin.dashboard'); ?>
            </main>
        </div>
    </div>

    <!-- Success Message -->
    <div class="fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg">
        <div class="flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
            🎉 Real Laravel Livewire is now working!
        </div>
    </div>

    <?php echo app('livewire')->scripts(); ?>
</body>

</html>
