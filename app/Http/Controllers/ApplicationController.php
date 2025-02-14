<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\DeliveryJob;
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

        $job = DeliveryJob::findOrFail($request->delivery_job_id);
        if ($job->applications()->count() === 1) {
            $job->update(['status' => 'in_progress']);
        }
    
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

        if ($application->status === 'accepted') {
            return response()->json(['error' => 'Você não pode desistir após ser aceito. Entre em contato com a loja.'], 403);
        }

        $application->delete();

        return response()->json(['message' => 'Candidatura remove com sucesso']);
    }

    public function cancelApplication($applicationId)
    {
        $user = auth('api')->user();
        $application = Application::where('id', $applicationId)->whereHas('deliveryJob', function ($query) use ($user) {
            $query->where('store_id', $user->store->id);
        })->first();

        if (!$application) {
            return response()->json(['error' => 'Candidatura não encontrada'], 404);
        }

        $application->update(['status' => 'rejected']);

        $deliveryJob = DeliveryJob::find($application->delivery_job_id);
        if ($deliveryJob->status === 'closed') {
            $deliveryJob->update(['status' => 'open']);
        }

        return response()->json(['message' => 'Candidatura cancelada e vaga reaberta com sucesso']);
    }

    public function listCandidates($deliveryJobId)
    {
        $user = auth('api')->user();

        $deliveryJob = DeliveryJob::find($deliveryJobId);
        if (!$deliveryJob) {
            return response()->json(['error' => 'Vaga não encontrada'], 404);
        }

        if ($user->role !== 'store') {
            return response()->json(['error' => 'Acesso negado. Apenas lojas podem acessar essa rota.'], 403);
        }

        $candidates = Application::where('delivery_job_id', $deliveryJobId) 
        ->with('deliveryJob') 
        ->with('motorcyclist') 
        ->get();

        return response()->json([
            'message' => 'Candidatos listados com sucesso',
            'candidates' => $candidates
        ]);
    }

    public function updateStatus(Request $request, $id)
    {   
        
        $user = auth('api')->user();

        if ($user->role !== 'store') {
            return response()->json(['error' => 'Acesso negado. Apenas lojas podem acessar essa rota.'], 403);
        }

        $application = Application::find($id);
        $job = DeliveryJob::findOrFail($application->delivery_job_id);

        if ($job->store_id !== $user->store->id) {
            return response()->json(['error' => 'Você não pode modificar essa vaga'], 403);
        }

        if (!$application || $application->deliveryJob->store_id !== $user->store->id) {
            return response()->json(['error' => 'Candidatura não encontrada'], 404);
        }

        $request->validate([
            'status' => 'required|in:accepted,rejected',
        ]);

        $application->update(['status' => $request->status]);

        if ($request->status === 'accepted') {
            // Fechar a vaga para novas candidaturas
            $job->update(['status' => 'closed']);
    
            // Rejeitar os outros candidatos
            Application::where('delivery_job_id', $job->id)
                ->where('id', '!=', $application->id)
                ->update(['status' => 'rejected']);
        }

        return response()->json([
            'message' => 'Status atualizado com sucesso',
            'application' => $application
        ], 200);
    }
}
