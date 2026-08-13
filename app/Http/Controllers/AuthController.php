<?php

namespace App\Http\Controllers;

use App\Services\GoogleSheetsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class AuthController extends Controller
{
    protected GoogleSheetsService $sheets;

    public function __construct(GoogleSheetsService $sheets)
    {
        $this->sheets = $sheets;
    }

    public function showLoginForm()
    {
        if (Session::get('user_id')) {
            return redirect()->route('dashboard');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required',
            'password' => 'required',
        ]);

        $users = $this->sheets->readAll('users');
        $user  = null;

        foreach ($users as $u) {
            if ($u['username'] === $request->username && $u['is_active'] !== '0') {
                if (password_verify($request->password, $u['password_hash'])) {
                    $user = $u;
                    break;
                }
            }
        }

        if (!$user) {
            return back()->withErrors(['login' => 'ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง'])->withInput();
        }

        Session::put('user_id', $user['id']);
        Session::put('user_name', $user['name']);
        Session::put('user_role', $user['role']);

        return redirect()->route('dashboard');
    }

    public function logout()
    {
        Session::flush();
        return redirect()->route('login');
    }
}
