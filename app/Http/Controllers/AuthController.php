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
            "email" => "required|email",
            "password" => "required",'min:6'
        ]);

        $user = User::query()->where('email', $data['email'])->first();

        if(!$user || !Hash::check($data['password'], $user->password)){
            return response(['message' => 'bad credentials'], 401);
        }

        $token = $user->createToken('authToken')->plainTextToken;

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
        return ['tenant_id' => $request->user()->tenant_id];

        return response()->json(['message' => 'Une erreur est survenue'], $response->getStatusCode());
    }

    public function tenantId(Request $request)
    {
        dd($request);
        //modifier ou insert le tenantId de l'user

//        return response()->json(['message' => 'Une erreur est survenue'], $response->getStatusCode());
    }

    public function getOffice(Request $request)
    {
        dd($request);
        //récuperer l'utilisateur->id via son bearer puis son tenantId
        $user = User::query()->where('email', $data['email'])->first();

        //

        return response()->json(['message' => 'Une erreur est survenue'], $response->getStatusCode());
    }

    public function updateOffice(Request $request)
    {
        dd($request);
        //modifier ou insert le tenantId de l'user

//        return response()->json(['message' => 'Une erreur est survenue'], $response->getStatusCode());
    }
}
