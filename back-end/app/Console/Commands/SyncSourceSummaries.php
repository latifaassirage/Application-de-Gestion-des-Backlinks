<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SourceSite;
use App\Models\SourceSummary;

class SyncSourceSummaries extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:source-summaries';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronize source_summaries table with source_sites domains';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting synchronization of source_summaries...');
        
        $updatedCount = 0;
        $createdCount = 0;
        
        // Get all source sites
        $sourceSites = SourceSite::all();
        
        foreach ($sourceSites as $sourceSite) {
            // Find existing summary by old domain or create new one
            $summary = SourceSummary::where('website', $sourceSite->domain)->first();
            
            if ($summary) {
                // Update existing summary to ensure it matches
                $summary->update([
                    'website' => $sourceSite->domain,
                    'updated_at' => now()
                ]);
                $updatedCount++;
                $this->line("Updated summary for domain: {$sourceSite->domain}");
            } else {
                // Create new summary if doesn't exist
                SourceSummary::create([
                    'website' => $sourceSite->domain,
                    'cost' => 0,
                    'link_type' => 'DoFollow',
                    'contact_email' => null,
                    'spam' => $sourceSite->spam_score ?? 0,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                $createdCount++;
                $this->line("Created summary for domain: {$sourceSite->domain}");
            }
        }
        
        // Clean up orphaned summaries (summaries that don't have corresponding source sites)
        $orphanedCount = SourceSummary::whereNotIn('website', SourceSite::pluck('domain'))->delete();
        
        $this->info('Synchronization completed!');
        $this->info("Updated: {$updatedCount} summaries");
        $this->info("Created: {$createdCount} summaries");
        $this->info("Cleaned up: {$orphanedCount} orphaned summaries");
        
        return 0;
    }
}
