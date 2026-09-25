<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResponseTrait;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    use ApiResponseTrait;

    public function __construct(private readonly AuthService $authService) {}

    /**
     * Register a new customer account.
     * POST /api/auth/register
     */
    public function register(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'         => ['required', 'string', 'max:255'],
            'email'        => ['required', 'email', 'unique:users,email'],
            'phone_number' => ['nullable', 'string', 'unique:users,phone_number'],
            'password'     => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()],
        ]);

        $result = $this->authService->register($data);

        return $this->successResponse([
            'user'  => $result['user'],
            'token' => $result['token'],
        ], 'Registration successful.', 201);
    }

    /**
     * Register a new farmer account.
     * POST /api/auth/register/farmer
     */
    public function registerFarmer(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'                    => ['required', 'string', 'max:255'],
            'email'                   => ['required', 'email', 'unique:users,email'],
            'phone_number'            => ['required', 'string', 'unique:users,phone_number'],
            'password'                => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()],
            'farm_name'               => ['required', 'string', 'max:255'],
            'bio'                     => ['nullable', 'string'],
            'primary_specialization'  => ['nullable', 'string', 'max:100'],
        ]);

        $result = $this->authService->registerFarmer($data);

        return $this->successResponse([
            'user'   => $result['user'],
            'farmer' => $result['farmer'],
            'token'  => $result['token'],
        ], 'Farmer registration successful. Your profile is under review.', 201);
    }

    /**
     * Login with email/phone + password.
     * POST /api/auth/login
     */
    public function login(Request $request): JsonResponse
    {
        $data = $request->validate([
            'login'    => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $result = $this->authService->login($data);

        return $this->successResponse([
            'user'  => $result['user'],
            'token' => $result['token'],
        ], 'Login successful.');
    }

    /**
     * Logout and revoke current token.
     * POST /api/auth/logout
     */
    public function logout(Request $request): JsonResponse
    {
        $this->authService->logout($request->user());
        return $this->successResponse(null, 'Logged out successfully.');
    }

    /**
     * Get authenticated user profile.
     * GET /api/auth/me
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user()->load('farmer');
        return $this->successResponse($user);
    }
}
