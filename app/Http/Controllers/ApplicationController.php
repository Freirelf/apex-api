<?php

namespace App\Http\Controllers;

use App\Models\Application;
use Illuminate\Http\Request;

class ApplicationController extends Controller
{
    public function apply(Request $request)
    {
        $request->validate([
            'delivery_job_id' => 'required|exists:delivery_jobs,id',
        ]);
    
        $motorcyclistId = auth('api')->user()->motorcyclist->id; // Recupera o ID do motoboy autenticado
    
        // Verifica se o motoboy já aplicou para a vaga
        $existingApplication = Application::where('motorcyclist_id', $motorcyclistId)
            ->where('delivery_job_id', $request->delivery_job_id)
            ->first();
    
        if ($existingApplication) {
            return response()->json(['error' => 'Você já aplicou para esta vaga'], 400);
        }
    
        // Cria a candidatura
        $application = Application::create([
            'motorcyclist_id' => $motorcyclistId,
            'delivery_job_id' => $request->delivery_job_id,
            'status' => 'pending',
        ]);
    
        return response()->json(['message' => 'Candidatura enviada com sucesso', 'application' => $application], 201);
    }

    public function myApplications()
    {
        $motorcyclistId = auth('api')->user()->motorcyclist->id;

        $applications = Application::with('deliveryJob')->where('motorcyclist_id', $motorcyclistId)->get();

        return response()->json($applications);
    }
}
