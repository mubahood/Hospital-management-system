<div class="min-h-screen flex items-center justify-center bg-gray-50 dark:bg-gray-900 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <!-- Header -->
        <div class="text-center">
            <h2 class="text-3xl font-bold text-gray-900 dark:text-white">
                <span class="text-primary-600">Global</span>Health
            </h2>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                Hospital Management System
            </p>
            <h3 class="mt-6 text-2xl font-bold text-gray-900 dark:text-white">
                Sign in to your account
            </h3>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                Access the admin dashboard
            </p>
        </div>

        <!-- Login Form -->
        <div class="bg-white dark:bg-gray-800 py-8 px-6 shadow-xl rounded-lg">
            <form wire:submit.prevent="login" class="space-y-6">
                <!-- Email Field -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Email Address
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"></path>
                            </svg>
                        </div>
                        <input 
                            type="email" 
                            id="email"
                            wire:model.defer="email"
                            class="block w-full pl-10 pr-3 py-3 border border-gray-300 dark:border-gray-600 rounded-md leading-5 bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition duration-150 ease-in-out @error('email') border-red-500 @enderror"
                            placeholder="Enter your email address"
                            autocomplete="email"
                            required
                        >
                    </div>
                    @error('email')
                        <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Password Field -->
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Password
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                            </svg>
                        </div>
                        <input 
                            type="{{ $showPassword ? 'text' : 'password' }}"
                            id="password"
                            wire:model.defer="password"
                            class="block w-full pl-10 pr-12 py-3 border border-gray-300 dark:border-gray-600 rounded-md leading-5 bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition duration-150 ease-in-out @error('password') border-red-500 @enderror"
                            placeholder="Enter your password"
                            autocomplete="current-password"
                            required
                        >
                        <button 
                            type="button"
                            wire:click="togglePasswordVisibility"
                            class="absolute inset-y-0 right-0 pr-3 flex items-center"
                        >
                            @if($showPassword)
                                <svg class="h-5 w-5 text-gray-400 hover:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21"></path>
                                </svg>
                            @else
                                <svg class="h-5 w-5 text-gray-400 hover:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                            @endif
                        </button>
                    </div>
                    @error('password')
                        <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Remember Me -->
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <input 
                            type="checkbox" 
                            id="remember"
                            wire:model.defer="remember"
                            class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded"
                        >
                        <label for="remember" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">
                            Remember me
                        </label>
                    </div>
                    <div class="text-sm">
                        <a href="#" class="font-medium text-primary-600 hover:text-primary-500">
                            Forgot password?
                        </a>
                    </div>
                </div>

                <!-- Submit Button -->
                <div>
                    <button 
                        type="submit"
                        wire:loading.attr="disabled"
                        class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 disabled:opacity-50 disabled:cursor-not-allowed transition duration-150 ease-in-out"
                    >
                        <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                            <svg wire:loading.remove class="h-5 w-5 text-primary-500 group-hover:text-primary-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"></path>
                            </svg>
                            <svg wire:loading class="animate-spin h-5 w-5 text-primary-500" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </span>
                        <span wire:loading.remove>Sign in</span>
                        <span wire:loading>Signing in...</span>
                    </button>
                </div>
            </form>

            <!-- Demo Credentials -->
            <div class="mt-6 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-md">
                <h4 class="text-sm font-medium text-blue-800 dark:text-blue-300 mb-2">Demo Credentials</h4>
                <p class="text-xs text-blue-600 dark:text-blue-400">
                    <strong>Email:</strong> admin@example.com<br>
                    <strong>Password:</strong> password
                </p>
                <button 
                    type="button"
                    wire:click="$set('email', 'admin@example.com')"
                    onclick="@this.set('password', 'password')"
                    class="mt-2 text-xs text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-200 underline"
                >
                    Fill demo credentials
                </button>
            </div>
        </div>

        <!-- Footer -->
        <div class="text-center">
            <p class="text-xs text-gray-500 dark:text-gray-400">
                © 2025 GlobalHealth. SAAS Hospital Management System.
            </p>
        </div>
    </div>
</div>
