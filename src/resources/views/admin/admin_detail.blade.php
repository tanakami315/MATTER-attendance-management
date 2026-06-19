@extends('layouts.admin_app')

@section('css')
	<link rel="stylesheet" href="{{ asset('css/after-login-common.css') }}">
	<link rel="stylesheet" href="{{ asset('css/detail.css') }}">
@endsection

@section('content')
@php
    $isPending = isset($attendance) && $attendanceCorrectRequest?->status === 0;
@endphp

<div class="card">
    <h1 class="title">
        勤怠詳細
    </h1>
    <!-- 管理者申請承認 -->
    @if ($isPending)
        <form action="/admin/approve/{{ $attendanceCorrectRequest->id }}" method="POST">
            @csrf
            @method('POST')
    <!-- 管理者勤怠修正 -->
    @elseif ($attendance)
        <form action="/admin/correct/{{ $attendance->id }}" method="POST">
            @csrf
            @method('POST')
    <!-- 管理者勤怠作成 -->
    @else 
        <form action="/admin/corrsect/{{ $attendance->id }}" method="POST">
            @csrf
            @method('PATCH')
    @endif
        <div class="detail-table">
            <table class="detail-table__inner">
                <tr class="detail-table__row">   
                    <th class="detail-table__label">名前</th>
                    <td class="detail-table__content">
                        @if($attendance)
                            {{ $attendance->user->name }}
                        @else
                            test
                        @endif
                    </td>
                </tr>
                <tr class="detail-table__row">   
                    <th class="detail-table__label">日付</th>
                    <td class="detail-table__content">
                        {{ $attendance->date->format('Y')}}年
                    </td>
                    <td class="detail-table__content">
                    </td>
                    <td class="detail-table__content">
                        {{ $attendance->date->format('n月j日')}}
                    </td>
                    <td class="detail-table__content">
                    </td>
                    <td class="detail-table__content">
                    </td>
                </tr>
                <tr class="detail-table__row">
                    <th class="detail-table__label">出勤・退勤</th>
                    <td class="detail-table__content">
                        @if ($isPending)
                            <span>
                                {{ $attendanceCorrectRequest->clock_in?->format('H:i') }}
                            </span>
                        @else
                            <input
                                class="detail-table__input"
                                type="text"
                                name="clock_in"
                                value="{{ $attendance->clock_in?->format('H:i') }}" 
                            >
                        @endif
                    </td>
                    <td class="detail-table__content--mark">
                        ～
                    </td>
                    <td class="detail-table__content">
                        @if ($isPending)
                            <span>
                                {{ $attendanceCorrectRequest->clock_out?->format('H:i') }}
                            </span>
                        @else
                            <input
                                class="detail-table__input"
                                type="text"
                                name="clock_out"
                                value="{{ $attendance->clock_out?->format('H:i') }}"
                            >
                        @endif
                    </td>
                </tr>

                @foreach($breakTimes as $breakTime)
                <tr class="detail-table__row">
                    <th class="detail-table__label">
                        休憩{{ $loop->iteration > 1 ? $loop->iteration : '' }}
                    </th>
                    <td class="detail-table__content">
                        @if ($isPending)
                            @if($breakCorrectRequest)
                                <span>
                                    {{ $breakCorrectRequest?->start_break?->format('H:i') }}
                                </span>
                            @endif
                        @else
                            <input
                                class="detail-table__input"
                                type="text"
                                name="start_break[]"
                                value="{{ $breakTime?->start_break?->format('H:i') }}"
                            >
                        @endif
                    </td>
                    <td class="detail-table__content--mark">
                        ～
                    </td>
                    <td class="detail-table__content">
                        @if ($isPending)
                            <span>
                                {{ $breakCorrectRequest?->end_break?->format('H:i') }}
                            </span>
                        @else
                            <input
                                class="detail-table__input"
                                type="text"
                                name="end_break[]"
                                value="{{ $breakTime?->end_break?->format('H:i') }}"
                            >
                        @endif
                    </td>
                </tr>
                @endforeach

                @if(!$attendanceCorrectRequest || $attendanceCorrectRequest->status !== 0)
                    <tr class="detail-table__row">
                        <th class="detail-table__label">
                            休憩{{ $attendance->breakTimes->count() + 1 }}
                        </th>
                        <td class="detail-table__content">
                            <input
                                class="detail-table__input"
                                type="text"
                                name="start_break[]"
                            >
                        </td>
                        <td class="detail-table__content--mark">
                            ～
                        </td>
                        <td class="detail-table__content">
                            <input
                                class="detail-table__input"
                                type="text"
                                name="end_break[]"
                            >
                        </td>
                    </tr>
                @endif

                <tr class="detail-table__row">
                    <th class="detail-table__label">備考</th>
                    <td 
                        colspan="3"
                        class="detail-table__content--comment">
                        @if ($isPending)
                            <p>{{ $attendanceCorrectRequest->comment }}</p>
                        @else
                        <textarea
                            class="detail-table__textarea"
                            name="comment"></textarea>
                        @endif
                    </td>
                </tr>
            </table>
        </div>
        <div class="request-action">
            @if ($isPending)
                <button class="request-button" type="submit">承認</button>
            @else
                <button class="request-button" type="submit">修正</button>
            @endif            
        </div>
    </form>
</div>
@endsection
