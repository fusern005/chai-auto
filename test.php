<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$q = App\Models\Question::latest('updated_at')->first();
echo "Latest updated question ID: " . $q->id . "\n";
print_r($q->findings);
?>
