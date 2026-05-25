<?php

namespace App\Policies;

use App\Models\Teacher;
use App\Models\User;

class TeacherPolicy
{
    /**
     * Only principal + hr (+ admin/superadmin) can see compensation, payroll, NPWP.
     */
    public function viewSensitive(User $user, ?Teacher $teacher = null): bool
    {
        return in_array($user->role ?? 'teacher', ['principal', 'hr', 'admin', 'superadmin'], true);
    }

    /**
     * Only principal (+ admin/superadmin) can initiate sensitive changes.
     * Actual change is gated behind two-person approval flow.
     */
    public function changeSensitive(User $user, ?Teacher $teacher = null): bool
    {
        return in_array($user->role ?? 'teacher', ['principal', 'admin', 'superadmin'], true);
    }

    /**
     * Approvers (1st or 2nd) must be principal/vice_principal/hr (+admin) AND
     * cannot be the original requester.
     */
    public function approveSensitive(User $user): bool
    {
        return in_array($user->role ?? 'teacher', ['principal', 'vice_principal', 'hr', 'admin', 'superadmin'], true);
    }
}
