@extends('layouts.app')

@section('css')
	<link rel="stylesheet" href="{{ asset('css/after-login-common.css') }}">
	<link rel="stylesheet" href="{{ asset('css/list.css') }}">
@endsection

@section('content')
<div class="card">
    <h1 class="title">
        勤怠一覧
    </h1>
    <div class="month-navigation">
        <a
            class="month-navigation__link"
            href="/attendance/list?month={{ $prevMonth }}"
        >
            <img
                class="month-navigation__link-icon"
                src="{{ asset('image/arrow.png') }}"
            >
            前月
        </a>
        <span class="month-navigation__current">
            <img
                class="month-navigation__current-icon"
                src="{{ asset('image/calendar.png') }}"
            >
            {{ $month->format('Y/m') }}
        </span>
        <a
            class="month-navigation__link"
            href="/attendance/list?month={{ $nextMonth }}"
        >
            <img
                class="month-navigation__link-icon month-navigation__link-icon--rotate"
                src="{{ asset('image/arrow.png') }}"
            >
            翌月
        </a>
    </div>

    @php
    $week = ['日', '月', '火', '水', '木', '金', '土'];
    @endphp
    
    <div class="list-table">
        <table class="list-table__inner">
            <tr class="list-table__header">
                <th class="list-table__text--left list-table__text--width">日付</th>
                <th class="list-table__text--center">出勤</th>
                <th class="list-table__text--center">退勤</th>
                <th class="list-table__text--center">休憩</th>
                <th class="list-table__text--center">合計</th>
                <th class="list-table__text--center">詳細</th>
            </tr>

            @foreach ($dates as $date)
                @php
                    $attendance = $attendances->get($date->format('Y-m-d'));
                @endphp
                
                <tr class="list-table__row">
                    <td class="list-table__text--left list-table__text--width">
                        {{ $date->format('m/d') }}（{{ $week[$date->dayOfWeek] }}）
                    </td>
                    <td class="list-table__text--center">
                        {{ $attendance?->clock_in?->format('H:i') }}
                    </td>
                    <td class="list-table__text--center">
                        {{ $attendance?->clock_out?->format('H:i') }}
                    </td>
                    <td class="list-table__text--center">
                        {{ $attendance?->break_time }}
                    </td>
                    <td class="list-table__text--center">
                        {{ $attendance?->work_time }}
                    </td>   
                    <td class="list-table__text--center">
                        @if ($attendance)
                            <a
                                class="list-table__link"
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
