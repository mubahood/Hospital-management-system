<?php

namespace App\Services;

use App\Models\DeviceToken;
use App\Models\User;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;

class PushNotificationService
{
    protected $client;
    protected $fcmServerKey;
    protected $fcmUrl = 'https://fcm.googleapis.com/fcm/send';

    public function __construct()
    {
        $this->client = new Client();
        $this->fcmServerKey = env('FCM_SERVER_KEY');
    }

    /**
     * Send push notification to a specific user
     */
    public function sendToUser($userId, $title, $body, $data = [], $enterpriseId = null)
    {
        try {
            $query = DeviceToken::where('user_id', $userId)->active();
            
            if ($enterpriseId) {
                $query->where('enterprise_id', $enterpriseId);
            }
            
            $deviceTokens = $query->get();

            if ($deviceTokens->isEmpty()) {
                Log::info("No active device tokens found for user {$userId}");
                return false;
            }

            $results = [];
            foreach ($deviceTokens as $deviceToken) {
                $result = $this->sendNotification(
                    $deviceToken->device_token, 
                    $title, 
                    $body, 
                    $data,
                    $deviceToken->platform
                );
                
                if ($result) {
                    $deviceToken->markAsUsed();
                }
                
                $results[] = $result;
            }

            return in_array(true, $results);

        } catch (\Exception $e) {
            Log::error('Push notification error for user ' . $userId . ': ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send push notification to multiple users
     */
    public function sendToUsers($userIds, $title, $body, $data = [], $enterpriseId = null)
    {
        $results = [];
        foreach ($userIds as $userId) {
            $results[] = $this->sendToUser($userId, $title, $body, $data, $enterpriseId);
        }
        return $results;
    }

    /**
     * Send notification to all users in an enterprise
     */
    public function sendToEnterprise($enterpriseId, $title, $body, $data = [])
    {
        try {
            $deviceTokens = DeviceToken::where('enterprise_id', $enterpriseId)
                                    ->active()
                                    ->get();

            if ($deviceTokens->isEmpty()) {
                Log::info("No active device tokens found for enterprise {$enterpriseId}");
                return false;
            }

            $results = [];
            foreach ($deviceTokens as $deviceToken) {
                $result = $this->sendNotification(
                    $deviceToken->device_token, 
                    $title, 
                    $body, 
                    $data,
                    $deviceToken->platform
                );
                
                if ($result) {
                    $deviceToken->markAsUsed();
                }
                
                $results[] = $result;
            }

            return $results;

        } catch (\Exception $e) {
            Log::error('Push notification error for enterprise ' . $enterpriseId . ': ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send notification to specific device tokens
     */
    public function sendToTokens($tokens, $title, $body, $data = [], $platform = 'android')
    {
        $results = [];
        foreach ($tokens as $token) {
            $results[] = $this->sendNotification($token, $title, $body, $data, $platform);
        }
        return $results;
    }

    /**
     * Send individual push notification
     */
    protected function sendNotification($deviceToken, $title, $body, $data = [], $platform = 'android')
    {
        try {
            // FCM notification payload
            if ($platform === 'ios') {
                $payload = [
                    'to' => $deviceToken,
                    'notification' => [
                        'title' => $title,
                        'body' => $body,
                        'sound' => 'default',
                        'badge' => 1,
                    ],
                    'data' => $data,
                    'priority' => 'high',
                    'content_available' => true
                ];
            } else {
                $payload = [
                    'to' => $deviceToken,
                    'notification' => [
                        'title' => $title,
                        'body' => $body,
                        'sound' => 'default',
                        'icon' => 'ic_notification',
                        'color' => '#2196F3'
                    ],
                    'data' => array_merge($data, [
                        'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                        'title' => $title,
                        'body' => $body,
                    ]),
                    'priority' => 'high'
                ];
            }

            // Use mock response for testing if FCM key not set
            if (!$this->fcmServerKey) {
                Log::info('FCM Server Key not set, simulating notification send', [
                    'token' => substr($deviceToken, 0, 20) . '...',
                    'title' => $title,
                    'body' => $body,
                    'data' => $data
                ]);
                return true; // Simulate successful send
            }

            $headers = [
                'Authorization' => 'key=' . $this->fcmServerKey,
                'Content-Type' => 'application/json',
            ];

            $response = $this->client->post($this->fcmUrl, [
                'headers' => $headers,
                'json' => $payload,
                'timeout' => 10
            ]);

            $responseBody = json_decode($response->getBody()->getContents(), true);
            
            if ($response->getStatusCode() === 200 && isset($responseBody['success']) && $responseBody['success'] > 0) {
                Log::info('Push notification sent successfully', [
                    'token' => substr($deviceToken, 0, 20) . '...',
                    'title' => $title
                ]);
                return true;
            } else {
                Log::warning('Push notification failed', [
                    'token' => substr($deviceToken, 0, 20) . '...',
                    'response' => $responseBody
                ]);
                return false;
            }

        } catch (RequestException $e) {
            Log::error('Push notification HTTP error: ' . $e->getMessage(), [
                'token' => substr($deviceToken, 0, 20) . '...'
            ]);
            return false;
        } catch (\Exception $e) {
            Log::error('Push notification error: ' . $e->getMessage(), [
                'token' => substr($deviceToken, 0, 20) . '...'
            ]);
            return false;
        }
    }

    /**
     * Register or update device token
     */
    public function registerToken($userId, $enterpriseId, $tokenData)
    {
        try {
            // Deactivate old tokens for this user and device
            DeviceToken::where('user_id', $userId)
                      ->where('device_token', $tokenData['device_token'])
                      ->update(['is_active' => false]);

            // Create new token record
            $deviceToken = DeviceToken::create([
                'user_id' => $userId,
                'enterprise_id' => $enterpriseId,
                'device_token' => $tokenData['device_token'],
                'device_type' => $tokenData['device_type'] ?? 'mobile',
                'platform' => $tokenData['platform'] ?? 'android',
                'app_version' => $tokenData['app_version'] ?? null,
                'device_model' => $tokenData['device_model'] ?? null,
                'os_version' => $tokenData['os_version'] ?? null,
                'is_active' => true,
                'last_used_at' => now(),
            ]);

            Log::info('Device token registered successfully', [
                'user_id' => $userId,
                'enterprise_id' => $enterpriseId,
                'platform' => $tokenData['platform'] ?? 'android'
            ]);

            return $deviceToken;

        } catch (\Exception $e) {
            Log::error('Device token registration error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Unregister device token
     */
    public function unregisterToken($userId, $deviceToken)
    {
        try {
            DeviceToken::where('user_id', $userId)
                      ->where('device_token', $deviceToken)
                      ->update(['is_active' => false]);

            Log::info('Device token unregistered successfully', [
                'user_id' => $userId,
                'token' => substr($deviceToken, 0, 20) . '...'
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error('Device token unregistration error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Clean up inactive tokens
     */
    public function cleanupInactiveTokens($daysOld = 30)
    {
        try {
            $cutoffDate = now()->subDays($daysOld);
            
            $deletedCount = DeviceToken::where('is_active', false)
                                    ->where('updated_at', '<', $cutoffDate)
                                    ->delete();

            Log::info("Cleaned up {$deletedCount} inactive device tokens older than {$daysOld} days");
            
            return $deletedCount;

        } catch (\Exception $e) {
            Log::error('Device token cleanup error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send test notification
     */
    public function sendTestNotification($deviceToken, $platform = 'android')
    {
        return $this->sendNotification(
            $deviceToken,
            'Test Notification',
            'This is a test notification from Hospital Management System',
            ['type' => 'test', 'timestamp' => now()->toISOString()],
            $platform
        );
    }

    /**
     * Send notification to a single device with custom content
     */
    public function sendToDevice($deviceToken, $notification, $platform = 'android')
    {
        return $this->sendNotification(
            $deviceToken,
            $notification['title'],
            $notification['body'],
            $notification['data'] ?? [],
            $platform
        );
    }
}
