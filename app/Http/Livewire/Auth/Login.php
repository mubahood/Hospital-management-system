<?php

namespace App\Http\Livewire\Auth;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Encore\Admin\Auth\Database\Administrator;

class Login extends Component
{
    public $email = '';
    public $password = '';
    public $remember = false;
    public $showPassword = false;

    protected $rules = [
        'email' => 'required|email',
        'password' => 'required|min:6',
    ];

    protected $messages = [
        'email.required' => 'Email address is required.',
        'email.email' => 'Please enter a valid email address.',
        'password.required' => 'Password is required.',
        'password.min' => 'Password must be at least 6 characters.',
    ];

    public function mount()
    {
        // Redirect if already authenticated
        if (Auth::guard('admin')->check()) {
            return redirect()->route('app.dashboard');
        }
    }

    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);
    }

    public function togglePasswordVisibility()
    {
        $this->showPassword = !$this->showPassword;
    }

    public function login()
    {
        $this->validate();

        // Rate limiting
        $key = 'login-attempts:' . request()->ip();
        
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            throw ValidationException::withMessages([
                'email' => "Too many login attempts. Please try again in {$seconds} seconds.",
            ]);
        }

        // Attempt to authenticate using Admin model
        $credentials = [
            'username' => $this->email, // Admin model uses username field
            'password' => $this->password,
        ];

        // Try with email field as well
        $admin = Administrator::where('email', $this->email)
                    ->orWhere('username', $this->email)
                    ->first();

        if ($admin && Hash::check($this->password, $admin->password)) {
            Auth::guard('admin')->login($admin, $this->remember);
            RateLimiter::clear($key);

            // Log the login (activity logging temporarily disabled)
            Log::info('Admin logged in', ['admin_id' => $admin->id, 'email' => $admin->email]);

            $this->emit('notify', 'Welcome back! Login successful.', 'success');

            return redirect()->to(env('LIVEWIRE_BASE_URL', '/app') . '/dashboard');
        }

        // Record failed attempt
        RateLimiter::hit($key, 300); // 5 minutes lockout

        throw ValidationException::withMessages([
            'email' => 'These credentials do not match our records.',
        ]);
    }

    public function render()
    {
        return view('livewire.auth.login');
    }
}
