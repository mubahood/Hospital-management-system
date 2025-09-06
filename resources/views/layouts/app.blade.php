<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data="{ 
    theme: localStorage.getItem('theme') || 'blue',
    sidebarOpen: false,
    init() {
        this.setTheme(this.theme);
        this.$watch('theme', value => {
            this.setTheme(value);
            localStorage.setItem('theme', value);
        });
    },
    setTheme(theme) {
        document.documentElement.setAttribute('data-theme', theme);
    }
}" :data-theme="theme">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>{{ config('app.name', 'GlobalHealth') }} - Hospital Management</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Alpine.js -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Livewire Styles -->
    @livewireStyles
    
    <!-- Custom CSS for Dynamic Theming -->
    <style>
        :root {
            --primary-50: 239 246 255;
            --primary-100: 219 234 254;
            --primary-200: 191 219 254;
            --primary-300: 147 197 253;
            --primary-400: 96 165 250;
            --primary-500: 59 130 246;
            --primary-600: 37 99 235;
            --primary-700: 29 78 216;
            --primary-800: 30 64 175;
            --primary-900: 30 58 138;
        }
        
        [data-theme="blue"] {
            --primary-50: 239 246 255;
            --primary-100: 219 234 254;
            --primary-200: 191 219 254;
            --primary-300: 147 197 253;
            --primary-400: 96 165 250;
            --primary-500: 59 130 246;
            --primary-600: 37 99 235;
            --primary-700: 29 78 216;
            --primary-800: 30 64 175;
            --primary-900: 30 58 138;
        }
        
        [data-theme="green"] {
            --primary-50: 240 253 244;
            --primary-100: 220 252 231;
            --primary-200: 187 247 208;
            --primary-300: 134 239 172;
            --primary-400: 74 222 128;
            --primary-500: 34 197 94;
            --primary-600: 22 163 74;
            --primary-700: 21 128 61;
            --primary-800: 22 101 52;
            --primary-900: 20 83 45;
        }
        
        [data-theme="purple"] {
            --primary-50: 250 245 255;
            --primary-100: 243 232 255;
            --primary-200: 233 213 255;
            --primary-300: 216 180 254;
            --primary-400: 196 181 253;
            --primary-500: 168 85 247;
            --primary-600: 147 51 234;
            --primary-700: 126 34 206;
            --primary-800: 107 33 168;
            --primary-900: 88 28 135;
        }
        
        [data-theme="orange"] {
            --primary-50: 255 247 237;
            --primary-100: 255 237 213;
            --primary-200: 254 215 170;
            --primary-300: 253 186 116;
            --primary-400: 251 146 60;
            --primary-500: 249 115 22;
            --primary-600: 234 88 12;
            --primary-700: 194 65 12;
            --primary-800: 154 52 18;
            --primary-900: 124 45 18;
        }
        
        [data-theme="red"] {
            --primary-50: 254 242 242;
            --primary-100: 254 226 226;
            --primary-200: 254 202 202;
            --primary-300: 252 165 165;
            --primary-400: 248 113 113;
            --primary-500: 239 68 68;
            --primary-600: 220 38 38;
            --primary-700: 185 28 28;
            --primary-800: 153 27 27;
            --primary-900: 127 29 29;
        }
        
        .bg-primary-50 { background-color: rgb(var(--primary-50)); }
        .bg-primary-100 { background-color: rgb(var(--primary-100)); }
        .bg-primary-500 { background-color: rgb(var(--primary-500)); }
        .bg-primary-600 { background-color: rgb(var(--primary-600)); }
        .bg-primary-700 { background-color: rgb(var(--primary-700)); }
        .text-primary-600 { color: rgb(var(--primary-600)); }
        .text-primary-700 { color: rgb(var(--primary-700)); }
        .border-primary-200 { border-color: rgb(var(--primary-200)); }
        .border-primary-500 { border-color: rgb(var(--primary-500)); }
        .hover\:bg-primary-700:hover { background-color: rgb(var(--primary-700)); }
        .hover\:text-primary-600:hover { color: rgb(var(--primary-600)); }
        .focus\:ring-primary-500:focus { --tw-ring-color: rgb(var(--primary-500)); }
        .focus\:border-primary-500:focus { border-color: rgb(var(--primary-500)); }
    </style>
    
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                }
            }
        }
    </script>
</head>
<body class="font-sans antialiased bg-gray-50 dark:bg-gray-900">
    <div class="min-h-screen">
        {{ $slot ?? '' }}
    </div>
    
    <!-- Livewire Scripts -->
    @livewireScripts
    
    <!-- Notification Component -->
    <div id="notifications" class="fixed top-4 right-4 z-50 space-y-2"></div>
    
    <script>
        // Simple notification system
        window.showNotification = function(message, type = 'success') {
            const notifications = document.getElementById('notifications');
            const notification = document.createElement('div');
            notification.className = `px-4 py-3 rounded-md shadow-lg transition-all duration-300 ${
                type === 'success' ? 'bg-green-500 text-white' : 
                type === 'error' ? 'bg-red-500 text-white' : 
                'bg-blue-500 text-white'
            }`;
            notification.textContent = message;
            
            notifications.appendChild(notification);
            
            setTimeout(() => {
                notification.style.opacity = '0';
                setTimeout(() => notification.remove(), 300);
            }, 3000);
        };
        
        // Listen for Livewire events
        document.addEventListener('livewire:load', function () {
            Livewire.on('notify', (message, type) => {
                showNotification(message, type);
            });
        });
    </script>
</body>
</html>
