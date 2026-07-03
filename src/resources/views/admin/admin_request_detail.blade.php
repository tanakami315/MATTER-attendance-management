@extends('layouts.staff_app')

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
    <form action="/admin/approve/{{ $attendanceCorrectRequest->id }}" method="POST">
        @csrf
        @method('POST')
        <div class="detail-table">
            <div class="detail-table__row">
                <div class="detail-table__label">名前</div>
                <div class="detail-table__value">
                    {{ $attendanceCorrectRequest->attendance->user->name }}
                </div>
            </div>

            <div class="detail-table__row">
                <div class="detail-table__label">日付</div>
                <div class="detail-table__value">
                    {{ $attendanceCorrectRequest->attendance->date->format('Y')}}年
                </div>
                <div></div>
                <div class="detail-table__value">
                    {{ $attendanceCorrectRequest->attendance->date->format('n月j日')}}
                </div>
            </div>

            <div class="detail-table__row">
                <div class="detail-table__label">出勤・退勤</div>
                <div class="detail-table__value">
                    <span>
                        {{ $attendanceCorrectRequest->clock_in?->format('H:i') }}
                    </span>
                </div>
                <div class="detail-table__mark">～</div>
                <div class="detail-table__value">
                    <span>
                        {{ $attendanceCorrectRequest->clock_out?->format('H:i') }}
                    </span>
                </div>
            </div>

            @foreach($attendanceCorrectRequest->breakCorrectRequests as $breakCorrectRequest)
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
            <div class="detail-table__row">
                <div class="detail-table__label">備考</div>
                <div class="detail-table__comment">
                    <p>{{ $attendanceCorrectRequest->comment }}</p>
                </div>
            </div>
        </div>
        @if($attendanceCorrectRequest?->status === 0)
            <div class="detail-table__action">
                <button class="detail-table__button" type="submit">承認</button>
            </div>
        @endif
    </form>
</div>
@endsection
