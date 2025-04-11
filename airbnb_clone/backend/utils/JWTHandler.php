<?php
require_once __DIR__ . '/../config/constants.php';

class JWTHandler {
    private $secret;
    private $expiration;

    public function __construct() {
        $this->secret = JWT_SECRET;
        $this->expiration = JWT_EXPIRATION;
    }

    public function generateToken($data) {
        $header = json_encode([
            'typ' => 'JWT',
            'alg' => 'HS256'
        ]);

        $payload = json_encode(array_merge(
            $data,
            [
                'iat' => time(),
                'exp' => time() + $this->expiration
            ]
        ));

        $base64UrlHeader = $this->base64UrlEncode($header);
        $base64UrlPayload = $this->base64UrlEncode($payload);
        $signature = $this->generateSignature($base64UrlHeader, $base64UrlPayload);
        $base64UrlSignature = $this->base64UrlEncode($signature);

        return $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
    }

    public function validateToken($token) {
        try {
            $parts = explode('.', $token);
            if (count($parts) !== 3) {
                throw new Exception('Invalid token format');
            }

            list($base64UrlHeader, $base64UrlPayload, $base64UrlSignature) = $parts;

            $signature = $this->base64UrlDecode($base64UrlSignature);
            $expectedSignature = $this->generateSignature($base64UrlHeader, $base64UrlPayload);

            if (!hash_equals($signature, $expectedSignature)) {
                throw new Exception('Invalid signature');
            }

            $payload = json_decode($this->base64UrlDecode($base64UrlPayload));
            
            if (!isset($payload->exp) || $payload->exp < time()) {
                throw new Exception('Token has expired');
            }

            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    public function getTokenData($token) {
        try {
            // Remove 'Bearer ' if present
            $token = str_replace('Bearer ', '', $token);
            
            $parts = explode('.', $token);
            if (count($parts) !== 3) {
                throw new Exception('Invalid token format');
            }

            list($base64UrlHeader, $base64UrlPayload, $base64UrlSignature) = $parts;

            if (!$this->validateToken($token)) {
                throw new Exception('Invalid token');
            }

            return json_decode($this->base64UrlDecode($base64UrlPayload));
        } catch (Exception $e) {
            throw new Exception('Failed to get token data: ' . $e->getMessage());
        }
    }

    private function generateSignature($base64UrlHeader, $base64UrlPayload) {
        return hash_hmac(
            'sha256',
            $base64UrlHeader . "." . $base64UrlPayload,
            $this->secret,
            true
        );
    }

    private function base64UrlEncode($data) {
        $base64 = base64_encode($data);
        $base64Url = strtr($base64, '+/', '-_');
        return rtrim($base64Url, '=');
    }

    private function base64UrlDecode($data) {
        $base64 = strtr($data, '-_', '+/');
        $padLength = 4 - (strlen($base64) % 4);
        if ($padLength < 4) {
            $base64 .= str_repeat('=', $padLength);
        }
        return base64_decode($base64);
    }

    public function refreshToken($token) {
        try {
            $data = $this->getTokenData($token);
            unset($data->iat);
            unset($data->exp);
            return $this->generateToken((array)$data);
        } catch (Exception $e) {
            throw new Exception('Failed to refresh token: ' . $e->getMessage());
        }
    }

    public function isTokenExpired($token) {
        try {
            $data = $this->getTokenData($token);
            return !isset($data->exp) || $data->exp < time();
        } catch (Exception $e) {
            return true;
        }
    }

    public function getTokenExpiration($token) {
        try {
            $data = $this->getTokenData($token);
            return isset($data->exp) ? $data->exp : null;
        } catch (Exception $e) {
            return null;
        }
    }

    public function getRemainingTime($token) {
        $expiration = $this->getTokenExpiration($token);
        if ($expiration === null) {
            return 0;
        }
        return max(0, $expiration - time());
    }
}
?>
