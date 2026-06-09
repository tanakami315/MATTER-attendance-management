@extends('layouts.app')

@section('css')
	<link rel="stylesheet" href="{{ asset('css/list.css') }}">
@endsection

@section('content')
<div class="attendance-month">
    <a href="/attendance/list?month={{ $prevMonth }}">前月</a>
    <span>{{ $month->format('Y/m') }}</span>
    <a href="/attendance/list?month={{ $nextMonth }}">翌月</a>
</div>

@php
$week = ['日', '月', '火', '水', '木', '金', '土'];
@endphp
<div class="attendance-table">
    <table class="attendance-table__inner">
        <tr class="attendance-table__row">
            <th class="attendance-table__header">
                <span class="attendance-table__header-title">日付</span>
                <span class="attendance-table__header-title">出勤</span> 
                <span class="attendance-table__header-title">退勤</span> 
                <span class="attendance-table__header-title">休憩</span> 
                <span class="attendance-table__header-title">合計</span> 
                <span class="attendance-table__header-title">詳細</span> 
            </th>
        </tr>
        @foreach ($dates as $date)
            @php
                $attendance = $attendances->get($date->format('Y-m-d'));
            @endphp
            <tr class="attendance-table__row">
                <td class="attendance-table__daily">
                    <span>{{ $date->format('m/d') }}（{{ $week[$date->dayOfWeek] }}）</span>
                    <span>{{ $attendance?->clock_in?->format('H:i') }}</span>
                    <span>{{ $attendance?->clock_out?->format('H:i') }}</span>
                    <span>{{ $attendance?->break_time }}</span>
                    <span>{{ $attendance?->work_time }}</span>   
                    @if ($attendance)
                    <a
                        href="{{ url('/attendance/detail/' . $attendance->id) }}"
                    >
                        詳細
                    </a>
                    @endif
                </td>
            </tr>
        @endforeach
    </table>
  </div>
</div>
@endsection
