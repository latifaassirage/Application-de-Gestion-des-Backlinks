<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SourceSummary extends Model
{
    protected $fillable = [
        'website',
        'cost',
        'link_type',
        'contact_email',
        'spam'
    ];

    /**
     * Relation avec le site source correspondant
     * Garder la relation existante mais améliorer la logique de recherche
     */
    public function sourceSite()
    {
        return $this->belongsTo(SourceSite::class, 'website', 'domain');
    }

    /**
     * Accesseur dynamique pour l'email du contact
     * Récupère depuis le client du backlink le plus récent pour ce domaine
     */
    public function getContactEmailAttribute($value)
    {
        // Si contact_email est défini dans summary, l'utiliser
        if ($value) {
            return $value;
        }

        // Sinon, récupérer depuis le client du backlink le plus récent pour ce domaine
        $latestBacklink = \App\Models\Backlink::with(['client', 'sourceSite'])
            ->whereHas('sourceSite', function($query) {
                $query->where('domain', $this->website);
            })
            ->orderBy('updated_at', 'desc')
            ->first();

        return $latestBacklink?->client?->contact_email ?: null;
    }

    /**
     * Accesseur dynamique pour le spam score
     * Récupère toujours depuis la table source_sites pour être à jour
     */
    public function getSpamAttribute($value)
    {
        // Toujours récupérer depuis source_site pour avoir la valeur la plus récente
        return $this->sourceSite?->spam_score ?? $value ?? 0;
    }

    /**
     * Accesseur dynamique pour le link_type
     * Récupère toujours depuis le backlink le plus récent pour être à jour
     */
    public function getLinkTypeAttribute($value)
    {
        // Si link_type est défini dans summary et n'est pas null, l'utiliser
        if ($value !== null) {
            return $value;
        }

        // Sinon, récupérer depuis le backlink le plus récent pour avoir la valeur la plus récente
        $latestBacklink = \App\Models\Backlink::with('sourceSite')
            ->whereHas('sourceSite', function($query) {
                $query->where('domain', $this->website);
            })
            ->orderBy('updated_at', 'desc')
            ->first();

        return $latestBacklink?->link_type;
    }

    /**
     * Accesseur dynamique pour le cost
     * Calcule le coût total depuis les backlinks si non défini
     */
    public function getCostAttribute($value)
    {
        // Si cost est défini dans summary, l'utiliser
        if ($value !== null) {
            return $value;
        }

        // Sinon, calculer depuis les backlinks (sans jointure)
        return \App\Models\Backlink::with('sourceSite')
            ->whereHas('sourceSite', function($query) {
                $query->where('domain', $this->website);
            })
            ->sum('cost');
    }
}
