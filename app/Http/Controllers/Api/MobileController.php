<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Enterprise;
use App\Models\User;
use App\Models\Utils;
use App\Models\Consultation;
use App\Models\MedicalService;
use App\Models\PaymentRecord;
use App\Models\Service;
use App\Models\DeviceToken;
use App\Services\PushNotificationService;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class MobileController extends Controller
{
    use ApiResponser;

    /**
     * Create a new MobileController instance.
     */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register']]);
    }

    /**
     * Get a JWT via given credentials.
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:6',
            'enterprise_id' => 'required|integer|exists:enterprises,id'
        ]);

        if ($validator->fails()) {
            return $this->error('Validation failed: ' . implode(', ', $validator->errors()->all()));
        }

        try {
            // Validate enterprise exists
            $enterprise = Enterprise::find($request->enterprise_id);
            if (!$enterprise) {
                return $this->error('Enterprise not found');
            }

            // Attempt to find user in the specified enterprise
            $user = User::where('email', $request->email)
                       ->where('enterprise_id', $request->enterprise_id)
                       ->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                return $this->error('Invalid credentials or enterprise');
            }

            $token = JWTAuth::fromUser($user);

            return $this->success([
                'access_token' => $token,
                'token_type' => 'bearer',
                'expires_in' => 3600,
                'user' => $user
            ], 'Login successful');

        } catch (\Exception $e) {
            return $this->error('Login failed: ' . $e->getMessage());
        }
    }

    /**
     * Register a new user
     */
    public function register(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:admin_users,email',
                'password' => 'required|string|min:6',
                'phone_number_1' => 'required|string|max:20',
                'enterprise_id' => 'required|integer|exists:enterprises,id',
                'user_type' => 'string|in:patient,doctor,nurse,admin',
                'username' => 'required|string|max:255|unique:admin_users,username',
            ]);

            if ($validator->fails()) {
                return $this->error('Validation failed: ' . implode(', ', $validator->errors()->all()));
            }

            // Validate enterprise exists
            $enterprise = Enterprise::find($request->enterprise_id);
            if (!$enterprise) {
                return $this->error('Enterprise not found');
            }

            $user = User::create([
                'username' => $request->username,
                'name' => $request->first_name . ' ' . $request->last_name,
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'phone_number_1' => $request->phone_number_1,
                'enterprise_id' => $request->enterprise_id,
                'user_type' => $request->user_type ?? 'mobile_user',
                'current_address' => $request->current_address,
                'nationality' => $request->nationality,
                'card_status' => 'Inactive',
                'status' => 2, // Active status
                'company_id' => $request->enterprise_id
            ]);

            $token = JWTAuth::fromUser($user);

            return $this->success([
                'access_token' => $token,
                'token_type' => 'bearer',
                'expires_in' => 3600,
                'user' => $user
            ], 'User registered successfully');

        } catch (\Exception $e) {
            return $this->error('Registration failed: ' . $e->getMessage());
        }
    }

    /**
     * Get current user profile
     */
    public function me()
    {
        $user = auth('api')->user();
        if (!$user) {
            return $this->error('User not authenticated', null, 401);
        }

        $enterprise = Enterprise::find($user->enterprise_id);

        return $this->success([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'phone_number_1' => $user->phone_number_1,
                'phone_number_2' => $user->phone_number_2,
                'enterprise_id' => $user->enterprise_id,
                'enterprise_name' => $enterprise ? $enterprise->name : 'Unknown',
                'user_type' => $user->user_type,
                'date_of_birth' => $user->date_of_birth,
                'current_address' => $user->current_address,
            ]
        ], 'Profile retrieved successfully');
    }

    /**
     * Get user's consultations
     */
    public function consultations()
    {
        $user = auth('api')->user();
        if (!$user) {
            return $this->error('User not authenticated', null, 401);
        }

        $consultations = Consultation::where('patient_id', $user->id)
            ->with(['patient', 'receptionist'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return $this->success([
            'consultations' => $consultations->items(),
            'pagination' => [
                'current_page' => $consultations->currentPage(),
                'last_page' => $consultations->lastPage(),
                'per_page' => $consultations->perPage(),
                'total' => $consultations->total(),
            ]
        ], 'Consultations retrieved successfully');
    }

    /**
     * Create a new consultation
     */
    public function createConsultation(Request $request)
    {
        $user = auth('api')->user();
        if (!$user) {
            return $this->error('User not authenticated', null, 401);
        }

        $validator = Validator::make($request->all(), [
            'reason_for_consultation' => 'required|string',
            'services_requested' => 'required|array',
            'preferred_date_and_time' => 'required|date|after:now',
        ]);

        if ($validator->fails()) {
            return $this->error('Validation failed', $validator->errors(), 422);
        }

        $consultation = Consultation::create([
            'enterprise_id' => $user->enterprise_id,
            'patient_id' => $user->id,
            'patient_name' => $user->name,
            'patient_contact' => $user->phone_number_1,
            'contact_address' => $user->current_address,
            'reason_for_consultation' => $request->reason_for_consultation,
            'services_requested' => implode(',', $request->services_requested),
            'preferred_date_and_time' => $request->preferred_date_and_time,
            'main_status' => 'Pending',
            'consultation_number' => Consultation::generate_consultation_number(),
        ]);

        return $this->success([
            'consultation' => $consultation
        ], 'Consultation created successfully', 201);
    }

    /**
     * Get available services
     */
    public function services()
    {
        $services = Service::all();
        return $this->success([
            'services' => $services
        ], 'Services retrieved successfully');
    }

    /**
     * Get user's medical services
     */
    public function medicalServices()
    {
        $user = auth('api')->user();
        if (!$user) {
            return $this->error('User not authenticated', null, 401);
        }

        $medicalServices = MedicalService::where('patient_id', $user->id)
            ->with(['consultation'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return $this->success([
            'medical_services' => $medicalServices->items(),
            'pagination' => [
                'current_page' => $medicalServices->currentPage(),
                'last_page' => $medicalServices->lastPage(),
                'per_page' => $medicalServices->perPage(),
                'total' => $medicalServices->total(),
            ]
        ], 'Medical services retrieved successfully');
    }

    /**
     * Get user's payment records
     */
    public function paymentRecords()
    {
        $user = auth('api')->user();
        if (!$user) {
            return $this->error('User not authenticated', null, 401);
        }

        // Get consultations for this user
        $consultationIds = Consultation::where('patient_id', $user->id)
            ->pluck('id')
            ->toArray();

        $paymentRecords = PaymentRecord::whereIn('consultation_id', $consultationIds)
            ->with(['consultation'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return $this->success([
            'payment_records' => $paymentRecords->items(),
            'pagination' => [
                'current_page' => $paymentRecords->currentPage(),
                'last_page' => $paymentRecords->lastPage(),
                'per_page' => $paymentRecords->perPage(),
                'total' => $paymentRecords->total(),
            ]
        ], 'Payment records retrieved successfully');
    }

    /**
     * Update user profile
     */
    public function updateProfile(Request $request)
    {
        $user = auth('api')->user();
        if (!$user) {
            return $this->error('User not authenticated', null, 401);
        }

        $validator = Validator::make($request->all(), [
            'first_name' => 'sometimes|string|max:255',
            'last_name' => 'sometimes|string|max:255',
            'phone_number_1' => 'sometimes|string|max:20',
            'phone_number_2' => 'sometimes|string|max:20',
            'current_address' => 'sometimes|string|max:500',
            'date_of_birth' => 'sometimes|date',
        ]);

        if ($validator->fails()) {
            return $this->error('Validation failed', $validator->errors(), 422);
        }

        $updateData = $request->only([
            'first_name', 'last_name', 'phone_number_1', 
            'phone_number_2', 'current_address', 'date_of_birth'
        ]);

        // Update name if first_name or last_name changed
        if (isset($updateData['first_name']) || isset($updateData['last_name'])) {
            $firstName = $updateData['first_name'] ?? $user->first_name;
            $lastName = $updateData['last_name'] ?? $user->last_name;
            $updateData['name'] = $firstName . ' ' . $lastName;
        }

        foreach ($updateData as $key => $value) {
            $user->{$key} = $value;
        }
        $user->save();

        $refreshedUser = User::find($user->id);

        return $this->success([
            'user' => $refreshedUser
        ], 'Profile updated successfully');
    }

    /**
     * Change password
     */
    public function changePassword(Request $request)
    {
        $user = auth('api')->user();
        if (!$user) {
            return $this->error('User not authenticated', null, 401);
        }

        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return $this->error('Validation failed', $validator->errors(), 422);
        }

        if (!Hash::check($request->current_password, $user->password)) {
            return $this->error('Current password is incorrect', null, 422);
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        return $this->success(null, 'Password changed successfully');
    }

    /**
     * Register device token for push notifications
     */
    public function registerDeviceToken(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'device_token' => 'required|string',
            'platform' => 'required|in:ios,android',
            'device_id' => 'required|string',
            'app_version' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->error('Validation failed', $validator->errors(), 422);
        }

        $user = auth()->user();
        
        // Check if token already exists for this device
        $existingToken = DeviceToken::where('user_id', $user->id)
            ->where('device_id', $request->device_id)
            ->first();

        if ($existingToken) {
            // Update existing token
            $existingToken->update([
                'device_token' => $request->device_token,
                'platform' => $request->platform,
                'app_version' => $request->app_version,
                'is_active' => true,
                'last_used_at' => now(),
            ]);
            $deviceToken = $existingToken;
        } else {
            // Create new token
            $deviceToken = DeviceToken::create([
                'user_id' => $user->id,
                'enterprise_id' => $user->enterprise_id,
                'device_token' => $request->device_token,
                'platform' => $request->platform,
                'device_id' => $request->device_id,
                'app_version' => $request->app_version,
                'is_active' => true,
                'last_used_at' => now(),
            ]);
        }

        return $this->success([
            'device_token_id' => $deviceToken->id,
            'registered_at' => $deviceToken->created_at->toISOString(),
        ], 'Device token registered successfully');
    }

    /**
     * Send test push notification
     */
    public function sendTestNotification(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:100',
            'body' => 'required|string|max:255',
            'data' => 'sometimes|array',
        ]);

        if ($validator->fails()) {
            return $this->error('Validation failed', $validator->errors(), 422);
        }

        $user = auth()->user();
        
        // Get user's active device tokens
        $deviceTokens = DeviceToken::where('user_id', $user->id)
            ->where('is_active', true)
            ->get();

        if ($deviceTokens->isEmpty()) {
            return $this->error('No active device tokens found for this user', null, 404);
        }

        $pushService = new PushNotificationService();
        
        $notification = [
            'title' => $request->title,
            'body' => $request->body,
            'data' => $request->data ?? [],
        ];

        $results = [];
        foreach ($deviceTokens as $deviceToken) {
            try {
                $result = $pushService->sendToDevice($deviceToken->device_token, $notification, $deviceToken->platform);
                $results[] = [
                    'device_id' => $deviceToken->device_id,
                    'platform' => $deviceToken->platform,
                    'success' => $result['success'],
                    'message_id' => $result['message_id'] ?? null,
                    'error' => $result['error'] ?? null,
                ];
            } catch (\Exception $e) {
                $results[] = [
                    'device_id' => $deviceToken->device_id,
                    'platform' => $deviceToken->platform,
                    'success' => false,
                    'error' => $e->getMessage(),
                ];
            }
        }

        return $this->success([
            'sent_to_devices' => count($deviceTokens),
            'results' => $results,
        ], 'Test notifications sent');
    }

    /**
     * Get user's device tokens
     */
    public function getUserDeviceTokens()
    {
        $user = auth()->user();
        
        $deviceTokens = DeviceToken::where('user_id', $user->id)
            ->orderBy('last_used_at', 'desc')
            ->get(['id', 'device_id', 'platform', 'app_version', 'is_active', 'created_at', 'last_used_at']);

        return $this->success([
            'device_tokens' => $deviceTokens,
        ], 'Device tokens retrieved successfully');
    }

    /**
     * Deactivate device token
     */
    public function deactivateDeviceToken(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'device_id' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->error('Validation failed', $validator->errors(), 422);
        }

        $user = auth()->user();
        
        $deviceToken = DeviceToken::where('user_id', $user->id)
            ->where('device_id', $request->device_id)
            ->first();

        if (!$deviceToken) {
            return $this->error('Device token not found', null, 404);
        }

        $deviceToken->update(['is_active' => false]);

        return $this->success(null, 'Device token deactivated successfully');
    }
}
