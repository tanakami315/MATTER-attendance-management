@extends('layouts.app')

@section('css')
	<link rel="stylesheet" href="{{ asset('css/list.css') }}">
@endsection

@section('content')
<div class="attendance-index">
    <a href="/admin/attendance/list?day={{ $prevDay }}">前日</a>
    <span>{{ $day->format('Y/m/d') }}</span>
    <a href="/admin/attendance/list?day={{ $nextDay }}">翌日</a>
</div>

<div class="attendance-table">
    <table class="attendance-table__inner">
        <tr class="attendance-table__row">
            <th class="attendance-table__header">
                <span class="attendance-table__header-title">名前</span>
                <span class="attendance-table__header-title">日付</span>
                <span class="attendance-table__header-title">出勤</span> 
                <span class="attendance-table__header-title">退勤</span> 
                <span class="attendance-table__header-title">休憩</span> 
                <span class="attendance-table__header-title">合計</span> 
                <span class="attendance-table__header-title">詳細</span> 
            </th>
        </tr>
        <tr class="attendance-table__row">
            <td class="attendance-table__daily">
                @foreach ($attendances as $attendance)
                    <span>{{ $attendance?->user?->name }}</span>
                    <span>{{ optional($attendance->clock_in)->format('H:i') }}</span>
                    <span>{{ optional($attendance->clock_out)->format('H:i') }}</span>
                    <span>{{ $attendance?->break_time }}</span>
                    <span>{{ $attendance?->work_time }}</span>   
                    @if ($attendance)
                        <a
                            href="{{ url('/admin/attendance/' . $attendance->id) }}"
                        >
                            詳細
                        </a>
                    @endif
                @endforeach
            </td>
        </tr>
    </table>
  </div>
</div>
@endsection
