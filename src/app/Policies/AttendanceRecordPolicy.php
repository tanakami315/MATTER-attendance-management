<?php

namespace App\Policies;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class AttendanceRecordPolicy
{
    use HandlesAuthorization;

    /**
     * Perform pre-authorization checks.
     *
     * @param User $user
     * @param string $ability
     * @return bool|null
     */
    public function before(User $user, string $ability): ?bool
    {
        if ($user->admin_status) {
            return true;
        }

        return null;
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  User  $user
     * @param  Attendance  $attendance
     * @return bool
     */
    public function update(User $user, Attendance $attendance): bool
    {
        // スタッフは自分の勤怠のみ更新可能
        return $user->id === $attendance->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  User  $user
     * @param  Attendance  $attendance
     * @return bool
     */
    public function delete(User $user, Attendance $attendance): bool
    {
        // スタッフは自分の勤怠のみ削除可能
        return $user->id === $attendance->user_id;
    }
}