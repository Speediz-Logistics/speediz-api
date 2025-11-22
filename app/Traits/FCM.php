<?php

namespace App\Traits;

use Google\Client as GoogleClient;

trait FCM
{
    public function sendFirebaseNotification(
        string $body,
        array $data = [],
        string $topic = null,
        string $title = "Notification",
        string $image = null,
        string $deviceToken = null,
    ){
        try {
            $token = $this->getFireBaseAccessToken();
            $url = env('FIREBASE_URL');

            // Target (topic or device token)
            $target = [];

            if (!empty($topic)) {
                $target['topic'] = $topic;
            }

            if (!empty($deviceToken)) {
                $target['token'] = $deviceToken;
            }

            if (empty($target)) {
                return [
                    "success" => false,
                    "message" => "Topic or Device Token must be provided",
                ];
            }

            // Payload
            $postData = [
                "message" => array_merge($target, [
                    "notification" => [
                        "title" => $title,
                        "body" => $body,
                        "image" => $image,
                    ],
                    "data" => $this->stringifyData(array_merge([
                        "click_action" => "FLUTTER_NOTIFICATION_CLICK",
                    ], $data)),
                    "android" => [
                        "priority" => "high",
                        "notification" => [
                            "sound" => "notification_alert.mp3",
                        ],
                    ],
                ])
            ];

            $jsonData = json_encode($postData);

            // Initialize cURL
            $curl = curl_init();

            curl_setopt_array($curl, [
                CURLOPT_URL => $url,
                CURLOPT_POST => true,
                CURLOPT_HTTPHEADER => [
                    "Content-Type: application/json",
                    "Authorization: Bearer {$token}",
                ],
                CURLOPT_POSTFIELDS => $jsonData,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_HEADER => true,       // Capture headers
            ]);

            $response = curl_exec($curl);

            // cURL error handling
            if ($response === false) {
                return [
                    "success" => false,
                    "curl_error" => curl_error($curl),
                ];
            }

            // Extract HTTP Status Code
            $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

            // Separate headers and body
            $headerSize = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
            $header = substr($response, 0, $headerSize);
            $body = substr($response, $headerSize);

            curl_close($curl);

            // Try to decode JSON body
            $decoded = json_decode($body, true);

            // Log for debugging
            logger("Firebase HTTP Code: " . $httpCode);
            logger("Firebase Response Raw: " . $body);

            // Return structured response
            return [
                "success" => $httpCode >= 200 && $httpCode < 300,
                "http_code" => $httpCode,
                "headers" => $this->parseHeaders($header),
                "raw_response" => $body,
                "json_response" => $decoded,
            ];

        } catch (\Exception $e) {
            logger("Firebase Notification Error: " . $e->getMessage());
            return [
                "success" => false,
                "exception" => $e->getMessage(),
            ];
        }
    }

    private function getFireBaseAccessToken()
    {
        $filePath = public_path(env('GOOGLE_CREDENTIALS_PATH'));
        $scope = env('FIREBASE_SCOPE_MESSAGE_URL');

        $client = new GoogleClient();
        $client->setAuthConfig($filePath);
        $client->addScope($scope);

        $response = $client->fetchAccessTokenWithAssertion();
        return $response['access_token'] ?? false;
    }

    /**
     * Parse response headers into an associative array
     */
    private function parseHeaders($headerString)
    {
        $headers = [];
        $lines = explode("\n", $headerString);

        foreach ($lines as $line) {
            if (strpos($line, ':') !== false) {
                [$key, $value] = explode(': ', $line, 2);
                $headers[$key] = trim($value);
            }
        }

        return $headers;
    }

    private function stringifyData(array $data): array
    {
        $stringified = [];

        foreach ($data as $key => $value) {
            // Convert everything to string
            if (is_array($value)) {
                // Convert nested arrays to JSON string
                $stringified[$key] = json_encode($value);
            } else {
                $stringified[$key] = (string) $value;
            }
        }

        return $stringified;
    }

}
