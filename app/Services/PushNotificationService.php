<?php

namespace App\Services;

use App\Models\Notification;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification as FirebaseNotification;

class PushNotificationService
{
    private $send_and_store = false;

    public function __construct(public string $title, public string $body) {}

    public function notify(array $fcm_token)
    {
        try {
            // Initialize Firebase with your service account from root directory
            $factory = (new Factory)->withServiceAccount(
                base_path('nepal-medishop-firebase-adminsdk-fbsvc-7deb99b9c4.json')
            );

            $messaging = $factory->createMessaging();

            info("MEDISHOP Notification(Cron Job) running at " . now());

            $successCount = 0;
            $failureCount = 0;
            $errors = [];

            foreach ($fcm_token as $token) {
                try {
                    $notification = FirebaseNotification::create($this->title, $this->body);

                    $message = CloudMessage::withTarget('token', $token)
                        ->withNotification($notification)
                        ->withData([
                            'type' => 'screen',
                        ]);

                    $messaging->send($message);

                    $successCount++;

                    if ($this->send_and_store) {
                        Notification::create([
                            'title' => $this->title,
                            'body' => $this->body,
                            'notified_at' => now(),
                            'data' => json_encode(['type' => 'screen']),
                        ]);
                    }
                } catch (\Kreait\Firebase\Exception\MessagingException $e) {
                    $failureCount++;
                    $errors[] = "Token {$token}: " . $e->getMessage();
                    Log::error("FCM error for token {$token}: " . $e->getMessage());
                } catch (\Throwable $e) {
                    $failureCount++;
                    $errors[] = "Token {$token}: " . $e->getMessage();
                    Log::error("Failed to send FCM to {$token}: " . $e->getMessage());
                }
            }

            $output = [
                'successes' => $successCount,
                'failures' => $failureCount,
                'errors' => $errors,
                'date' => now()->format('Y-m-d H:i:s')
            ];

            Log::info('Notification summary', $output);
            return $output;
        } catch (\Throwable $e) {
            Log::error("Firebase initialization failed: " . $e->getMessage());
            return [
                'successes' => 0,
                'failures' => count($fcm_token),
                'errors' => [$e->getMessage()],
                'date' => now()->format('Y-m-d H:i:s')
            ];
        }
    }

    public function store()
    {
        $this->send_and_store = true;
        return $this;
    }
}
