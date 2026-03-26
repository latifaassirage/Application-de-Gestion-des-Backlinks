<?php
// Synchronisation script
require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\SourceSite;
use App\Models\SourceSummary;

echo 'Starting synchronization...' . PHP_EOL;

$sourceSites = SourceSite::all();
$updatedCount = 0;
$createdCount = 0;

foreach ($sourceSites as $sourceSite) {
    $summary = SourceSummary::where('website', $sourceSite->domain)->first();
    
    if ($summary) {
        $summary->update(['updated_at' => now()]);
        $updatedCount++;
        echo 'Updated: ' . $sourceSite->domain . PHP_EOL;
    } else {
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
        echo 'Created: ' . $sourceSite->domain . PHP_EOL;
    }
}

$orphanedCount = SourceSummary::whereNotIn('website', SourceSite::pluck('domain'))->delete();

echo 'Completed! Updated: ' . $updatedCount . ', Created: ' . $createdCount . ', Cleaned: ' . $orphanedCount . PHP_EOL;
