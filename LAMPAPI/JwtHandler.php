<?php
require_once __DIR__ . '/vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JwtHandler
{
    private static $secret = 'WqGsVn9D7mpDE0Txy1WvFZQm+P9zlsVcvAg4g+HY7H8='; // Replace with your real secret key
    private static $algo = 'HS256';

    // Create a new JWT token
    public static function generateToken($payload)
    {
        $issuedAt = time();
        $expiration = $issuedAt + (60 * 60); // Token valid for 1 hour

        $tokenPayload = array_merge($payload, [
            'iat' => $issuedAt,
            'exp' => $expiration
        ]);

        return JWT::encode($tokenPayload, self::$secret, self::$algo);
    }

    // Decode and verify the token
    public static function validateToken($token)
    {
        try {
            $decoded = JWT::decode($token, new Key(self::$secret, self::$algo));
            return [
                'valid' => true,
                'payload' => (array)$decoded
            ];
        } catch (\Exception $e) {
            return [
                'valid' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}
?>
