<?php

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

$users = \App\Models\User::all();
echo "Total Users: " . $users->count() . "\n\n";

foreach ($users as $user) {
    echo "ID: " . $user->id . "\n";
    echo "Email: " . $user->email . "\n";
    echo "Employee ID: " . $user->employee_id . "\n";
    echo "Password: " . substr($user->password, 0, 10) . "...\n";
    echo "Role: " . $user->role . "\n";
    echo "--------------------------\n";
}
