<?php

namespace App\Services\Notification;

use App\Models\Notification;
use App\Models\User;
use App\Models\UserDevice;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PushNotificationService
{
    protected const EXPO_PUSH_URL = 'https://exp.host/--/api/v2/push/send';

    /**
     * Send a notification to a specific User or User ID.
     *
     * @param  User|int  $user
     * @param  string  $title
     * @param  string  $body
     * @param  array  $data Custom metadata / deep-linking payload
     * @param  string|null  $type Notification category/type (e.g. 'booking', 'reminder', 'system')
     * @param  array  $options Custom notification options (sound, priority, badge, channelId, save_to_db)
     * @return array
     */
    public function sendToUser(
        User|int $user,
        string $title,
        string $body,
        array $data = [],
        ?string $type = null,
        array $options = []
    ): array {
        $userId = $user instanceof User ? $user->id : (int) $user;

        $devices = UserDevice::where('user_id', $userId)
            ->whereNotNull('push_notification_token')
            ->where('push_notifications_enabled', true)
            ->where('is_active', true)
            ->get();

        $saveToDb = $options['save_to_db'] ?? true;
        $dbNotification = null;

        if ($saveToDb) {
            $dbNotification = $this->recordInDatabase(
                userId: $userId,
                title: $title,
                body: $body,
                type: $type ?? ($data['type'] ?? 'general'),
                data: $data
            );
        }

        if ($devices->isEmpty()) {
            return [
                'success' => false,
                'message' => "No registered push devices found for user ID: {$userId}",
                'user_id' => $userId,
                'devices_count' => 0,
                'db_notification_id' => $dbNotification?->id,
            ];
        }

        $tokens = $devices->pluck('push_notification_token')->unique()->values()->all();

        $response = $this->sendToTokens($tokens, $title, $body, array_merge($data, [
            'notification_id' => $dbNotification?->id,
            'type' => $type ?? ($data['type'] ?? 'general'),
        ]), $options);

        return [
            'success' => $response['success'] ?? false,
            'user_id' => $userId,
            'devices_count' => count($tokens),
            'expo_response' => $response,
            'db_notification_id' => $dbNotification?->id,
        ];
    }

    /**
     * Send notifications to multiple users.
     *
     * @param  iterable<User|int>  $users
     * @param  string  $title
     * @param  string  $body
     * @param  array  $data
     * @param  string|null  $type
     * @param  array  $options
     * @return array
     */
    public function sendToUsers(
        iterable $users,
        string $title,
        string $body,
        array $data = [],
        ?string $type = null,
        array $options = []
    ): array {
        $userIds = [];
        foreach ($users as $user) {
            $userIds[] = $user instanceof User ? $user->id : (int) $user;
        }

        $userIds = array_values(array_unique(array_filter($userIds)));

        if (empty($userIds)) {
            return [
                'success' => false,
                'message' => 'No valid users provided.',
                'notified_count' => 0,
            ];
        }

        $results = [];
        foreach ($userIds as $userId) {
            $results[$userId] = $this->sendToUser($userId, $title, $body, $data, $type, $options);
        }

        return [
            'success' => true,
            'total_users' => count($userIds),
            'results' => $results,
        ];
    }

    /**
     * Send direct push notifications to a list of Expo tokens.
     *
     * @param  array<string>  $tokens
     * @param  string  $title
     * @param  string  $body
     * @param  array  $data
     * @param  array  $options
     * @return array
     */
    public function sendToTokens(
        array $tokens,
        string $title,
        string $body,
        array $data = [],
        array $options = []
    ): array {
        $validTokens = array_values(array_unique(array_filter($tokens, function ($token) {
            return ! empty($token) && (str_starts_with($token, 'ExponentPushToken[') || str_starts_with($token, 'ExpoPushToken['));
        })));

        if (empty($validTokens)) {
            // Also accept other token formats if Expo supports them
            $validTokens = array_values(array_unique(array_filter($tokens)));
        }

        if (empty($validTokens)) {
            return [
                'success' => false,
                'message' => 'No valid push tokens provided.',
                'sent_count' => 0,
            ];
        }

        $messages = [];
        foreach ($validTokens as $token) {
            $message = [
                'to' => $token,
                'sound' => $options['sound'] ?? 'default',
                'title' => $title,
                'body' => $body,
                'data' => $data,
                'channelId' => $options['channelId'] ?? 'default',
                'priority' => $options['priority'] ?? 'high',
            ];

            if (isset($options['badge'])) {
                $message['badge'] = (int) $options['badge'];
            }

            if (isset($options['subtitle'])) {
                $message['subtitle'] = (string) $options['subtitle'];
            }

            $messages[] = $message;
        }

        // Expo allows maximum 100 messages per HTTP batch
        $chunks = array_chunk($messages, 100);
        $responses = [];
        $overallSuccess = true;

        foreach ($chunks as $chunk) {
            try {
                $httpResponse = Http::timeout(10)->withHeaders([
                    'Accept' => 'application/json',
                    'Accept-Encoding' => 'gzip, deflate',
                    'Content-Type' => 'application/json',
                ])->post(self::EXPO_PUSH_URL, $chunk);

                $responseData = $httpResponse->json();
                $responses[] = $responseData;

                if (! $httpResponse->successful()) {
                    $overallSuccess = false;
                    Log::warning('Expo Push notification batch failed', [
                        'status' => $httpResponse->status(),
                        'response' => $responseData,
                    ]);
                }
            } catch (\Throwable $e) {
                $overallSuccess = false;
                Log::error('Expo Push notification exception', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
            }
        }

        return [
            'success' => $overallSuccess,
            'sent_count' => count($validTokens),
            'batches_count' => count($chunks),
            'responses' => $responses,
        ];
    }

    /**
     * Send topic / role broadcast notification.
     *
     * @param  string  $topic Topic name, e.g. 'general', 'booking', 'promo', 'updates'
     * @param  string  $title
     * @param  string  $body
     * @param  array  $data
     * @param  string|null  $targetRole Target user role (e.g. 'patient', 'partner', null for all)
     * @return array
     */
    public function sendToTopic(
        string $topic,
        string $title,
        string $body,
        array $data = [],
        ?string $targetRole = null
    ): array {
        $query = User::query();

        if ($targetRole === 'patient') {
            $query->whereHas('patient');
        } elseif ($targetRole === 'partner') {
            $query->whereHas('partner');
        }

        $userIds = $query->pluck('id')->all();

        return $this->sendToUsers(
            users: $userIds,
            title: $title,
            body: $body,
            data: array_merge($data, ['topic' => $topic]),
            type: $topic
        );
    }

    /**
     * Store in-app notification in database.
     */
    public function recordInDatabase(
        int $userId,
        string $title,
        string $body,
        string $type,
        array $data = []
    ): Notification {
        return Notification::create([
            'user_id' => $userId,
            'title' => $title,
            'body' => $body,
            'type' => $type,
            'data' => $data,
            'is_read' => false,
        ]);
    }
}
