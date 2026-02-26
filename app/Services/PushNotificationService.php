<?php

namespace App\Services;

use App\Models\Notification;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification as FirebaseNotification;
use Kreait\Firebase\Messaging\RegistrationToken;

class PushNotificationService
{
    private $send_and_store = false;
    public function __construct(public string $title, public string $body) {}

    function notify(array $fcm_token)
    {
        $factory = app('firebase.messaging');
        $messaging = $factory->createMessaging();

        info("MEDISHOP Notification(Cron Job) running at " . now());

        $successCount = 0;
        $failureCount = 0;
        $errors = [];

        foreach ($fcm_token as $row) {
            try {
                $title = $this->title;
                $body = $this->body;

                $notification = FirebaseNotification::create($title, $body);

                $message = CloudMessage::new()
                    ->withNotification($notification)
                    ->withData([
                        'type' => 'screen',
                    ]);

                $response = $messaging->sendMulticast($message, [
                    RegistrationToken::fromValue($row)
                ]);

                if ($response->successes()->count() > 0) {
                    if ($this->send_and_store) {                        
                        Notification::create([
                            'title' => $title,
                            'body' => $body,
                            'notified_at' => now(),
                            'data' => json_encode(['type' => 'screen']),
                        ]);
                    }
                    $successCount++;
                } else {
                    $failureCount++;
                    foreach ($response->failures() as $failure) {
                        $errors[] = $failure->error()->getMessage();
                        Log::error("FCM error for token {$row}: " . $failure->error()->getMessage());
                    }
                }
            } catch (\Throwable $e) {
                $failureCount++;
                $errors[] = $e->getMessage();
                Log::error("Failed to send FCM to {$row}: " . $e->getMessage());
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
    }

    function store() {
        $this->send_and_store = true;
    }
}
