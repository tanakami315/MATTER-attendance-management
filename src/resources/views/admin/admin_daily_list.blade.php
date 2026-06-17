@extends('layouts.app')

@section('css')
	<!-- 背景色、card、titleを記載 -->
	<link rel="stylesheet" href="{{ asset('css/after-login-common.css') }}">
	<!-- date-navigation以下を記載 -->
    <link rel="stylesheet" href="{{ asset('css/date-navigation.css') }}">
	<!-- list-table以下を記載 -->
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
            href="/admin/attendance/list?day={{ $prevDay }}"
        >
            <img
                class="date-navigation__link-icon"
                src="{{ asset('image/arrow.png') }}"
            >
            前日
        </a>
        <span class="date-navigation__current">
            <img
                class="date-navigation__current-icon"
                src="{{ asset('image/calendar.png') }}"
            >
            {{ $day->format('Y/m/d') }}
        </span>
        <a
            class="date-navigation__link"
            href="/admin/attendance/list?day={{ $nextDay }}"
        >
            <img
                class="date-navigation__link-icon date-navigation__link-icon--rotate"
                src="{{ asset('image/arrow.png') }}"
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
                <th class="list-table__center-align-text">詳細</th>
            </tr>

            @foreach ($attendances as $attendance)
                <tr class="list-table__row">
                    <td class="list-table__center-align-text">
                        {{ $attendance?->user?->name }}
                    </td>
                    <td class="list-table__center-align-text">
                        {{ optional($attendance->clock_in)->format('H:i') }}
                    </td>
                    <td class="list-table__center-align-text">
                        {{ optional($attendance->clock_out)->format('H:i') }}
                    </td>
                    <td class="list-table__center-align-text">
                        {{ $attendance?->break_time }}
                    </td>
                    <td class="list-table__center-align-text">
                        {{ $attendance?->work_time }}
                    </td>   
                    <td class="list-table__center-align-text">
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
