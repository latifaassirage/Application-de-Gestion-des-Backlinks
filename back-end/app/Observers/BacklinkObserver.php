<?php

namespace App\Observers;

use App\Models\Backlink;
use App\Models\SourceSummary;
use App\Models\SourceSite;
use App\Models\Client;

class BacklinkObserver
{
    /**
     * Handle the Backlink "created" event.
     *
     * @param  \App\Models\Backlink  $backlink
     * @return void
     */
    public function created(Backlink $backlink)
    {
        $this->syncToSourceSummary($backlink);
    }

    /**
     * Handle the Backlink "updated" event.
     *
     * @param  \App\Models\Backlink  $backlink
     * @return void
     */
    public function updated(Backlink $backlink)
    {
        $this->syncToSourceSummary($backlink);
    }

    /**
     * Synchronize backlink data to source_summaries table
     *
     * @param  \App\Models\Backlink  $backlink
     * @return void
     */
    private function syncToSourceSummary(Backlink $backlink)
    {
        try {
            // Récupérer le domaine depuis la table source_sites
            $website = null;
            if ($backlink->sourceSite) {
                $website = $backlink->sourceSite->domain;
            }

            // Si pas de website, on ne synchronise pas
            if (empty($website)) {
                return;
            }

            // Récupérer l'email depuis la table clients
            $contactEmail = null;
            if ($backlink->client) {
                $contactEmail = $backlink->client->contact_email;
            }

            // Utiliser updateOrCreate pour synchroniser
            SourceSummary::updateOrCreate(
                ['website' => $website], // Clé unique
                [
                    'cost' => $backlink->cost,
                    'link_type' => $backlink->link_type,
                    'contact_email' => $contactEmail,
                    'spam' => $backlink->sourceSite ? $backlink->sourceSite->spam_score : null,
                    'updated_at' => now()
                ]
            );

            \Log::info('Backlink synchronized to source_summaries', [
                'backlink_id' => $backlink->id,
                'website' => $website,
                'action' => 'sync'
            ]);

        } catch (\Exception $e) {
            \Log::error('Error synchronizing backlink to source_summaries', [
                'backlink_id' => $backlink->id,
                'error' => $e->getMessage()
            ]);
        }
    }
}
