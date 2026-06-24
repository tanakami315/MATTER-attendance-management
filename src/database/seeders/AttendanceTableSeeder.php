<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use Carbon\Carbon;

class AttendanceTableSeeder extends Seeder
{
    public function run()
    {
        $user1 = User::where('email', 'user1@example.com')->firstOrFail();
        $user2 = User::where('email', 'user2@example.com')->firstOrFail();

        // 既存勤怠を削除
        Attendance::whereIn('user_id',[
            $user1->id,
            $user2->id,
        ])->delete();

        // user1の勤怠
        // 過去5ヶ月：各月 平日15日、9:00-18:00
        for ($i = 5; $i >= 1; $i--) {
            $month = now()->subMonths($i)->startOfMonth();
            $count = 0;

            for ($date = $month->copy(); $date->month === $month->month; $date->addDay()) {
                if ($date->isWeekend()) {
                    continue;
                }

                $this->createAttendance($user1, $date, '09:00', '18:00', '12:00', '13:00');
                $count++;

                if ($count >= 15) {
                    break;
                }
            }
        }

        // 当月17日分
        $thisMonth = now()->startOfMonth();
        $workDates = [];

        for ($date = $thisMonth->copy(); $date->month === $thisMonth->month; $date->addDay()) {
            if (!$date->isWeekend()) {
                $workDates[] = $date->copy();
            }

            if (count($workDates) >= 17) {
                break;
            }
        }

        // 通常10日
        for ($i = 0; $i < 10; $i++) {
            $this->createAttendance($user1, $workDates[$i], '09:00', '18:00');
        }

        // 残業3日：9:00-20:00
        for ($i = 10; $i < 13; $i++) {
            $this->createAttendance($user1, $workDates[$i], '09:00', '20:00');
        }

        // 遅刻2日：9:30-18:00
        for ($i = 13; $i < 15; $i++) {
            $this->createAttendance($user1, $workDates[$i], '09:30', '18:00');
        }

        // 早退1日：9:00-17:00
        $this->createAttendance($user1, $workDates[15], '09:00', '17:00');

        // 長時間労働1日：8:00-21:00
        $this->createAttendance($user1, $workDates[16], '08:00', '21:00');

        //  user2の勤怠
        // 過去5ヶ月：各月 平日15日、9:00-18:00
        for ($i = 5; $i >= 1; $i--) {
            $month = now()->subMonths($i)->startOfMonth();
            $count = 0;

            for ($date = $month->copy(); $date->month === $month->month; $date->addDay()) {
                if ($date->isWeekend()) {
                    continue;
                }

                $this->createAttendance($user2, $date, '09:00', '18:00', '12:30', '13:30');
                $count++;

                if ($count >= 15) {
                    break;
                }
            }
        }

        // 当月16日分
        $thisMonth = now()->startOfMonth();
        $workDates = [];

        for ($date = $thisMonth->copy(); $date->month === $thisMonth->month; $date->addDay()) {
            if (!$date->isWeekend()) {
                $workDates[] = $date->copy();
            }

            if (count($workDates) >= 16) {
                break;
            }
        }

        // 通常5日
        for ($i = 0; $i < 5; $i++) {
            $this->createAttendance($user2, $workDates[$i], '09:00', '18:00', '12:30', '13:30');
        }

        // 残業3日：9:00-20:00
        for ($i = 5; $i < 8; $i++) {
            $this->createAttendance($user2, $workDates[$i], '09:00', '20:00', '12:30', '13:30');
        }

        // 遅刻2日：9:30-18:00
        for ($i = 8; $i < 10; $i++) {
            $this->createAttendance($user2, $workDates[$i], '09:30', '18:00', '12:30', '13:30');
        }

        // 遅刻2日：13:00-18:00 休憩なし
        for ($i = 10; $i < 12; $i++) {
            $this->createAttendance($user2, $workDates[$i], '13:00', '18:00', null, null);
        }

        // 早退2日：9:00-17:00
        for ($i = 12; $i < 14; $i++) {
            $this->createAttendance($user2, $workDates[$i], '09:00', '17:00');
        }

        // 早退1日：9:00-12:00 休憩なし
        for ($i = 14; $i < 15; $i++) {
            $this->createAttendance($user2, $workDates[$i], '09:00', '12:00', null, null);
        }

        // 長時間労働1日：9:00-21:00 休憩2回
        $attendance = $this->createAttendance($user2, $workDates[15], '09:00', '21:00');
        BreakTime::create([
            'attendance_id' => $attendance->id,
            'start_break' => Carbon::parse(
                $workDates[15]->format('Y-m-d') . ' 18:00'
            ),
            'end_break' => Carbon::parse(
                $workDates[15]->format('Y-m-d') . ' 18:15'
            ),
        ]);

    }

    private function createAttendance(
            $user,
            Carbon $date,
            string $clockIn,
            string $clockOut,
            ?string $breakStart = '12:00',
            ?string $breakEnd = '13:00'
        ) {
            $attendance = Attendance::create([
                'user_id' => $user->id,
                'date' => $date->format('Y-m-d'),
                'clock_in' => Carbon::parse($date->format('Y-m-d') . ' ' . $clockIn),
                'clock_out' => Carbon::parse($date->format('Y-m-d') . ' ' . $clockOut),
                'comment' => null,
            ]);

            if ($breakStart && $breakEnd) {
                BreakTime::create([
                    'attendance_id' => $attendance->id,
                    'start_break' => Carbon::parse($date->format('Y-m-d') . ' ' . $breakStart),
                    'end_break' => Carbon::parse($date->format('Y-m-d') . ' ' . $breakEnd),
                ]);
            }

            return $attendance;
        }
}
