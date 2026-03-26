<?php
// Script de vérification et synchronisation des données
require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\SourceSite;
use App\Models\SourceSummary;

echo '=== Vérification des données ===' . PHP_EOL;

// Vérifier les données actuelles
echo 'Source Sites:' . PHP_EOL;
SourceSite::take(3)->get(['id', 'domain'])->each(function($s) {
    echo 'ID: ' . $s->id . ' - Domain: ' . $s->domain . PHP_EOL;
});

echo PHP_EOL . 'Source Summaries:' . PHP_EOL;
SourceSummary::take(3)->get(['id', 'website'])->each(function($s) {
    echo 'ID: ' . $s->id . ' - Website: ' . $s->website . PHP_EOL;
});

echo PHP_EOL . '=== Test de jointure ===' . PHP_EOL;

// Test de la jointure comme dans le contrôleur
$summaryData = SourceSummary::leftJoin('source_sites', 'source_summaries.website', '=', 'source_sites.domain')
    ->select(
        'source_summaries.*',
        'source_sites.domain as actual_domain'
    )
    ->take(3)
    ->get();

$summaryData->each(function($s) {
    echo 'Summary ID: ' . $s->id . ' - Website: ' . $s->website . ' - Actual Domain: ' . ($s->actual_domain ?? 'NULL') . PHP_EOL;
});

echo PHP_EOL . '=== Synchronisation ===' . PHP_EOL;

// Synchroniser les données
$updatedCount = 0;
$createdCount = 0;

foreach (SourceSite::all() as $sourceSite) {
    $summary = SourceSummary::where('website', $sourceSite->domain)->first();
    
    if ($summary) {
        $summary->update(['updated_at' => now()]);
        $updatedCount++;
        echo '✓ Updated: ' . $sourceSite->domain . PHP_EOL;
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
        echo '✓ Created: ' . $sourceSite->domain . PHP_EOL;
    }
}

echo PHP_EOL . 'Terminé! Updated: ' . $updatedCount . ', Created: ' . $createdCount . PHP_EOL;
