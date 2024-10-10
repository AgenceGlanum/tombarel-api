<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string',
            'email' => 'required|string|email',
            'password' => 'required|string|confirmed'
        ]);

        $user = User::create($data);

        if(!$user || !Hash::check($data['password'], $user->password)){
            return response(['message' => 'bad credentials'], 401);
        }

        return ['user' => $user];
    }

    public function token(Request $request)
    {
        $data = $request->validate([
            "name" => "required|string",
            "password" => "required",'min:6'
        ]);

        $user = User::query()->where('name', $data['name'])->first();

        if(!$user || !Hash::check($data['password'], $user->password)){
            return response(['message' => 'bad credentials'], 401);
        }

        $token = $user->token;
//        $token = $user->createToken('authToken')->plainTextToken;

        return ['token' => $token, 'user' => $user];
    }

    public function healthcheck(): \Illuminate\Http\JsonResponse
    {
        $response = response()->json(['message' => 'Glanum ETL Workflow Trigger API is running']);

        // Vérifier le statut de la réponse
        if ($response->getStatusCode() === 200) {
            return $response;
        }

        return response()->json(['message' => 'Une erreur est survenue'], $response->getStatusCode());
    }

    public function getTenantId(Request $request)
    {
        $user = User::query()->where('token', $request->bearerToken())->first();
        return ['tenant_id' => $user->tenant_id ?? null];
    }

    public function tenantId(Request $request)
    {
        $data = $request->validate([
            "tenant_id" => "required","integer",
            "user_id" => "required",'integer'
        ]);
        //modifier ou insert le tenantId de l'user
        $user = User::query()->where('id', $data['user_id'])->first();
        $user->tenant_id = $data['tenant_id'];
        $user->save();

        return ['user' => $user, 'tenant_id' => $user->tenant_id ?? null];
    }

    public function getOffice(Request $request): array
    {
        $user = User::query()->where('token', $request->bearerToken())->first();

        return ['office' => $user->office ?? ''];
    }

    public function updateOffice(Request $request): array
    {
        $data = $request->validate([
            "office" => "required|string",
            "id" => "required",'integer'
        ]);

        //permettre le changement si l'utilisateur a un token ok.
        $userToPermit = User::query()->where('token', $request->bearerToken())->first();

        if(!$userToPermit){
            return response(['message' => 'non authentifié'], 401);
        }

        $user = User::query()->where('id', $data['id'])->first();
        $user->office = $data['office'];
        $user->save();

        return ['user' => $user,'office' => $user->office ?? null];
    }
}
