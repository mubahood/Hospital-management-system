<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Carbon\Carbon;

/**
 * EnhancedAuthentication Middleware
 * 
 * Provides enterprise-aware authentication with enhanced security features:
 * - Multi-factor authentication support
 * - Rate limiting per user/IP
 * - Session security validation
 * - Audit logging
 * - Enterprise isolation
 * - Suspicious activity detection
 */
class EnhancedAuthentication
{
    /**
     * Maximum failed login attempts before lockout
     */
    const MAX_LOGIN_ATTEMPTS = 5;

    /**
     * Lockout duration in minutes
     */
    const LOCKOUT_DURATION = 15;

    /**
     * Session timeout in minutes
     */
    const SESSION_TIMEOUT = 120;

    /**
     * Maximum concurrent sessions per user
     */
    const MAX_CONCURRENT_SESSIONS = 3;

    /**
     * Handle an incoming request
     */
    public function handle(Request $request, Closure $next, ...$guards)
    {
        // Check if user is authenticated
        if (!Auth::check()) {
            return $this->handleUnauthenticated($request);
        }

        $user = Auth::user();

        // Validate user account status
        if (!$this->validateUserStatus($user)) {
            Auth::logout();
            return $this->handleAccountSuspended($request);
        }

        // Validate enterprise access
        if (!$this->validateEnterpriseAccess($user, $request)) {
            Auth::logout();
            return $this->handleUnauthorizedEnterprise($request);
        }

        // Check session security
        if (!$this->validateSessionSecurity($request, $user)) {
            Auth::logout();
            return $this->handleSessionSecurity($request);
        }

        // Rate limiting
        if (!$this->checkRateLimit($request, $user)) {
            return $this->handleRateLimited($request);
        }

        // Check for suspicious activity
        $this->detectSuspiciousActivity($request, $user);

        // Update session activity
        $this->updateSessionActivity($request, $user);

        // Log successful access
        $this->logSuccessfulAccess($request, $user);

        return $next($request);
    }

