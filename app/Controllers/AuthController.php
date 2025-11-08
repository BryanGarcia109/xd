<?php

namespace App\Controllers;

use App\Models\User;
use App\Services\AuthService;
use App\Helpers\ValidationHelper;
use App\Helpers\SanitizeHelper;
use App\Helpers\LogHelper;
use App\Middlewares\RateLimitMiddleware;

/**
 * Controlador de Autenticación
 */
class AuthController extends BaseController
{
    private AuthService $authService;
    private User $userModel;
    private RateLimitMiddleware $rateLimit;

    public function __construct()
    {
        $this->authService = new AuthService();
        $this->userModel = new User();
        $this->rateLimit = new RateLimitMiddleware(20, 900); // 20 requests por 15 minutos (aumentado para desarrollo)
    }

    /**
     * Registro de usuario
     * POST /api/auth/register
     */
    public function register(): void
    {
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) {
            $this->errorResponse('Datos inválidos', 400);
            return;
        }

        // Validaciones
        $errors = [];
        if (!ValidationHelper::required($input, 'nombre')) {
            $errors[] = 'El nombre es requerido';
        }
        if (!ValidationHelper::required($input, 'email')) {
            $errors[] = 'El email es requerido';
        } elseif (!ValidationHelper::email($input['email'])) {
            $errors[] = 'El email no es válido';
        }
        if (!ValidationHelper::required($input, 'telefono')) {
            $errors[] = 'El teléfono es requerido';
        }
        if (!ValidationHelper::required($input, 'password')) {
            $errors[] = 'La contraseña es requerida';
        } elseif (!ValidationHelper::minLength($input['password'], 6)) {
            $errors[] = 'La contraseña debe tener al menos 6 caracteres';
        }

        if (!empty($errors)) {
            $this->errorResponse(implode(', ', $errors), 400);
            return;
        }

        // Sanitizar datos
        $email = SanitizeHelper::email($input['email']);
        
        // Verificar si el email ya existe
        $existingUser = $this->userModel->findByEmail($email);
        if ($existingUser) {
            $this->errorResponse('El email ya está registrado', 409);
            return;
        }

