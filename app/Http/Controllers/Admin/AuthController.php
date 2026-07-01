<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\DbService;
use App\Services\PermissionService;
use App\Helpers\HashHelper;
use Illuminate\Http\Request;


class AuthController extends Controller
{
    public function __construct(
        private DbService $db,
        private PermissionService $permissions
    ) {}

    /**
     * Show the login form.
     */
    public function showLogin()
    {
        if (session()->has('admin_user')) {
            return redirect()->route('admin.dashboard');
        }
        return view('admin.auth.login');
    }

    /**
     * Process the login request.
     */
    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        $email    = strtolower(trim($request->email));
        $password = $request->password;

        $user = null;

        // Find admin user by email in 'users' table
        $users = $this->db->getAll('users');
        foreach ($users as $u) {
            if (isset($u['email']) && strtolower(trim($u['email'])) === $email) {
                if (HashHelper::check($password, $u['password'] ?? '')) {
                    $user = $u;
                }
                break;
            }
        }

        if (!$user) {
            return back()->withErrors(['email' => 'Invalid email or security password.'])->withInput();
        }

        if (!empty($user['isBlocked']) || (isset($user['status']) && strtolower($user['status']) === 'suspended')) {
            return back()->withErrors(['email' => 'Your administrator account has been suspended.'])->withInput();
        }

        // Fetch user permissions
        $user['permissions'] = $this->permissions->getUserPermissions($user['id']);

        // Log audit event
        $this->permissions->logAuditEvent($user['id'], 'Admin logged in', 'Authentication');

        // Store user in session
        session(['admin_user' => $user]);

        return redirect()->route('admin.dashboard');
    }

    /**
     * Log out the administrator.
     */
    public function logout()
    {
        if (session()->has('admin_user.id')) {
            $this->permissions->logAuditEvent(session('admin_user.id'), 'Admin logged out', 'Authentication');
        }
        
        session()->forget('admin_user');
        
        return redirect()->route('admin.login')->with('success', 'Logged out successfully.');
    }
}
