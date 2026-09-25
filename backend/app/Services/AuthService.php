<?php

namespace App\Services;

use App\Models\User;
use App\Models\Farmer;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthService
{
    /**
     * Register a new user (CUSTOMER by default).
     */
    public function register(array $data): array
    {
        return DB::transaction(function () use ($data) {
            $user = User::create([
                'name'         => $data['name'],
                'email'        => $data['email'],
                'phone_number' => $data['phone_number'] ?? null,
                'password'     => Hash::make($data['password']),
                'role'         => $data['role'] ?? 'CUSTOMER',
            ]);

            $token = $user->createToken('auth_token')->plainTextToken;

            return [
                'user'  => $user,
                'token' => $token,
            ];
        });
    }

    /**
     * Authenticate via email or phone + password.
     */
    public function login(array $credentials): array
    {
        $field = filter_var($credentials['login'], FILTER_VALIDATE_EMAIL) ? 'email' : 'phone_number';

        $user = User::where($field, $credentials['login'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'login' => ['The provided credentials are incorrect.'],
            ]);
        }

        if ($user->status === 'SUSPENDED') {
            throw ValidationException::withMessages([
                'login' => ['Your account has been suspended. Please contact support.'],
            ]);
        }

        // Revoke all old tokens on new login (single session per user)
        $user->tokens()->delete();
        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'user'  => $user,
            'token' => $token,
        ];
    }

    /**
     * Logout the current token.
     */
    public function logout(User $user): void
    {
        $user->currentAccessToken()->delete();
    }

    /**
     * Register a farmer account (creates User + Farmer profile in one transaction).
     */
    public function registerFarmer(array $data): array
    {
        return DB::transaction(function () use ($data) {
            $user = User::create([
                'name'         => $data['name'],
                'email'        => $data['email'],
                'phone_number' => $data['phone_number'] ?? null,
                'password'     => Hash::make($data['password']),
                'role'         => 'FARMER',
            ]);

            $farmer = Farmer::create([
                'user_id'               => $user->id,
                'farm_name'             => $data['farm_name'],
                'slug'                  => Str::slug($data['farm_name']) . '-' . Str::random(5),
                'bio'                   => $data['bio'] ?? null,
                'primary_specialization'=> $data['primary_specialization'] ?? null,
            ]);

            $token = $user->createToken('auth_token')->plainTextToken;

            return [
                'user'   => $user,
                'farmer' => $farmer,
                'token'  => $token,
            ];
        });
    }
}
