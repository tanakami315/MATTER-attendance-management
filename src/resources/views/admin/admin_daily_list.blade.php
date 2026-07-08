@extends('layouts.admin_app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/after-login-common.css') }}">
    <link rel="stylesheet" href="{{ asset('css/date-navigation.css') }}">
    <link rel="stylesheet" href="{{ asset('css/list.css') }}">
@endsection

@section('content')
<div class="card">
    <h1 class="title">
        {{ $day->format('Y年n月j日') }}の勤怠
    </h1>

    <div class="date-navigation">
        <a
            class="date-navigation__link"
            href="{{ url('/admin/attendance/list?day=' . $prevDay) }}"
        >
            <img
                class="date-navigation__link-icon"
                src="{{ asset('image/arrow.png') }}"
                alt="前日"
            >
            前日
        </a>
        <span class="date-navigation__current">
            <img
                class="date-navigation__current-icon"
                src="{{ asset('image/calendar.png') }}"
                alt="カレンダー"
            >
            {{ $day->format('Y/m/d') }}
        </span>
        <a
            class="date-navigation__link"
            href="{{ url('/admin/attendance/list?day=' . $nextDay) }}"
        >
            <img
                class="date-navigation__link-icon date-navigation__link-icon--rotate"
                src="{{ asset('image/arrow.png') }}"
                alt="翌日"
            >
            翌日
        </a>
    </div>

    <div class="list-table">
        <table class="list-table__inner">
            <tr class="list-table__row">
                <th class="list-table__center-align-text">名前</th>
                <th class="list-table__center-align-text">出勤</th>
                <th class="list-table__center-align-text">退勤</th>
                <th class="list-table__center-align-text">休憩</th>
                <th class="list-table__center-align-text">合計</th>
                <th class="list-table__center-align-text list-table__right-space">詳細</th>
            </tr>

            @foreach ($attendances as $attendance)
                <tr class="list-table__row">
                    <td class="list-table__center-align-text">
                        {{ $attendance?->user?->name }}
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
                    <td class="list-table__center-align-text list-table__right-space">
                        <a
                            class="list-table__link"
                            href="{{ url('/admin/attendance/' . $attendance->id) }}"
                        >
                            詳細
                        </a>
                    </td>
                </tr>
            @endforeach
        </table>
    </div>
</div>
@endsection
