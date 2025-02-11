<?php

namespace App\Http\Controllers;

use App\Models\Motorcyclist;
use App\Models\Store;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function register(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
            'role' => 'required|in:store,motorcyclist',
            'cpf' => 'nullable|required_if:role,motorcyclist|string|size:11|unique:motorcyclists,cpf',
            'cnpj' => 'nullable|required_if:role,store|string|size:14|unique:stores,cnpj'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role
        ]);

        if ($request->role === 'store') {
            Store::create([
                'user_id' => $user->id,
                'cnpj' => $request->cnpj,
                'fantasy_name' => $request->name,
                'phone' => $request->phone ?? null,
                'logo' => $request->logo ?? null
            ]);
        }

        if ($request->role === 'motorcyclist') {
            Motorcyclist::create([
                'user_id' => $user->id,
                'cpf' => $request->cpf,
                'placa_moto' => $request->placa_moto ?? null,
                'cnh' => $request->cnh ?? null,
                'gender' => $request->gender ?? null
            ]);
        }

        return response()->json([
            'message' => 'User registered successfully.',
            'user' => $user,
        ], 201);    }

   public function login(Request $request)
   {
       // Validação das credenciais
       $request->validate([
           'email' => 'required|email',
           'password' => 'required|min:6',
       ]);

       $credentials = $request->only('email', 'password');

       try {
           if (!$token = JWTAuth::attempt($credentials)) {
               return response()->json(['error' => 'Credenciais inválidas'], 401);
           }
       } catch (JWTException $e) {
           return response()->json(['error' => 'Não foi possível gerar o token'], 500);
       }

       return response()->json([
           'user' => JWTAuth::user(),
           'token' => $token,
       ]);
   }

   public function logout()
    {
        try {
            $token = JWTAuth::getToken();

            JWTAuth::invalidate($token);

            return response()->json(['message' => 'Logout realizado com sucesso']);
        } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
            return response()->json(['error' => 'Token inválido'], 401);
        } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
            return response()->json(['error' => 'Token não encontrado'], 400);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Ocorreu um erro ao processar o logout'], 500);
        }
    }

   public function me()
   {
       try {
           $user = JWTAuth::user();
           return response()->json($user);
       } catch (JWTException $e) {
           return response()->json(['error' => 'Não foi possível recuperar o usuário'], 500);
       }
   }
}
