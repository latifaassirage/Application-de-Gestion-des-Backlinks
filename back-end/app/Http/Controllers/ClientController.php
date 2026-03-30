<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\SourceSummary;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    /**
     * Synchroniser les summaries pour tous les domaines où ce client a des backlinks
     */
    private function syncClientSummaries($clientId)
    {
        try {
            // Récupérer le client mis à jour pour avoir le nouvel email
            $client = Client::find($clientId);
            if (!$client) {
                return;
            }
            
            // Récupérer tous les domaines où ce client a des backlinks
            $domains = \App\Models\Backlink::with('sourceSite')
                ->where('client_id', $clientId)
                ->whereHas('sourceSite')
                ->get()
                ->pluck('sourceSite.domain')
                ->unique();

            // Pour chaque domaine, mettre à jour le contact_email dans source_summaries
            foreach ($domains as $domain) {
                \Log::info("Updating contact_email for domain: {$domain} to client email: {$client->contact_email}");
                
                // Mettre à jour directement le champ contact_email dans source_summaries
                \App\Models\SourceSummary::where('website', $domain)
                    ->update(['contact_email' => $client->contact_email]);
                
                // Synchroniser aussi les autres données du summary
                $backlinkController = new \App\Http\Controllers\BacklinkController();
                $backlinkController->syncSourceSummary($domain);
            }

        } catch (\Exception $e) {
            \Log::error('Error syncing client summaries for client ' . $clientId . ': ' . $e->getMessage());
        }
    }

    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 10); // 10 par défaut
        $page = $request->get('page', 1); // Page 1 par défaut
        $search = $request->get('search', ''); // Terme de recherche
        
        $query = Client::orderBy('created_at', 'desc');
        
        // Ajouter la recherche si un terme est fourni
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('company_name', 'LIKE', '%' . $search . '%')
                  ->orWhere('contact_email', 'LIKE', '%' . $search . '%')
                  ->orWhere('website', 'LIKE', '%' . $search . '%');
            });
        }
        
        $clients = $query->paginate($perPage, ['*'], 'page', $page);

        return $clients;
    }

    public function all()
    {
        return Client::orderBy('created_at', 'desc')->get();
    }

    public function unique()
    {
        // Retourne les clients uniques par email pour éviter les doublons
        return Client::select('id', 'company_name', 'contact_email', 'website', 'city', 'state', 'notes', 'created_at')
            ->distinct('contact_email')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'company_name'=>'required|string|max:150',
            'contact_email'=>'nullable|email|max:150',
            'website'=>'required|string|max:150',
            'city'=>'nullable|string|max:100',
            'state'=>'nullable|string|max:100',
            'notes'=>'nullable|string'
        ]);

        // Vérifier si l'email existe déjà (blocage définitif)
        if (!empty($data['contact_email'])) {
            $existingClient = Client::where('contact_email', $data['contact_email'])->first();
            if ($existingClient) {
                return response()->json([
                    'message' => 'A client with this email already exists.',
                    'errors' => ['contact_email' => ['This email is already registered.']]
                ], 422);
            }
        }

        $client = Client::create($data);
        
        // Synchroniser les summaries pour mettre à jour l'email du contact
        // (utile si le client est créé avec des backlinks existants)
        $this->syncClientSummaries($client->id);
        
        return response()->json($client, 201);
    }

    public function show($id)
    {
        return Client::with('backlinks')->findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $client = Client::findOrFail($id);
        $data = $request->validate([
            'company_name'=>'sometimes|string|max:150',
            'contact_email'=>'sometimes|nullable|email|max:150',
            'website'=>'sometimes|string|max:150',
            'city'=>'nullable|string|max:100',
            'state'=>'nullable|string|max:100',
            'notes'=>'nullable|string'
        ]);

        // Vérifier si l'email existe déjà (blocage définitif)
        if (isset($data['contact_email']) && !empty($data['contact_email'])) {
            $existingClient = Client::where('contact_email', $data['contact_email'])
                ->where('id', '!=', $id)
                ->first();
            if ($existingClient) {
                return response()->json([
                    'message' => 'A client with this email already exists.',
                    'errors' => ['contact_email' => ['This email is already registered.']]
                ], 422);
            }
        }

        $client->update($data);
        
        // Synchroniser les summaries pour mettre à jour l'email du contact dans tous les domaines concernés
        \Log::info("Client {$id} updated, syncing summaries for email/contact changes");
        $this->syncClientSummaries($id);
        \Log::info("Client summaries sync completed");
        
        return response()->json($client);
    }

    public function destroy($id)
    {
        Client::findOrFail($id)->delete();
        return response()->json(['message'=>'Deleted']);
    }
}