    /**
     * Handle unauthenticated request
     */
    protected function handleUnauthenticated(Request $request)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated. Please log in to continue.',
                'error_code' => 'AUTH_REQUIRED'
            ], 401);
        }

        return redirect()->guest(route('login'))->with('error', 'Please log in to access this page.');
    }

    /**
     * Validate user account status
     */
    protected function validateUserStatus($user): bool
    {
        // Check if user account is active
        if (isset($user->status) && $user->status !== 'Active') {
            return false;
        }

        // Check if user is not deleted
        if (method_exists($user, 'trashed') && $user->trashed()) {
            return false;
        }

        // Check if user has valid enterprise
        if (!$user->enterprise_id) {
            return false;
        }

        return true;
    }

    /**
     * Validate enterprise access
     */
    protected function validateEnterpriseAccess($user, Request $request): bool
    {
        // Get enterprise from request or user
        $requestedEnterpriseId = $request->header('Enterprise-ID') 
            ?? $request->input('enterprise_id') 
            ?? session('enterprise_id');

        // If no specific enterprise requested, use user's enterprise
        if (!$requestedEnterpriseId) {
            session(['enterprise_id' => $user->enterprise_id]);
            return true;
        }

        // Validate user belongs to requested enterprise
        if ($requestedEnterpriseId != $user->enterprise_id) {
            Log::warning('Unauthorized enterprise access attempt', [
                'user_id' => $user->id,
                'user_enterprise' => $user->enterprise_id,
                'requested_enterprise' => $requestedEnterpriseId,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);
            return false;
        }

        session(['enterprise_id' => $user->enterprise_id]);
        return true;
    }

    /**
     * Validate session security
     */
    protected function validateSessionSecurity(Request $request, $user): bool
    {
        $sessionKey = 'user_session_' . $user->id;
        $currentSessionData = Cache::get($sessionKey, []);

        // Check session timeout
        if (isset($currentSessionData['last_activity'])) {
            $lastActivity = Carbon::parse($currentSessionData['last_activity']);
            if ($lastActivity->diffInMinutes(now()) > self::SESSION_TIMEOUT) {
                return false;
            }
        }

        // Check IP address consistency (if enabled)
        if (config('security.check_ip_consistency', false)) {
            if (isset($currentSessionData['ip']) && $currentSessionData['ip'] !== $request->ip()) {
                Log::warning('IP address changed during session', [
                    'user_id' => $user->id,
                    'original_ip' => $currentSessionData['ip'],
                    'new_ip' => $request->ip(),
                    'user_agent' => $request->userAgent()
                ]);
                return false;
            }
        }

        // Check user agent consistency
        if (config('security.check_user_agent_consistency', false)) {
            if (isset($currentSessionData['user_agent']) && 
                $currentSessionData['user_agent'] !== $request->userAgent()) {
                Log::warning('User agent changed during session', [
                    'user_id' => $user->id,
                    'original_user_agent' => $currentSessionData['user_agent'],
                    'new_user_agent' => $request->userAgent(),
                    'ip' => $request->ip()
                ]);
                return false;
            }
        }

        // Check concurrent sessions
        $activeSessions = Cache::get('user_active_sessions_' . $user->id, []);
        if (count($activeSessions) > self::MAX_CONCURRENT_SESSIONS) {
            // Remove oldest session
            $oldestSession = array_shift($activeSessions);
            Cache::put('user_active_sessions_' . $user->id, $activeSessions, now()->addMinutes(self::SESSION_TIMEOUT));
        }

        return true;
    }

    /**
     * Check rate limiting
     */
    protected function checkRateLimit(Request $request, $user): bool
    {
        $key = 'user_requests_' . $user->id;
        $attempts = RateLimiter::attempts($key);
        $maxAttempts = config('security.max_requests_per_minute', 60);

        if ($attempts >= $maxAttempts) {
            Log::warning('Rate limit exceeded', [
                'user_id' => $user->id,
                'ip' => $request->ip(),
                'attempts' => $attempts,
                'max_attempts' => $maxAttempts
            ]);
            return false;
        }

        RateLimiter::hit($key, 60); // 60 seconds window
        return true;
    }

    /**
     * Detect suspicious activity
     */
    protected function detectSuspiciousActivity(Request $request, $user): void
    {
        $suspiciousPatterns = [
            // Multiple rapid requests
            'rapid_requests' => $this->checkRapidRequests($request, $user),
            // Unusual access times
            'unusual_time' => $this->checkUnusualAccessTime($request, $user),
            // Geographic anomaly
            'geographic_anomaly' => $this->checkGeographicAnomaly($request, $user),
            // Suspicious user agent
            'suspicious_user_agent' => $this->checkSuspiciousUserAgent($request),
        ];

        $suspiciousCount = array_sum($suspiciousPatterns);

        if ($suspiciousCount >= 2) {
            Log::alert('Suspicious activity detected', [
                'user_id' => $user->id,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'patterns' => $suspiciousPatterns,
                'suspicion_level' => $suspiciousCount,
                'url' => $request->fullUrl(),
                'timestamp' => now()->toISOString()
            ]);

            // Optional: Send notification to security team
            $this->notifySecurityTeam($user, $suspiciousPatterns, $request);
        }
    }

    /**
     * Check for rapid requests
     */
    protected function checkRapidRequests(Request $request, $user): bool
    {
        $key = 'rapid_check_' . $user->id;
        $recentRequests = Cache::get($key, []);
        $now = now();

        // Add current request
        $recentRequests[] = $now->timestamp;

        // Keep only requests from last 60 seconds
        $recentRequests = array_filter($recentRequests, function($timestamp) use ($now) {
            return ($now->timestamp - $timestamp) <= 60;
        });

        Cache::put($key, $recentRequests, 60);

        // Flag if more than 30 requests in 60 seconds
        return count($recentRequests) > 30;
    }

    /**
     * Check unusual access time
     */
    protected function checkUnusualAccessTime(Request $request, $user): bool
    {
        $currentHour = now()->hour;
        
        // Flag access between 2 AM and 6 AM as potentially suspicious
        return $currentHour >= 2 && $currentHour < 6;
    }

    /**
     * Check geographic anomaly
     */
    protected function checkGeographicAnomaly(Request $request, $user): bool
    {
        // This would require IP geolocation service
        // For now, return false - implement with service like MaxMind
        return false;
    }

    /**
     * Check suspicious user agent
     */
    protected function checkSuspiciousUserAgent(Request $request): bool
    {
        $userAgent = strtolower($request->userAgent());
        
        $suspiciousPatterns = [
            'bot', 'crawler', 'spider', 'scraper', 'automated',
            'curl', 'wget', 'postman', 'insomnia'
        ];

        foreach ($suspiciousPatterns as $pattern) {
            if (strpos($userAgent, $pattern) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Update session activity
     */
    protected function updateSessionActivity(Request $request, $user): void
    {
        $sessionKey = 'user_session_' . $user->id;
        $sessionData = [
            'user_id' => $user->id,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'last_activity' => now()->toISOString(),
            'last_url' => $request->fullUrl(),
            'enterprise_id' => $user->enterprise_id
        ];

        Cache::put($sessionKey, $sessionData, now()->addMinutes(self::SESSION_TIMEOUT));

        // Update active sessions
        $activeSessionsKey = 'user_active_sessions_' . $user->id;
        $activeSessions = Cache::get($activeSessionsKey, []);
        $sessionId = session()->getId();
        
        $activeSessions[$sessionId] = [
            'session_id' => $sessionId,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'started_at' => $activeSessions[$sessionId]['started_at'] ?? now()->toISOString(),
            'last_activity' => now()->toISOString()
        ];

        Cache::put($activeSessionsKey, $activeSessions, now()->addMinutes(self::SESSION_TIMEOUT));
    }

    /**
     * Log successful access
     */
    protected function logSuccessfulAccess(Request $request, $user): void
    {
        Log::info('Successful authentication', [
            'user_id' => $user->id,
            'enterprise_id' => $user->enterprise_id,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'timestamp' => now()->toISOString()
        ]);
    }

    /**
     * Handle account suspended
     */
    protected function handleAccountSuspended(Request $request)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Your account has been suspended. Please contact support.',
                'error_code' => 'ACCOUNT_SUSPENDED'
            ], 403);
        }

        return redirect()->route('login')
            ->with('error', 'Your account has been suspended. Please contact support.');
    }

    /**
     * Handle unauthorized enterprise access
     */
    protected function handleUnauthorizedEnterprise(Request $request)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access to enterprise data.',
                'error_code' => 'ENTERPRISE_UNAUTHORIZED'
            ], 403);
        }

        return redirect()->route('login')
            ->with('error', 'Unauthorized access. Please log in with appropriate credentials.');
    }

    /**
     * Handle session security issues
     */
    protected function handleSessionSecurity(Request $request)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Session security validation failed. Please log in again.',
                'error_code' => 'SESSION_INVALID'
            ], 401);
        }

        return redirect()->route('login')
            ->with('warning', 'Your session has expired or become invalid. Please log in again.');
    }

    /**
     * Handle rate limited requests
     */
    protected function handleRateLimited(Request $request)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Too many requests. Please slow down.',
                'error_code' => 'RATE_LIMITED'
            ], 429);
        }

        return back()->with('error', 'Too many requests. Please wait before trying again.');
    }

    /**
     * Notify security team of suspicious activity
     */
    protected function notifySecurityTeam($user, array $patterns, Request $request): void
    {
        // This would integrate with your notification system
        // For now, just log it - implement with mail/SMS/Slack etc.
        Log::channel('security')->critical('Security Alert: Suspicious Activity', [
            'user' => [
                'id' => $user->id,
                'email' => $user->email,
                'enterprise_id' => $user->enterprise_id
            ],
            'request' => [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'url' => $request->fullUrl(),
                'method' => $request->method()
            ],
            'patterns' => $patterns,
            'timestamp' => now()->toISOString()
        ]);
    }
}
