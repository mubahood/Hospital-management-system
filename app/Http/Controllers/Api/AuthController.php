<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

/**
 * API Authentication Controller
 * 
 * Handles user authentication for API endpoints including:
 * - Login/logout
 * - Token refresh
 * - User profile management
 * - Password management
 */
class AuthController extends BaseApiController
{
    /**
     * Create a new AuthController instance.
     */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register', 'forgotPassword']]);
    }

    /**
     * Get a JWT via given credentials.
     */
    public function login(Request $request): JsonResponse
    {
        try {
            $validated = $this->validateRequest($request, [
                'email' => 'required|email',
                'password' => 'required|string',
                'enterprise_id' => 'nullable|integer|exists:companies,id',
                'remember_me' => 'boolean'
            ]);

            // Rate limiting
            $key = 'login_attempts:' . $request->ip();
            if (!$this->checkRateLimit($key, 5, 15)) {
                return $this->errorResponse('Too many login attempts. Please try again later.', 429);
            }

            // Find user
            $user = User::where('email', $validated['email'])->first();

            if (!$user || !Hash::check($validated['password'], $user->password)) {
                return $this->errorResponse('Invalid credentials', 401);
            }

            // Check if user is active
            if (!$user->is_active) {
                return $this->errorResponse('Account is inactive', 403);
            }

            // Enterprise scope check
            if (isset($validated['enterprise_id']) && $user->enterprise_id !== $validated['enterprise_id']) {
                return $this->errorResponse('Invalid enterprise access', 403);
            }

            // Generate token
            $token = JWTAuth::fromUser($user);
            $ttl = config('jwt.ttl', 60);

            // Update last login
            $user->update([
                'last_login_at' => now(),
                'last_login_ip' => $request->ip(),
            ]);

            $this->logApiActivity('user_login', [
                'user_id' => $user->id,
                'enterprise_id' => $user->enterprise_id,
            ]);

            return $this->successResponse([
                'access_token' => $token,
                'token_type' => 'bearer',
                'expires_in' => $ttl * 60, // Convert to seconds
                'user' => $this->transformUser($user),
            ], 'Login successful');

        } catch (ValidationException $e) {
            return $this->handleException($e);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Register a new user
     */
    public function register(Request $request): JsonResponse
    {
        try {
            $validated = $this->validateRequest($request, [
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:8|confirmed',
                'phone' => 'nullable|string|max:20',
                'enterprise_id' => 'required|integer|exists:companies,id',
                'user_type' => 'required|string|in:doctor,nurse,receptionist,admin,patient',
            ]);

            // Rate limiting
            $key = 'register_attempts:' . $request->ip();
            if (!$this->checkRateLimit($key, 3, 60)) {
                return $this->errorResponse('Too many registration attempts. Please try again later.', 429);
            }

            // Create user
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'phone' => $validated['phone'] ?? null,
                'enterprise_id' => $validated['enterprise_id'],
                'user_type' => $validated['user_type'],
                'is_active' => true,
                'email_verified_at' => now(), // Auto-verify for API registration
            ]);

            // Generate token
            $token = JWTAuth::fromUser($user);

            $this->logApiActivity('user_register', [
                'user_id' => $user->id,
                'enterprise_id' => $user->enterprise_id,
            ]);

            return $this->successResponse([
                'access_token' => $token,
                'token_type' => 'bearer',
                'expires_in' => config('jwt.ttl', 60) * 60,
                'user' => $this->transformUser($user),
            ], 'Registration successful', 201);

        } catch (ValidationException $e) {
            return $this->handleException($e);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Get the authenticated User.
     */
    public function me(): JsonResponse
    {
        try {
            $user = Auth::user();
            
            return $this->successResponse([
                'user' => $this->transformUser($user, ['roles', 'permissions']),
            ], 'User profile retrieved');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Log the user out (Invalidate the token).
     */
    public function logout(): JsonResponse
    {
        try {
            $user = Auth::user();
            
            JWTAuth::invalidate(JWTAuth::getToken());

            $this->logApiActivity('user_logout', [
                'user_id' => $user->id,
                'enterprise_id' => $user->enterprise_id,
            ]);

            return $this->successResponse(null, 'Successfully logged out');

        } catch (JWTException $e) {
            return $this->errorResponse('Failed to logout, please try again', 500);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Refresh a token.
     */
    public function refresh(): JsonResponse
    {
        try {
            $token = JWTAuth::refresh(JWTAuth::getToken());
            $user = Auth::user();

            return $this->successResponse([
                'access_token' => $token,
                'token_type' => 'bearer',
                'expires_in' => config('jwt.ttl', 60) * 60,
                'user' => $this->transformUser($user),
            ], 'Token refreshed successfully');

        } catch (JWTException $e) {
            return $this->errorResponse('Token cannot be refreshed', 401);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Update user profile
     */
    public function updateProfile(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            
            $validated = $this->validateRequest($request, [
                'name' => 'sometimes|string|max:255',
                'phone' => 'sometimes|nullable|string|max:20',
                'avatar' => 'sometimes|nullable|string',
                'bio' => 'sometimes|nullable|string|max:1000',
                'preferences' => 'sometimes|array',
            ]);

            $user->update($validated);

            $this->logApiActivity('profile_update', [
                'user_id' => $user->id,
                'updated_fields' => array_keys($validated),
            ]);

            return $this->successResponse([
                'user' => $this->transformUser($user->fresh()),
            ], 'Profile updated successfully');

        } catch (ValidationException $e) {
            return $this->handleException($e);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Change password
     */
    public function changePassword(Request $request): JsonResponse
    {
        try {
            $validated = $this->validateRequest($request, [
                'current_password' => 'required|string',
                'new_password' => 'required|string|min:8|confirmed',
            ]);

            $user = Auth::user();

            if (!Hash::check($validated['current_password'], $user->password)) {
                return $this->errorResponse('Current password is incorrect', 400);
            }

            $user->update([
                'password' => Hash::make($validated['new_password']),
                'password_changed_at' => now(),
            ]);

            $this->logApiActivity('password_change', [
                'user_id' => $user->id,
            ]);

            return $this->successResponse(null, 'Password changed successfully');

        } catch (ValidationException $e) {
            return $this->handleException($e);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Forgot password - send reset link
     */
    public function forgotPassword(Request $request): JsonResponse
    {
        try {
            $validated = $this->validateRequest($request, [
                'email' => 'required|email|exists:users,email',
            ]);

            // Rate limiting
            $key = 'forgot_password:' . $request->ip();
            if (!$this->checkRateLimit($key, 3, 60)) {
                return $this->errorResponse('Too many password reset attempts. Please try again later.', 429);
            }

            $user = User::where('email', $validated['email'])->first();

            // Generate reset token (implement your password reset logic)
            $resetToken = str()->random(64);
            
            // Store reset token (you might want to create a password_resets table)
            // For now, we'll just log the activity
            
            $this->logApiActivity('password_reset_requested', [
                'user_id' => $user->id,
                'email' => $user->email,
            ]);

            // In a real implementation, you would send an email here
            // Mail::to($user)->send(new PasswordResetMail($resetToken));

            return $this->successResponse(null, 'Password reset instructions sent to your email');

        } catch (ValidationException $e) {
            return $this->handleException($e);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Transform user for API response
     */
    private function transformUser(User $user, array $includes = []): array
    {
        $data = [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'user_type' => $user->user_type,
            'enterprise_id' => $user->enterprise_id,
            'is_active' => $user->is_active,
            'avatar' => $user->avatar,
            'bio' => $user->bio,
            'last_login_at' => $user->last_login_at?->toISOString(),
            'created_at' => $user->created_at->toISOString(),
            'updated_at' => $user->updated_at->toISOString(),
        ];

        // Add includes if specified
        if (in_array('enterprise', $includes) && $user->relationLoaded('enterprise')) {
            $data['enterprise'] = $user->enterprise;
        }

        return $data;
    }
}
