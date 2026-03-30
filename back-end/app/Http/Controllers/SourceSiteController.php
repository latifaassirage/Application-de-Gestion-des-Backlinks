<?php

namespace App\Http\Controllers;

use App\Models\SourceSite;
use Illuminate\Http\Request;

class SourceSiteController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 10); // 10 par défaut
        $page = $request->get('page', 1); // Page 1 par défaut
        $search = $request->get('search', ''); // Terme de recherche
        
        // Construire la requête de base
        $query = SourceSite::query();
        
        // Appliquer le filtre de recherche SUR TOUTE LA TABLE en premier
        if ($search) {
            $query->where('domain', 'LIKE', '%' . $search . '%');
        }
        
        // Appliquer le tri APRÈS le filtrage
        $query->orderBy('created_at', 'desc');
        
        // Appliquer la pagination EN DERNIER
        $sources = $query->paginate($perPage, ['*'], 'page', $page);

        return $sources;
    }

    public function all()
    {
        return SourceSite::orderBy('created_at', 'desc')->get();
    }

    public function grouped()
    {
        // Regrouper les sources par domaine avec des statistiques agrégées
        $groupedSources = SourceSite::select([
                'domain',
                \DB::raw('MIN(id) as id'),
                \DB::raw('MIN(quality_score) as min_quality_score'),
                \DB::raw('MAX(quality_score) as max_quality_score'),
                \DB::raw('AVG(quality_score) as avg_quality_score'),
                \DB::raw('MIN(dr) as min_dr'),
                \DB::raw('MAX(dr) as max_dr'),
                \DB::raw('AVG(dr) as avg_dr'),
                \DB::raw('MIN(traffic_estimated) as min_traffic'),
                \DB::raw('MAX(traffic_estimated) as max_traffic'),
                \DB::raw('AVG(traffic_estimated) as avg_traffic'),
                \DB::raw('MIN(spam_score) as min_spam'),
                \DB::raw('MAX(spam_score) as max_spam'),
                \DB::raw('AVG(spam_score) as avg_spam'),
                \DB::raw('COUNT(*) as source_count'),
                \DB::raw('MIN(created_at) as first_created'),
                \DB::raw('MAX(created_at) as last_created')
            ])
            ->groupBy('domain')
            ->orderBy('last_created', 'desc')
            ->get();

        // Ajouter le nombre de backlinks pour chaque domaine
        foreach ($groupedSources as $source) {
            $backlinkCount = \DB::table('backlinks')
                ->join('source_sites', 'backlinks.source_site_id', '=', 'source_sites.id')
                ->where('source_sites.domain', $source->domain)
                ->count();
            
            $source->backlink_count = $backlinkCount;
        }

        return $groupedSources;
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'domain'=>'required|string|unique:source_sites,domain',
            'quality_score'=>'required|integer|min:1|max:5',
            'dr'=>'nullable|integer',
            'traffic_estimated'=>'nullable|integer',
            'spam_score'=>'required|integer|min:0|max:100',
            'contact_email'=>'nullable|email',
            'notes'=>'nullable|string',
        ]);

        // Vérification explicite anti-doublons avec message clair
        $existingSource = SourceSite::where('domain', $data['domain'])->first();
        if ($existingSource) {
            return response()->json([
                'message' => 'A source website with this domain already exists.',
                'errors' => ['domain' => ['This domain is already registered.']]
            ], 422);
        }

        $source = SourceSite::create($data);
        
        // Créer automatiquement un enregistrement dans source_summaries si n'existe pas
        \App\Models\SourceSummary::firstOrCreate(
            ['website' => $data['domain']],
            [
                'cost' => 0,
                'link_type' => 'DoFollow',
                'contact_email' => null,
                'spam' => $data['spam_score'],
                'created_at' => now(),
                'updated_at' => now()
            ]
        );
        
        return response()->json($source, 201);
    }

    public function show($id)
    {
        return SourceSite::with('backlinks')->findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $source = SourceSite::findOrFail($id);
        $oldDomain = $source->domain; // Garder l'ancien domaine
        
        $data = $request->validate([
            'domain'=>'sometimes|string|unique:source_sites,domain,'.$id,
            'quality_score'=>'sometimes|integer|min:1|max:5',
            'dr'=>'nullable|integer',
            'traffic_estimated'=>'nullable|integer',
            'spam_score'=>'sometimes|integer|min:0|max:100',
            'contact_email'=>'nullable|email',
            'notes'=>'nullable|string',
        ]);

        // Vérification explicite anti-doublons avec message clair
        if (isset($data['domain'])) {
            $existingSource = SourceSite::where('domain', $data['domain'])
                ->where('id', '!=', $id)
                ->first();
            if ($existingSource) {
                return response()->json([
                    'message' => 'A source website with this domain already exists.',
                    'errors' => ['domain' => ['This domain is already registered.']]
                ], 422);
            }
        }
        
        $source->update($data);
        
        // Toujours synchroniser le summary après modification pour garantir la cohérence
        $currentDomain = $data['domain'] ?? $oldDomain;
        \Log::info("Source site {$id} updated, syncing summary for domain: {$currentDomain}");
        
        $backlinkController = new \App\Http\Controllers\BacklinkController();
        $backlinkController->syncSourceSummary($currentDomain);
        
        // Si le domaine a changé, synchroniser aussi l'ancien domaine
        if (isset($data['domain']) && $data['domain'] !== $oldDomain) {
            \Log::info("Domain changed from '{$oldDomain}' to '{$data['domain']}', updating source_summaries");
            
            \App\Models\SourceSummary::where('website', $oldDomain)
                ->update(['website' => $data['domain']]);
                
            \Log::info("Updated source_summaries website field from '{$oldDomain}' to '{$data['domain']}'");
                
            // Synchroniser l'ancien domaine aussi au cas où il aurait encore des backlinks
            $backlinkController->syncSourceSummary($oldDomain);
            
            \Log::info("Synced summaries for both old and new domains");
        }
        
        return response()->json($source);
    }

    public function destroy($id)
    {
        $source = SourceSite::findOrFail($id);
        $domain = $source->domain; // Garder le domaine avant suppression
        
        $source->delete();
        
        // Supprimer aussi les enregistrements correspondants dans source_summaries
        $summary = \App\Models\SourceSummary::where('website', $domain)->first();
        if ($summary) {
            $summary->delete();
        }
        
        return response()->json(['message'=>'Deleted']);
    }
}
