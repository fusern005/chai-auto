<?php

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

// Run the migration
\Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
echo "Migrated successfully.<br>";

// Update existing users to have employee_id
$users = \App\Models\User::all();
foreach ($users as $user) {
    if (empty($user->employee_id)) {
        // Use part of email as employee_id, or just 'admin' for admin@test.com
        if ($user->email === 'admin@test.com') {
            $user->employee_id = 'admin';
        } else {
            $user->employee_id = explode('@', $user->email)[0];
        }
        $user->save();
        echo "Updated employee_id for {$user->email} to {$user->employee_id}<br>";
    }
}

echo "<br>All done! <a href='/Project-Internal-Audit/public/login'>Go to Login</a>";
