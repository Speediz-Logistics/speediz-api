<?php

namespace App\Traits;

use App\Models\Notification;
use Kreait\Firebase\Messaging;
use Kreait\Firebase\Factory;
use Illuminate\Support\Facades\Log;
use Throwable;

trait FCM
{
    protected function messaging(): Messaging
    {
        logger('fcm config', config('firebase.credentials'));
        return (new Factory)
            ->withServiceAccount(config('firebase.credentials'))
            ->createMessaging();
    }

    /**
     * Send FCM Notification to Device Token
     */
    public function sendToToken(string $userId,string $token, string $title, string $body, array $data = []): bool
    {
        try {
            $message = [
                'token' => $token,
                'notification' => [
                    'title' => $title,
                    'body' => $body,
                ],
                'data' => $data,
            ];

            $fcm = $this->messaging()->send($message);

            $notification = Notification::query()->create([
                'user_id' => $userId,
                'title' => $title,
                'body' => $body,
                'image_url' => $data['image_url'] ?? null,
            ]);

            //log fcm
            Log::info("FCM sent to token: " . $token . " Message ID: " . $notification->id);
            return true;

        } catch (Throwable $e) {
            Log::error("FCM Token Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send to Topic
     */
    public function sendToTopic(string $topic, string $title, string $body, array $data = []): bool
    {
        try {
            $message = [
                'topic' => $topic,
                'notification' => [
                    'title' => $title,
                    'body' => $body,
                ],
                'data' => $data,
            ];

            $this->messaging()->send($message);

            Notification::query()->create([
                'user_id' => null,
                'title' => $title,
                'body' => $body,
                'image_url' => $data['image_url'] ?? null,
            ]);
            return true;

        } catch (Throwable $e) {
            Log::error("FCM Topic Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send Data Only (Silent push)
     */
    public function sendDataMessage(string $token, array $data): bool
    {
        try {
            $message = [
                'token' => $token,
                'data' => $data,
            ];

            $this->messaging()->send($message);
            return true;

        } catch (Throwable $e) {
            Log::error("FCM Data Error: " . $e->getMessage());
            return false;
        }
    }
}
