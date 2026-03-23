<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

echo 'Clients: ' . App\Models\Client::count() . ', Sources: ' . App\Models\SourceSite::count();
