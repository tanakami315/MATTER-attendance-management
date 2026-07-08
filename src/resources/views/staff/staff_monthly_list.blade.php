@extends('layouts.staff_app')

@section('css')
	<link rel="stylesheet" href="{{ asset('css/after-login-common.css') }}">
    <link rel="stylesheet" href="{{ asset('css/date-navigation.css') }}">
	<link rel="stylesheet" href="{{ asset('css/list.css') }}">
@endsection

@section('content')
<div class="card">
    <h1 class="title">
        勤怠一覧
    </h1>

    <div class="date-navigation">
        <a
            class="date-navigation__link"
            href="{{ url('/attendance/list?month=' . $prevMonth) }}"
        >
            <img
                class="date-navigation__link-icon"
                src="{{ asset('image/arrow.png') }}"
                alt="前月"
            >
            前月
        </a>
        <span class="date-navigation__current">
            <img
                class="date-navigation__current-icon"
                src="{{ asset('image/calendar.png') }}"
                alt="カレンダー"
            >
            {{ $month->format('Y/m') }}
        </span>
        <a
            class="date-navigation__link"
            href="{{ url('/attendance/list?month=' . $nextMonth) }}"
        >
            <img
                class="date-navigation__link-icon date-navigation__link-icon--rotate"
                src="{{ asset('image/arrow.png') }}"
                alt="翌月"
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
                <th class="list-table__left-align-text list-table__wide-text">日付</th>
                <th class="list-table__center-align-text">出勤</th>
                <th class="list-table__center-align-text">退勤</th>
                <th class="list-table__center-align-text">休憩</th>
                <th class="list-table__center-align-text">合計</th>
                <th class="list-table__center-align-text">詳細</th>
            </tr>

            @foreach ($dates as $date)
                @php
                    $attendance = $attendances->get($date->format('Y-m-d'));
                @endphp

                <tr class="list-table__row">
                    <td class="list-table__left-align-text list-table__wide-text">
                        {{ $date->format('m/d') }}（{{ $week[$date->dayOfWeek] }}）
                    </td>
                    <td class="list-table__center-align-text">
                        {{ $attendance?->clock_in?->format('H:i') }}
                    </td>
                    <td class="list-table__center-align-text">
                        {{ $attendance?->clock_out?->format('H:i') }}
                    </td>
                    <td class="list-table__center-align-text">
                        {{ $attendance?->break_time }}
                    </td>
                    <td class="list-table__center-align-text">
                        {{ $attendance?->work_time }}
                    </td>
                    <td class="list-table__center-align-text">
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
