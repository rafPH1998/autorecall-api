<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:6'],
        ]);

        $user = User::query()
            ->where('email', strtolower(trim($data['email'])))
            ->first();

        if (! $user || ! $user->active || ! password_verify($data['password'], $user->password)) {
            return response()->json(['message' => 'E-mail ou senha inválidos.'], 401);
        }

        $user->tokens()->delete();

        return [
            'token' => $user->createToken('auth')->plainTextToken,
            'user' => new UserResource($user),
        ];
    }

    public function forgotPassword()
    {
        return ['message' => 'Se o e-mail existir, enviaremos as instruções de recuperação.'];
    }

    public function me(Request $request): UserResource
    {
        return new UserResource($request->user());
    }
}
