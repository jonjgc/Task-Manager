<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Company;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
            'company_name' => 'required|string|max:255',
            'company_identifier' => 'required|string|max:255|unique:companies,identifier',
        ]);

        // Cria empresa
        $company = Company::create([
            'name' => $validated['company_name'],
            'identifier' => $validated['company_identifier'],
        ]);

        // Cria usuário
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'company_id' => $company->id,
        ]);

        $token = JWTAuth::fromUser($user);

        // Inclui company_name na resposta
        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'company_name' => $company->name,
            ],
            'token' => $token,
            'message' => 'User registered successfully'
        ], 201);
    }

    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        try {
            if (!$token = JWTAuth::attempt($validated)) {
                return response()->json(['error' => 'Invalid credentials'], 401);
            }
        } catch (JWTException $e) {
            return response()->json(['error' => 'Could not create token'], 500);
        }

        $user = JWTAuth::setToken($token)->toUser();
        $company = $user->company; // pega a empresa

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'company_name' => $company ? $company->name : null,
            ],
            'token' => $token,
            'message' => 'Login successful'
        ]);
    }

    /**
     * Endpoint para pegar dados do usuário autenticado
     */
    public function me(Request $request)
    {
        $user = auth()->user();
        $company = $user->company;

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'company_name' => $company ? $company->name : null,
        ]);
    }
}