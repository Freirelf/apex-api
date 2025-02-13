<?php

use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DeliveryJobController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::get('/up', function () {
    return response()->json(['message' => 'API is running']);
});

// Routes for authentication
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware(['auth:api'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
});

// 📌 Routes for STORE (Create and manager)
Route::middleware(['auth:api', 'role:store'])->group(function () {
    Route::post('/delivery-jobs', [DeliveryJobController::class, 'store']);  
    Route::get('/my-delivery-jobs', [DeliveryJobController::class, 'listMyJobs']);    
    Route::put('/delivery-jobs/{id}', [DeliveryJobController::class, 'update']); 
    Route::delete('/delivery-jobs/{id}', [DeliveryJobController::class, 'delete']); 
});

// Routes for MOTOBOYS (candidacy)
Route::middleware(['auth:api', 'role:motorcyclist'])->group(function () {
    Route::post('/candidacy', [ApplicationController::class, 'apply']); 
    Route::get('/my-candidacy', [ApplicationController::class, 'myApplications']); 
    Route::delete('/candidacy/{id}', [ApplicationController::class, 'removeApplication']);
    Route::get('/delivery-jobs', [DeliveryJobController::class, 'listAllJobs']);
});

Route::middleware(['auth:api', 'role:store'])->group(function () {
    Route::get('/delivery-jobs/{id}/candidates', [ApplicationController::class, 'listCandidates']); 
    Route::put('/candidacy/{id}/status', [ApplicationController::class, 'updateStatus']); 
});

Route::middleware(['auth:api', 'role:store'])->group(function () {
    Route::get('/delivery-jobs/{id}/candidates', [ApplicationController::class, 'listCandidates']);
    Route::put('/candidacy/{id}/status', [ApplicationController::class, 'updateStatus']);
});

// Routes for testing
// Route::middleware(['auth:api', 'role:store'])->get('/test-role', function (Request $request) {
//     return response()->json(['message' => 'Middleware funcionando!', 'user' => auth('api')->user()]);
// });
// Route::middleware(['auth:api'])->group(function () {
//     Route::get('/test-auth', function () {
//         dd(auth('api')->user()); // Deve mostrar os dados do usuário autenticado
//     });
// });
// Route::get('/test-auth', function () {
//     return response()->json(['message' => 'Middleware auth funcionando!']);
// })->middleware('auth:api');