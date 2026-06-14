<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class AdminAuthController extends Controller
{
    public function showLogin()
    {
        if (session('is_admin')) {
            return redirect('/sub/admin/dashboard');
        }
        return view('admin.login');
    }

    public function login(Request $request)
    {
        $credentials = $this->getMarzbanCredentials();

        if ($request->username === $credentials['username'] && $request->password === $credentials['password']) {
            session(['is_admin' => true]);
            return redirect('/sub/admin/dashboard');
        }

        return back()->withErrors(['login' => 'Invalid credentials from Marzban .env']);
    }

    public function logout()
    {
        session()->forget('is_admin');
        return redirect('/sub/admin/login');
    }

    private function getMarzbanCredentials()
    {
        $path = '/opt/Marzban/.env';
        $username = null;
        $password = null;

        if (File::exists($path)) {
            $content = File::get($path);
            $lines = explode("\n", $content);
            foreach ($lines as $line) {
                $line = trim($line);
                if (preg_match('/^SUDO_USERNAME\s*=\s*(.*)$/', $line, $matches)) {
                    $username = trim($matches[1], '"\' ');
                }
                if (preg_match('/^SUDO_PASSWORD\s*=\s*(.*)$/', $line, $matches)) {
                    $password = trim($matches[1], '"\' ');
                }
            }
        }

        return [
            'username' => $username,
            'password' => $password
        ];
    }
}
