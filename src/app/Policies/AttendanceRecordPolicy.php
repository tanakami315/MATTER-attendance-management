<?php

namespace App\Policies;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class AttendanceRecordPolicy
{
    use HandlesAuthorization;

    public function before(User $user, $ability)
    {
        if ($user->admin_status) {
            return true;
        }

        return null;
    }
    /**
     * Determine whether the user can view any models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewAny(User $user)
    {
        //
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Attendance  $attendance
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, Attendance $attendance)
    {
        //
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user)
    {
        //
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Attendance  $attendance
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, Attendance $attendance)
    {

        // スタッフは自分の勤怠のみ更新可能
        return $user->id === $attendance->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Attendance  $attendance
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, Attendance $attendance)
    {
        // スタッフは自分の勤怠のみ更新可能
        return $user->id === $attendance->user_id;
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Attendance  $attendance
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, Attendance $attendance)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Attendance  $attendance
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, Attendance $attendance)
    {
        //
    }
}
