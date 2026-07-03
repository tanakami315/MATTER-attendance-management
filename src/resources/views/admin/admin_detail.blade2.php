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
    <form action="/admin/correct/{{ $attendance->id }}" method="POST">
        @csrf
        @method('POST')
        <div class="detail-table">
            <table class="detail-table__inner">
                <tr class="detail-table__row">
                    <th class="detail-table__label">名前</th>
                    <td class="detail-table__content">
                        {{ $attendance->user->name }}
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
                                value="{{ old('clock_in', $attendance->clock_in?->format('H:i')) }}" 
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
                                value="{{ old('clock_out', $attendance->clock_out?->format('H:i')) }}"
                            >
                        @endif
                    </td>
                </tr>

                @if ($isPending)
                    @foreach($breakCorrectRequests as $breakCorrectRequest)
                        <tr class="detail-table__row">
                            <th class="detail-table__label">
                                休憩{{ $loop->iteration > 1 ? $loop->iteration : '' }}
                            </th>
                            <td class="detail-table__content">
                                    {{ $breakCorrectRequest?->start_break?->format('H:i') }}
                            </td>
                            <td class="detail-table__content--mark">
                                ～
                            </td>
                            <td class="detail-table__content">
                                    {{ $breakCorrectRequest?->end_break?->format('H:i') }}
                            </td>
                        </tr>
                    @endforeach
                @else
                    @foreach($breakTimes as $index => $breakTime)
                        <tr class="detail-table__row">
                            <th class="detail-table__label">
                                休憩{{ $loop->iteration > 1 ? $loop->iteration : '' }}
                            </th>
                            <td class="detail-table__content">
                                <input
                                    class="detail-table__input"
                                    type="text"
                                    name="start_break[]"
                                    value="{{ old('start_break.' . $index, $breakTime?->start_break?->format('H:i')) }}"
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
                                    value="{{ old('end_break.' . $index, $breakTime?->end_break?->format('H:i')) }}"
                                >
                            </td>
                        </tr>
                    @endforeach

                    <tr class="detail-table__row">
                        <th class="detail-table__label">
                            休憩{{ $attendance->breakTimes->count() + 1 }}
                        </th>
                        <td class="detail-table__content">
                            <input
                                class="detail-table__input"
                                type="text"
                                name="start_break[]"
                                value="{{ old('start_break.' . $attendance->breakTimes->count()) }}"
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
                                value="{{ old('end_break.' . $attendance->breakTimes->count()) }}"
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
                                name="comment"
                            >{{ old('comment', $attendance->comment) }}</textarea>
                        @endif
                    </td>
                </tr>
            </table>
        </div>
        @if ($errors->any())
            <div class="detail-table__error">
                @foreach (array_unique($errors->all()) as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif
        <div class="detail-table__action">
            @if ($isPending)
                <span class="detail-table__message">*承認待ちのため修正はできません。</span>
            @else
                <button class="detail-table__button" type="submit">修正</button>
            @endif
        </div>
    </form>
</div>
@endsection
