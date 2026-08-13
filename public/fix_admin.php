<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

$user = \App\Models\User::where('email', 'admin@test.com')->first();
if ($user) {
    $user->employee_id = 'admin';
    // reset password just in case
    $user->password = \Illuminate\Support\Facades\Hash::make('password');
    $user->save();
    echo "Admin account fixed! Employee ID is set to 'admin' and Password is set to 'password'.";
} else {
    echo "Admin account not found.";
}
echo "<br><br><a href='/Project-Internal-Audit/public/login'>Go to Login</a>";
