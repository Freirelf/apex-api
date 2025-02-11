<?php

namespace App\Http\Controllers;

use App\Models\DeliveryJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DeliveryJobController extends Controller
{
    public function store(Request $request)
    {   
        $user = auth('api')->user();
    
        // 🔍 Depuração: Verificar se o usuário foi autenticado corretamente
        if (!$user) {
            return response()->json(['error' => 'User not authenticated'], 401);
        }
    
        // 🔍 Verifica se o usuário tem uma loja associada
        $store = $user->store;
    
        if (!$store) {
            return response()->json(['error' => 'Store not found for this user'], 404);
        }
    
        // ✅ Agora sabemos que a loja existe, então podemos pegar o ID
        $storeId = $store->id;
    
        // 📌 Validação dos campos
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'distance' => 'required|numeric|min:1',
            'date' => 'required|date',
            'time' => 'required|date_format:H:i',
            'value' => 'required|numeric|min:0',
            'is_guaranteed' => 'required|boolean',
            'meal_included' => 'required|boolean',
            'provides_bag' => 'required|boolean',
            'pickup_address' => 'required|string|max:255',
        ]);
    
        // 📌 Verifica se o usuário autenticado é uma loja
        if ($user->role !== 'store') {
            return response()->json(['error' => 'Only stores can create delivery jobs'], 403);
        }
    
        // 🚀 Criar a vaga
        $deliveryJob = DeliveryJob::create([
            'store_id' => $storeId,
            'title' => $request->title,
            'description' => $request->description,
            'distance' => $request->distance,
            'date' => $request->date,
            'time' => $request->time,
            'value' => $request->value,
            'is_guaranteed' => $request->is_guaranteed,
            'meal_included' => $request->meal_included,
            'provides_bag' => $request->provides_bag,
            'pickup_address' => $request->pickup_address,
            'status' => 'open',
        ]);
    
        return response()->json(['message' => 'Vacancy created successfully', 'job' => $deliveryJob], 201);
    }
    
    public function list(Request $request)
    {   

        $query = DeliveryJob::query();

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
    
        $deliveryJob = $query->get();

        return response()->json([
            'message' => 'List of delivery jobs',
            'jobs' => $deliveryJob,
        ]);
    }

    public function update(Request $request, $id)
    {
        $deliveryJob = DeliveryJob::findOrFail($id);

        if (!$deliveryJob) {
            return response()->json(['error' => 'Vaga não encontrada'], 404);
        }

        if (auth('api')->user()->id !== $deliveryJob->store_id) {
            return response()->json(['error' => 'Você não tem permissão para editar esta vaga'], 403);
        }

         $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'distance' => 'sometimes|required|numeric|min:1',
            'date' => 'sometimes|required|date',
            'time' => 'sometimes|required|date_format:H:i',
            'value' => 'sometimes|required|numeric|min:0',
            'is_guaranteed' => 'sometimes|required|boolean',
            'meal_included' => 'sometimes|required|boolean',
            'provides_bag' => 'sometimes|required|boolean',
            'pickup_address' => 'sometimes|required|string|max:255',
            'status' => 'sometimes|required|in:open,in_progress,completed',
        ]);

        $deliveryJob->update($request->all());

        return response()->json(['message' => 'Job updated successfully', 'job' => $deliveryJob]);
    }

    public function delete($id)
    {   
        $user = auth('api')->user();

        if ($user->role !== 'store') {
            return response()->json(['error' => 'Apenas lojas podem excluir vagas'], 403);
        }

        $deliveryJob = DeliveryJob::findOrFail($id);

        if (!$deliveryJob) {
            return response()->json(['error' => 'Vaga não encontrada'], 404);
        }

        if (auth('api')->user()->id !== $deliveryJob->store_id) {
            return response()->json(['error' => ' você não tem permissão para excluir esta vaga'], 403);
        }

        $deliveryJob->delete();

        return response()->json(['message' => 'Job deleted successfully']);
    }
}
