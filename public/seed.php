<?php

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

\Illuminate\Support\Facades\Artisan::call('db:seed', ['--class' => 'ExampleQuestionSeeder']);

echo "Seeded 10 example questions successfully. <br><br>";
echo "<a href='/Project-Internal-Audit/public/admin/questions'>Go back to Questions List</a>";
