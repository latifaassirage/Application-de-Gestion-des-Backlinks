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
        
        $sources = SourceSite::orderBy('created_at', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);

        return $sources;
    }

    public function all()
    {
        return SourceSite::orderBy('domain', 'asc')->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'domain'=>'required|string|unique:source_sites,domain',
            'quality_score'=>'required|integer|min:1|max:5',
            'dr'=>'nullable|integer',
            'traffic_estimated'=>'nullable|integer',
            'spam_score'=>'required|integer|min:0|max:100',
            'notes'=>'nullable|string',
        ]);

        $source = SourceSite::create($data);
        return response()->json($source, 201);
    }

    public function show($id)
    {
        return SourceSite::with('backlinks')->findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $source = SourceSite::findOrFail($id);
        $data = $request->validate([
            'domain'=>'sometimes|required|string|unique:source_sites,domain,'.$id,
            'quality_score'=>'sometimes|required|integer|min:1|max:5',
            'dr'=>'nullable|integer',
            'traffic_estimated'=>'nullable|integer',
            'spam_score'=>'sometimes|required|integer|min:0|max:100',
            'notes'=>'nullable|string',
        ]);
        $source->update($data);
        return response()->json($source);
    }

    public function destroy($id)
    {
        SourceSite::findOrFail($id)->delete();
        return response()->json(['message'=>'Deleted']);
    }
}
