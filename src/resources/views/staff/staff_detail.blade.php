@extends('layouts.staff_app')

@section('css')
	<link rel="stylesheet" href="{{ asset('css/after-login-common.css') }}">
	<link rel="stylesheet" href="{{ asset('css/detail.css') }}">
@endsection

@section('content')
@php
    $isPending = $attendanceCorrectRequest?->status === 0;
@endphp

<div class="card">
    <h1 class="title">
        勤怠詳細
    </h1>
    <form action="{{ url('/stamp_correction_request/' . $attendance->id ) }}" method="POST">
        @csrf
        <div class="detail-table">
            <div class="detail-table__row">
                <div class="detail-table__label">名前</div>
                <div class="detail-table__value">
                    {{ $attendance->user->name }}
                </div>
            </div>

            <div class="detail-table__row">
                <div class="detail-table__label">日付</div>
                <div class="detail-table__value">
                    {{ $attendance->date->format('Y') }}年
                </div>
                <div></div>
                <div class="detail-table__value">
                    {{ $attendance->date->format('n月j日') }}
                </div>
            </div>

            <div class="detail-table__row">
                <div class="detail-table__label">出勤・退勤</div>
                <div class="detail-table__value">
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
                </div>
                <div class="detail-table__mark">～</div>
                <div class="detail-table__value">
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
                </div>
            </div>

            @if ($isPending)
                @foreach ($breakCorrectRequests as $breakCorrectRequest)
                    <div class="detail-table__row">
                        <div class="detail-table__label">
                            休憩{{ $loop->iteration > 1 ? $loop->iteration : '' }}
                        </div>
                        <div class="detail-table__value">
                            {{ $breakCorrectRequest?->start_break?->format('H:i') }}
                        </div>
                        <div class="detail-table__mark">～</div>
                        <div class="detail-table__value">
                            {{ $breakCorrectRequest?->end_break?->format('H:i') }}
                        </div>
                    </div>
                @endforeach
            @else
                @foreach ($breakTimes as $index => $breakTime)
                    <div class="detail-table__row">
                        <div class="detail-table__label">
                            休憩{{ $loop->iteration > 1 ? $loop->iteration : '' }}
                        </div>
                        <div class="detail-table__value">
                            <input
                                class="detail-table__input"
                                type="text"
                                name="start_break[]"
                                value="{{ old('start_break.' . $index, $breakTime?->start_break?->format('H:i')) }}"
                            >
                        </div>
                        <div class="detail-table__mark">～</div>
                        <div class="detail-table__content detail-table__right-space">
                            <input
                                class="detail-table__input"
                                type="text"
                                name="end_break[]"
                                value="{{ old('end_break.' . $index, $breakTime?->end_break?->format('H:i')) }}"
                            >
                        </div>
                    </div>
                @endforeach
                <div class="detail-table__row">
                    <div class="detail-table__label">
                        休憩{{ $attendance->breakTimes->count() + 1 > 1 ? $attendance->breakTimes->count() + 1 : '' }}
                    </div>
                    <div class="detail-table__value">
                        <input
                            class="detail-table__input"
                            type="text"
                            name="start_break[]"
                            value="{{ old('start_break.' . $attendance->breakTimes->count()) }}"
                        >
                    </div>
                    <div class="detail-table__mark">～</div>
                    <div class="detail-table__value">
                        <input
                            class="detail-table__input"
                            type="text"
                            name="end_break[]"
                            value="{{ old('end_break.' . $attendance->breakTimes->count()) }}"
                        >
                    </div>
                </div>
            @endif
            <div class="detail-table__row">
                <div class="detail-table__label">備考</div>
                <div class="detail-table__comment">
                    @if ($isPending)
                        <p>{{ $attendanceCorrectRequest->comment }}</p>
                    @else
                        <textarea
                            class="detail-table__textarea"
                            name="comment"
                        >{{ old('comment', $attendance->comment) }}</textarea>
                    @endif
                </div>
            </div>
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
