<?php
require_once __DIR__ . '/vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

$secretKey = 'WqGsVn9D7mpDE0Txy1WvFZQm+P9zlsVcvAg4g+HY7H8=';


function createJWT($userId, $firstName, $lastName)
{
    global $secretKey;

    $issuedAt = time();
    $expirationTime = $issuedAt + 3600; // 1 hour token validity

    $payload = [
        'iat' => $issuedAt,
        'exp' => $expirationTime,
        'data' => [
            'id' => $userId,
            'firstName' => $firstName,
            'lastName' => $lastName
        ]
    ];

    return JWT::encode($payload, $secretKey, 'HS256');
}

function validateJWT($token)
{
    global $secretKey;

    try {
        $decoded = JWT::decode($token, new Key($secretKey, 'HS256'));
        return (array) $decoded->data;
    } catch (Exception $e) {
        return null;
    }
}
