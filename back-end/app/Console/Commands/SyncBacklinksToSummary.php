<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Backlink;
use App\Models\SourceSummary;

class SyncBacklinksToSummary extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:sync-backlinks-to-summary';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronize all existing backlinks to source_summaries table';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting synchronization of backlinks to source_summaries...');
        
        $processed = 0;
        $created = 0;
        $updated = 0;
        $errors = 0;

        // Récupérer tous les backlinks avec leurs relations
        $backlinks = Backlink::with(['client', 'sourceSite'])->get();
        
        $this->info("Found {$backlinks->count()} backlinks to process");
        
        // Progress bar
        $progressBar = $this->output->createProgressBar($backlinks->count());
        $progressBar->start();

        foreach ($backlinks as $backlink) {
            try {
                // Récupérer le domaine depuis la table source_sites
                $website = null;
                if ($backlink->sourceSite) {
                    $website = $backlink->sourceSite->domain;
                }

                // Si pas de website, on passe
                if (empty($website)) {
                    $this->line("\nSkipping backlink ID {$backlink->id}: No website found");
                    $processed++;
                    $progressBar->advance();
                    continue;
                }

                // Récupérer l'email depuis la table clients
                $contactEmail = null;
                if ($backlink->client) {
                    $contactEmail = $backlink->client->contact_email;
                }

                // Utiliser updateOrCreate pour synchroniser
                $summary = SourceSummary::updateOrCreate(
                    ['website' => $website], // Clé unique
                    [
                        'cost' => $backlink->cost,
                        'link_type' => $backlink->link_type,
                        'contact_email' => $contactEmail,
                        'spam' => $backlink->sourceSite ? $backlink->sourceSite->spam_score : null,
                        'updated_at' => now()
                    ]
                );

                if ($summary->wasRecentlyCreated) {
                    $created++;
                } else {
                    $updated++;
                }

                $processed++;

            } catch (\Exception $e) {
                $this->line("\nError processing backlink ID {$backlink->id}: " . $e->getMessage());
                $errors++;
            }
            
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->line("\n");

        // Résumé
        $this->info('Synchronization completed!');
        $this->line("Total processed: {$processed}");
        $this->line("Created: {$created}");
        $this->line("Updated: {$updated}");
        $this->line("Errors: {$errors}");

        return 0;
    }
}
