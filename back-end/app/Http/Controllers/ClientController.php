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
            // Récupérer tous les domaines où ce client a des backlinks (sans jointure)
            $domains = \App\Models\Backlink::with('sourceSite')
                ->where('client_id', $clientId)
                ->whereHas('sourceSite')
                ->get()
                ->pluck('sourceSite.domain')
                ->unique();

            // Pour chaque domaine, forcer la mise à jour du summary
            foreach ($domains as $domain) {
                // Supprimer l'entrée summary pour forcer la recréation avec les nouvelles données
                SourceSummary::where('website', $domain)->delete();
                
                // Recréer le summary avec les données à jour (sans jointure)
                $backlinks = \App\Models\Backlink::with('sourceSite')
                    ->whereHas('sourceSite', function($query) use ($domain) {
                        $query->where('domain', $domain);
                    })
                    ->get();

                $totalBacklinks = $backlinks->count();
                $liveBacklinks = $backlinks->where('status', 'Live')->count();
                $pendingBacklinks = $backlinks->where('status', 'Pending')->count();
                $lostBacklinks = $backlinks->where('status', 'Lost')->count();
                $totalCost = $backlinks->sum('cost');
                $dofollowBacklinks = $backlinks->where('link_type', 'DoFollow')->count();
                $nofollowBacklinks = $backlinks->where('link_type', 'NoFollow')->count();

                if ($totalBacklinks > 0) {
                    SourceSummary::updateOrCreate(
                        ['website' => $domain],
                        [
                            'total_backlinks' => $totalBacklinks,
                            'live_backlinks' => $liveBacklinks,
                            'pending_backlinks' => $pendingBacklinks,
                            'lost_backlinks' => $lostBacklinks,
                            'total_cost' => $totalCost,
                            'dofollow_backlinks' => $dofollowBacklinks,
                            'nofollow_backlinks' => $nofollowBacklinks,
                        ]
                    );
                }
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
            'company_name'=>'sometimes|required|string|max:150',
            'contact_email'=>'sometimes|nullable|email|max:150',
            'website'=>'sometimes|required|string|max:150',
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
        
        // Synchroniser les summaries pour mettre à jour l'email du contact
        $this->syncClientSummaries($id);
        
        return response()->json($client);
    }

    public function destroy($id)
    {
        Client::findOrFail($id)->delete();
        return response()->json(['message'=>'Deleted']);
    }
}
