<?php

namespace App\Services;

use App\Contracts\AuthServiceInterface;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthService implements AuthServiceInterface
{
    // REGISTER
    public function register(array $data): array
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        $token = $user
            ->createToken('vue-app')
            ->plainTextToken;

        return [
            'user' => $user,
            'token' => $token,
        ];
    }

    // LOGIN
    public function login(array $credentials): ?array
    {
        $user = User::where(
            'email',
            $credentials['email']
        )->first();

        if (
            !$user ||
            !Hash::check(
                $credentials['password'],
                $user->password
            )
        ) {
            return null;
        }

        $token = $user
            ->createToken('vue-app')
            ->plainTextToken;

        return [
            'user' => $user,
            'token' => $token,
        ];
    }

    // LOGOUT
    
public function logout(User $user): void
{
    /** @var PersonalAccessToken|null $token */
    $token = $user->currentAccessToken();

    $token?->delete();
}


}