        // Crear usuario
        try {
            $userId = $this->userModel->create([
                'nombre' => SanitizeHelper::string($input['nombre']),
                'email' => $email,
                'telefono' => SanitizeHelper::string($input['telefono']),
                'password_hash' => password_hash($input['password'], PASSWORD_DEFAULT),
                'role' => 'client'
            ]);

            LogHelper::log('user_registered', ['user_id' => $userId, 'email' => $email]);

            $this->successResponse([
                'user_id' => $userId,
                'message' => 'Usuario registrado correctamente'
            ], 'Registro exitoso', 201);
        } catch (\Exception $e) {
            LogHelper::log('register_error', ['error' => $e->getMessage()]);
            $this->errorResponse('Error al registrar el usuario', 500);
        }
    }

    /**
     * Login de usuario
     * POST /api/auth/login
     */
    public function login(): void
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        if (!$this->rateLimit->check("login_{$ip}")) {
            return; // Rate limit excedido
        }

        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) {
            $this->errorResponse('Datos inválidos', 400);
            return;
        }

        // Validaciones
        if (!ValidationHelper::required($input, 'email') || !ValidationHelper::required($input, 'password')) {
            $this->errorResponse('Email y contraseña son requeridos', 400);
            return;
        }

        $email = SanitizeHelper::email($input['email']);
        $password = $input['password'];

        // Autenticar
        $user = $this->authService->authenticate($email, $password);
        if (!$user) {
            LogHelper::log('login_failed', ['email' => $email, 'ip' => $ip]);
            $this->errorResponse('Credenciales inválidas', 401);
            return;
        }

        // Generar token JWT
        $token = $this->authService->generateToken($user);

        // Crear sesión para panel web (opcional)
        session_start();
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['role'];

        LogHelper::log('login_success', ['user_id' => $user['id'], 'email' => $email], $user['id']);

        $this->successResponse([
            'token' => $token,
            'user' => [
                'id' => $user['id'],
                'nombre' => $user['nombre'],
                'email' => $user['email'],
                'role' => $user['role']
            ]
        ], 'Login exitoso');
    }

    /**
     * Recuperación de contraseña
     * POST /api/auth/forgot-password
     */
    public function forgotPassword(): void
    {
        $input = json_decode(file_get_contents('php://input'), true);
        if (!ValidationHelper::required($input, 'email')) {
            $this->errorResponse('El email es requerido', 400);
            return;
        }

        $email = SanitizeHelper::email($input['email']);
        $user = $this->userModel->findByEmail($email);

        // Por seguridad, siempre retornamos éxito aunque el usuario no exista
        if ($user) {
            $token = $this->authService->generateResetToken();
            $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
            
            $this->userModel->saveResetToken($email, $token, $expires);

            // Aquí deberías enviar el email con el token
            // Por ahora solo lo logueamos
            LogHelper::log('password_reset_requested', [
                'user_id' => $user['id'],
                'email' => $email,
                'token' => $token
            ]);
        }

        $this->successResponse([], 'Si el email existe, recibirás instrucciones para recuperar tu contraseña');
    }

    /**
     * Reset de contraseña
     * POST /api/auth/reset-password
     */
    public function resetPassword(): void
    {
        $input = json_decode(file_get_contents('php://input'), true);
        
        $errors = [];
        if (!ValidationHelper::required($input, 'token')) {
            $errors[] = 'El token es requerido';
        }
        if (!ValidationHelper::required($input, 'password')) {
            $errors[] = 'La contraseña es requerida';
        } elseif (!ValidationHelper::minLength($input['password'], 6)) {
            $errors[] = 'La contraseña debe tener al menos 6 caracteres';
        }

        if (!empty($errors)) {
            $this->errorResponse(implode(', ', $errors), 400);
            return;
        }

        $user = $this->userModel->findByResetToken($input['token']);
        if (!$user) {
            $this->errorResponse('Token inválido o expirado', 400);
            return;
        }

        // Actualizar contraseña
        $this->userModel->updateProfile($user['id'], [
            'password_hash' => password_hash($input['password'], PASSWORD_DEFAULT)
        ]);

        // Limpiar token
        $this->userModel->clearResetToken($user['email']);

        LogHelper::log('password_reset_completed', ['user_id' => $user['id']], $user['id']);

        $this->successResponse([], 'Contraseña actualizada correctamente');
    }

    /**
     * Obtener perfil del usuario actual
     * GET /api/auth/profile
     */
    public function profile(): void
    {
        $user = \App\Middlewares\JWTAuthMiddleware::getCurrentUser();
        if (!$user) {
            $this->errorResponse('No autenticado', 401);
            return;
        }

        $userData = $this->userModel->findById($user['user_id']);
        if (!$userData) {
            $this->errorResponse('Usuario no encontrado', 404);
            return;
        }

        unset($userData['password_hash']);
        $this->successResponse($userData);
    }

    /**
     * Actualizar perfil
     * PUT /api/auth/profile
     */
    public function updateProfile(): void
    {
        $user = \App\Middlewares\JWTAuthMiddleware::getCurrentUser();
        if (!$user) {
            $this->errorResponse('No autenticado', 401);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $updateData = [];

        if (isset($input['nombre'])) {
            $updateData['nombre'] = SanitizeHelper::string($input['nombre']);
        }
        if (isset($input['telefono'])) {
            $updateData['telefono'] = SanitizeHelper::string($input['telefono']);
        }
        if (isset($input['password'])) {
            if (!ValidationHelper::minLength($input['password'], 6)) {
                $this->errorResponse('La contraseña debe tener al menos 6 caracteres', 400);
                return;
            }
            $updateData['password_hash'] = password_hash($input['password'], PASSWORD_DEFAULT);
        }

        if (empty($updateData)) {
            $this->errorResponse('No hay datos para actualizar', 400);
            return;
        }

        $this->userModel->updateProfile($user['user_id'], $updateData);
        LogHelper::log('profile_updated', ['user_id' => $user['user_id']], $user['user_id']);

        $this->successResponse([], 'Perfil actualizado correctamente');
    }
}

