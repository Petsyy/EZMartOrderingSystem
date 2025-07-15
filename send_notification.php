<?php
function sendPushNotification($fcmToken, $title, $body, $conn = null, $orderId = null)
{
    require_once "get_fcm_token.php";
    $accessToken = getOAuthToken();

    if (strpos($accessToken, "Error") !== false) {
        return ["success" => false, "error" => $accessToken];
    }

    $fcmUrl = "https://fcm.googleapis.com/v1/projects/ezmart-f178a/messages:send";

    $notificationPayload = [
        "message" => [
            "token" => $fcmToken,
            "notification" => [
                "title" => $title,
                "body" => $body
            ],
            "android" => [
                "priority" => "high"
            ]
        ]
    ];

    $headers = [
        "Authorization: Bearer " . $accessToken,
        "Content-Type: application/json"
    ];

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $fcmUrl,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POSTFIELDS => json_encode($notificationPayload)
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $responseData = json_decode($response, true);
    curl_close($ch);

    // Handle invalid token
    if (
        $httpCode === 404 && isset($responseData['error']['details'][0]['errorCode']) &&
        $responseData['error']['details'][0]['errorCode'] === 'UNREGISTERED'
    ) {
        return [
            "success" => false,
            "error" => "UNREGISTERED",
            "message" => "FCM token is invalid"
        ];
    }

    return [
        "success" => $httpCode === 200,
        "http_code" => $httpCode,
        "response" => $responseData
    ];
}
