<?php
namespace App\Traits;

use Illuminate\Support\Facades\Auth;

trait AuthorizesRole
{
    protected function authorizeRole($role)
    {
        $user = Auth::user();

        if (!$user || $user->role !== $role) {
            abort(403, 'Unauthorized');
        }
    }
}
