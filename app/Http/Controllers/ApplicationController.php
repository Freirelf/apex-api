<?php

namespace App\Http\Controllers;

use App\Models\Application;
use Illuminate\Http\Request;

class ApplicationController extends Controller
{
    public function apply(Request $request)
    {   
        $user = auth('api')->user();
    
        if ($user->role !== 'motorcyclist') {
            return response()->json(['error' => 'Acesso negado. Apenas motoboys podem se candidatar.'], 403);
        }

        $request->validate([
            'delivery_job_id' => 'required|exists:delivery_jobs,id',
        ]);
    
        $motorcyclistId = auth('api')->user()->motorcyclist->id; 
    
        $existingApplication = Application::where('motorcyclist_id', $motorcyclistId)
            ->where('delivery_job_id', $request->delivery_job_id)
            ->exists();
    
        if ($existingApplication) {
            return response()->json(['error' => 'Você já aplicou para esta vaga'], 400);
        }
    
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

        if (!$motorcyclistId) {
            return response()->json(['error' => 'Acesso negado. Apenas motoboys podem acessar essa rota.'], 403);
        }

        $applications = Application::with('deliveryJob')->where('motorcyclist_id', $motorcyclistId)->get();

        return response()->json($applications);
    }

    public function removeApplication($id)
    {
        $motorcyclistId = auth('api')->user()->motorcyclist->id;

        if (!$motorcyclistId) {
            return response()->json(['error' => 'Acesso negado. Apenas motoboys podem acessar essa rota.'], 403);
        }

        $application = Application::where('motorcyclist_id', $motorcyclistId)->where('id', $id)->first();

        if (!$application) {
            return response()->json(['error' => 'Candidatura não encontrada'], 404);
        }

        $application->delete();

        return response()->json(['message' => 'Candidatura remove com sucesso']);
    }

    public function listCandidates($deliveryJobId)
    {
        $user = auth('api')->user();

        if ($user->role !== 'store') {
            return response()->json(['error' => 'Acesso negado. Apenas lojas podem acessar essa rota.'], 403);
        }

        $candidates = Application::with('delivery_job_id', $deliveryJobId)
            ->with('motorcyclist')
            ->get();

        return response()->json($candidates);
    }

    public function updateStatus(Request $request, $id)
    {
        $user = auth('api')->user();

        if ($user->role !== 'store') {
            return response()->json(['error' => 'Acesso negado. Apenas lojas podem acessar essa rota.'], 403);
        }

        $request->validate([
            'status' => 'required|in:accepted,rejected',
        ]);

        $application = Application::find($id);

        if (!$application) {
            return response()->json(['error' => 'Candidatura não encontrada'], 404);
        }

        $application->status = $request->status;
        $application->save();

        return response()->json(['message' => 'Status atualizado com sucesso']);
    }
}
