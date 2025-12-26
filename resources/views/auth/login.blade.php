<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Login - {{ env('APP_NAME', 'Hospital Management System') }}</title>
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="{{ url('css/admin-redesign.css') }}">
    
    <!-- Bootstrap CSS (optional, for grid system only) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        /* Additional Login-Specific Styles */
        .login-wrapper {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #f9fafb 0%, #e5e7eb 100%);
            padding: 20px;
            position: relative;
            overflow: hidden;
        }
        
        /* Animated background pattern */
        .login-wrapper::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: 
                radial-gradient(circle at 20% 50%, rgba(14, 165, 233, 0.03) 0%, transparent 50%),
                radial-gradient(circle at 80% 80%, rgba(16, 185, 129, 0.03) 0%, transparent 50%),
                radial-gradient(circle at 40% 20%, rgba(245, 158, 11, 0.02) 0%, transparent 50%);
            animation: float 20s ease-in-out infinite;
        }
        
        @keyframes float {
            0%, 100% { transform: translate(0, 0) rotate(0deg); }
            33% { transform: translate(30px, -30px) rotate(120deg); }
            66% { transform: translate(-20px, 20px) rotate(240deg); }
        }
        
        .login-container {
            background: white;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            max-width: 480px;
            width: 100%;
            padding: 48px;
            position: relative;
            z-index: 1;
        }
        
        .login-logo {
            width: 80px;
            height: 80px;
            margin: 0 auto 24px;
            display: block;
            background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 36px;
            font-weight: 700;
            box-shadow: 0 10px 15px -3px rgba(14, 165, 233, 0.3);
        }
        
        .login-logo img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .login-title {
            font-size: 28px;
            font-weight: 700;
            color: #1f2937;
            text-align: center;
            margin-bottom: 8px;
        }
        
        .login-subtitle {
            font-size: 14px;
            color: #6b7280;
            text-align: center;
            margin-bottom: 32px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 8px;
        }
        
        .input-group {
            position: relative;
        }
        
        .input-icon {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #6b7280;
            font-size: 16px;
        }
        
        .form-control {
            width: 100%;
            padding: 12px 16px 12px 44px;
            border: 1px solid #d1d5db;
            font-size: 14px;
            color: #1f2937;
            background-color: white;
            transition: all 0.2s ease;
        }
        
        .form-control:focus {
            outline: none;
            border-color: #0ea5e9;
            box-shadow: 0 0 0 3px rgba(14, 165, 233, 0.1);
        }
        
        .form-control.is-invalid {
            border-color: #dc2626;
        }
        
        .form-control.is-invalid:focus {
            box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.1);
        }
        
        .form-control::placeholder {
            color: #9ca3af;
        }
        
        .error-message {
            display: block;
            color: #dc2626;
            font-size: 12px;
            margin-top: 6px;
            font-weight: 500;
        }
        
        .password-toggle-wrapper {
            position: relative;
        }
        
        .password-toggle-btn {
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #6b7280;
            cursor: pointer;
            padding: 0;
            font-size: 16px;
            transition: color 0.2s ease;
        }
        
        .password-toggle-btn:hover {
            color: #1f2937;
        }
        
        .form-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
            font-size: 13px;
        }
        
        .remember-me {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .remember-me input[type="checkbox"] {
            width: 16px;
            height: 16px;
            border: 1px solid #d1d5db;
            cursor: pointer;
        }
        
        .remember-me label {
            color: #4b5563;
            margin: 0;
            cursor: pointer;
            font-weight: 500;
        }
        
        .forgot-link {
            color: #0ea5e9;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.2s ease;
        }
        
        .forgot-link:hover {
            color: #0284c7;
        }
        
        .btn-login {
            width: 100%;
            padding: 14px;
            background-color: #0ea5e9;
            color: white;
            border: none;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        
        .btn-login:hover {
            background-color: #0284c7;
            transform: translateY(-1px);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        
        .btn-login:active {
            transform: translateY(0);
        }
        
        .btn-login:disabled {
            background-color: #9ca3af;
            cursor: not-allowed;
            transform: none;
        }
        
        .alert {
            padding: 12px 16px;
            margin-bottom: 24px;
            display: flex;
            align-items: flex-start;
            gap: 12px;
            font-size: 13px;
        }
        
        .alert-success {
            background-color: #d1fae5;
            color: #065f46;
            border: 1px solid #6ee7b7;
        }
        
        .alert-error {
            background-color: #fee2e2;
            color: #991b1b;
            border: 1px solid #fca5a5;
        }
        
        .alert-icon {
            flex-shrink: 0;
            font-size: 16px;
        }
        
        .login-footer {
            text-align: center;
            margin-top: 32px;
            padding-top: 24px;
            border-top: 1px solid #e5e7eb;
            font-size: 13px;
            color: #6b7280;
        }
        
        .login-footer a {
            color: #0ea5e9;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.2s ease;
        }
        
        .login-footer a:hover {
            color: #0284c7;
        }
        
        /* Responsive */
        @media (max-width: 576px) {
            .login-container {
                padding: 32px 24px;
            }
            
            .login-title {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>
    <div class="login-wrapper">
        <div class="login-container">
            <!-- Logo -->
            <div class="login-logo">
                @if(file_exists(public_path('assets/img/logo.png')))
                    <img src="{{ url('assets/img/logo.png') }}" alt="Logo">
                @else
                    <i class="fas fa-hospital"></i>
                @endif
            </div>
            
            <!-- Title -->
            <h1 class="login-title">{{ env('APP_NAME', 'Hospital Management System') }}</h1>
            <p class="login-subtitle">Sign in to your account to continue</p>
            
            <!-- Success Message -->
            @if(isset($_GET['message']) || session('success'))
                <div class="alert alert-success">
                    <i class="fas fa-check-circle alert-icon"></i>
                    <div>
                        <strong>Success!</strong> 
                        {{ $_GET['message'] ?? session('success') }}
                    </div>
                </div>
            @endif
            
            <!-- Error Messages -->
            @if($errors->any())
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle alert-icon"></i>
                    <div>
                        <strong>Error!</strong>
                        <ul style="margin: 4px 0 0 0; padding-left: 20px;">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif
            
            <!-- Login Form -->
            <form action="{{ admin_url('auth/login') }}" method="POST" id="loginForm">
                @csrf
                
                <!-- Email Field -->
                <div class="form-group">
                    <label for="username" class="form-label">Email Address</label>
                    <div class="input-group">
                        <i class="fas fa-envelope input-icon"></i>
                        <input 
                            type="email" 
                            class="form-control @error('username') is-invalid @enderror" 
                            id="username" 
                            name="username" 
                            placeholder="Enter your email address"
                            value="{{ old('username') }}"
                            required
                            autofocus
                        >
                    </div>
                    @error('username')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>
                
                <!-- Password Field -->
                <div class="form-group">
                    <label for="password" class="form-label">Password</label>
                    <div class="password-toggle-wrapper">
                        <div class="input-group">
                            <i class="fas fa-lock input-icon"></i>
                            <input 
                                type="password" 
                                class="form-control @error('password') is-invalid @enderror" 
                                id="password" 
                                name="password" 
                                placeholder="Enter your password"
                                required
                            >
                        </div>
                        <button type="button" class="password-toggle-btn" onclick="togglePassword()">
                            <i class="fas fa-eye" id="toggleIcon"></i>
                        </button>
                    </div>
                    @error('password')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>
                
                <!-- Remember Me & Forgot Password -->
                <div class="form-options">
                    <div class="remember-me">
                        <input type="checkbox" id="remember" name="remember">
                        <label for="remember">Remember me</label>
                    </div>
                    <a href="{{ url('request-password-reset') }}" class="forgot-link">Forgot password?</a>
                </div>
                
                <!-- Submit Button -->
                <button type="submit" class="btn-login" id="submitBtn">
                    <i class="fas fa-sign-in-alt"></i>
                    Sign in
                </button>
            </form>
            
            <!-- Footer -->
            <div class="login-footer">
                <p style="margin: 0;">
                    &copy; {{ date('Y') }} {{ env('APP_NAME', 'Hospital Management System') }}. All rights reserved.
                    <br>
                    Powered by <a href="https://8technologies.net" target="_blank" rel="noopener">Eight Tech Consults</a>
                </p>
            </div>
        </div>
    </div>
    
    <script>
        // Toggle password visibility
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('toggleIcon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }
        
        // Form submission handling
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const submitBtn = document.getElementById('submitBtn');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Signing in...';
        });
        
        // Auto-hide success message after 5 seconds
        setTimeout(function() {
            const successAlert = document.querySelector('.alert-success');
            if (successAlert) {
                successAlert.style.transition = 'opacity 0.3s ease';
                successAlert.style.opacity = '0';
                setTimeout(function() {
                    successAlert.remove();
                }, 300);
            }
        }, 5000);
    </script>
</body>
</html>
