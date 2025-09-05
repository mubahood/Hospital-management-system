<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Traits\ApiResponser;
use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;

class AdminAuthController extends Controller
{
    use ApiResponser;

    public function __construct()
    {
        $this->middleware('auth:admin_api', ['except' => ['login', 'register']]);
    }

    /**
     * Login for admin users
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        // Find admin user by email
        $admin = Administrator::where('email', $request->email)->first();

        if (!$admin) {
            return $this->error('User not found.', 401);
        }

        // Check password
        if (!Hash::check($request->password, $admin->password)) {
            return $this->error('Invalid credentials.', 401);
        }

        // Set JWT TTL to 30 days
        JWTAuth::factory()->setTTL(60 * 24 * 30);

        // Create token for admin guard
        $token = auth('admin_api')->login($admin);

        if (!$token) {
            return $this->error('Could not create token.', 500);
        }

        return $this->success([
            'user' => [
                'id' => $admin->id,
                'name' => $admin->name,
                'email' => $admin->email,
                'avatar' => $admin->avatar,
                'company_id' => $admin->company_id,
            ],
            'token' => $token,
        ], 'Login successful.');
    }

    /**
     * Get authenticated admin user
     */
    public function me()
    {
        $admin = auth('admin_api')->user();
        
        if (!$admin) {
            return $this->error('User not authenticated.', 401);
        }

        return $this->success([
            'user' => [
                'id' => $admin->id,
                'name' => $admin->name,
                'email' => $admin->email,
                'avatar' => $admin->avatar,
                'company_id' => $admin->company_id,
            ],
        ], 'User data retrieved successfully.');
    }

    /**
     * Logout admin user
     */
    public function logout()
    {
        auth('admin_api')->logout();
        return $this->success(null, 'Successfully logged out.');
    }

    /**
     * Refresh admin token
     */
    public function refresh()
    {
        $token = auth('admin_api')->refresh();
        return $this->success(['token' => $token], 'Token refreshed successfully.');
    }
}
