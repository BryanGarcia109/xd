<?php

namespace App\Services;

use App\Models\User;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

/**
 * Servicio de Autenticación
 */
class AuthService
{
    private User $userModel;
    private string $jwtSecret;

    public function __construct()
    {
        $this->userModel = new User();
        $this->jwtSecret = getenv('JWT_SECRET') ?: 'default-secret-key';
    }

    /**
     * Genera un token JWT para un usuario
     *
     * @param array $user
     * @return string
     */
    public function generateToken(array $user): string
    {
        $payload = [
            'user_id' => $user['id'],
            'email' => $user['email'],
            'role' => $user['role'],
            'iat' => time(),
            'exp' => time() + (60 * 60 * 24) // 24 horas
        ];

        return JWT::encode($payload, $this->jwtSecret, 'HS256');
    }

    /**
     * Valida un token JWT
     *
     * @param string $token
     * @return array|false
     */
    public function validateToken(string $token)
    {
        try {
            $decoded = JWT::decode($token, new Key($this->jwtSecret, 'HS256'));
            return (array) $decoded;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Autentica un usuario
     *
     * @param string $email
     * @param string $password
     * @return array|false
     */
    public function authenticate(string $email, string $password)
    {
        $user = $this->userModel->findByEmail($email);
        
        if (!$user || !password_verify($password, $user['password_hash'])) {
            return false;
        }

        return $user;
    }

    /**
     * Genera token de recuperación de contraseña
     *
     * @return string
     */
    public function generateResetToken(): string
    {
        return bin2hex(random_bytes(32));
    }
}

