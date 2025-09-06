<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hospital Admin - Login</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
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
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Inter', sans-serif;
        }
        
        .login-form {
            background: var(--surface);
            border-radius: 16px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(59, 130, 246, 0.3);
        }
        
        .form-input {
            transition: all 0.3s ease;
        }
        
        .form-input:focus {
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
            border-color: var(--primary-color);
        }
    </style>
</head>
<body class="flex items-center justify-center min-h-screen p-4">
    <div class="login-form w-full max-w-md p-8">
        <div class="text-center mb-8">
            <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-purple-600 rounded-full mx-auto mb-4 flex items-center justify-center">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-gray-900 mb-2">Hospital Admin</h1>
            <p class="text-gray-600">Sign in to access your dashboard</p>
        </div>

        <?php if (isset($_GET['error'])): ?>
            <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
                <div class="flex">
                    <svg class="w-5 h-5 text-red-400 mt-0.5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                    </svg>
                    <p class="text-sm text-red-800"><?php echo htmlspecialchars($_GET['error']); ?></p>
                </div>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['message'])): ?>
            <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg">
                <div class="flex">
                    <svg class="w-5 h-5 text-green-400 mt-0.5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    <p class="text-sm text-green-800"><?php echo htmlspecialchars($_GET['message']); ?></p>
                </div>
            </div>
        <?php endif; ?>

        <form action="auth.php" method="POST" class="space-y-6">
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email Address</label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    required 
                    class="form-input w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none"
                    placeholder="Enter your email"
                >
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-2">Password</label>
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    required 
                    class="form-input w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none"
                    placeholder="Enter your password"
                >
            </div>

            <div class="flex items-center justify-between">
                <label class="flex items-center">
                    <input type="checkbox" name="remember" class="rounded border-gray-300 text-blue-600">
                    <span class="ml-2 text-sm text-gray-600">Remember me</span>
                </label>
                <a href="#" class="text-sm text-blue-600 hover:text-blue-500">Forgot password?</a>
            </div>

            <button 
                type="submit" 
                class="btn-primary w-full py-3 px-4 text-white font-medium rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
            >
                Sign In
            </button>
        </form>

        <div class="mt-8 text-center">
            <p class="text-sm text-gray-600">
                Don't have an account? 
                <a href="#" class="text-blue-600 hover:text-blue-500 font-medium">Contact Administrator</a>
            </p>
        </div>
    </div>

    <script>
        // Add some interactive effects
        document.querySelector('form').addEventListener('submit', function(e) {
            const button = this.querySelector('button');
            button.innerHTML = `
                <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Signing in...
            `;
        });
    </script>
</body>
</html>
