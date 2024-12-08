<?php

require_once '../logger/Logger.php';

class JWTValidator {
    private const JWT_SECRET_KEY = 'gasdf23bd872bncyhbnasdiufg837';
    private $logger;

    public function __construct() {
        $this->logger = Logger::getInstance();
    }

    /**
     * Validate a JWT token.
     *
     * @param string $jwt The token to validate.
     * @return array|false Decoded payload if valid, false otherwise.
     */
    public function validateJWT(string $jwt) {
        $parts = explode('.', $jwt);

        if (count($parts) !== 3) {
            $this->logger->log("Invalid JWT structure");
            return false; // Invalid structure
        }

        [$base64UrlHeader, $base64UrlPayload, $base64UrlSignature] = $parts;

        // Decode the header and payload
        $header = json_decode($this->base64UrlDecode($base64UrlHeader), true);
        $payload = json_decode($this->base64UrlDecode($base64UrlPayload), true);

        if (!$header || !$payload) {
            $this->logger->log("Invalid JWT encoding");
            return false; // Invalid encoding
        }

        // Recalculate the signature
        $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, self::JWT_SECRET_KEY, true);
        $base64UrlSignatureValid = $this->base64UrlEncode($signature);

        if (!hash_equals($base64UrlSignatureValid, $base64UrlSignature)) {
            $this->logger->log("Invalid JWT signature");
            return false; // Invalid signature
        }

        // Check token expiration
        if (isset($payload['exp']) && $payload['exp'] < time()) {
            $this->logger->log("JWT token expired");
            return false; // Token expired
        }

        $this->logger->log("Valid JWT for user ID: " . ($payload['user_id'] ?? 'unknown'));
        return $payload; // Valid token
    }

    /**
     * Decode a Base64 URL-safe string.
     *
     * @param string $data The Base64 URL-safe string to decode.
     * @return string Decoded string.
     */
    private function base64UrlDecode(string $data): string {
        return base64_decode(str_replace(['-', '_'], ['+', '/'], $data));
    }

    /**
     * Encode data to Base64 URL-safe string.
     *
     * @param string $data The string to encode.
     * @return string Encoded string.
     */
    private function base64UrlEncode(string $data): string {
        return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($data));
    }
}