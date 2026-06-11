@extends('layouts.app')

@section('css')
	<link rel="stylesheet" href="{{ asset('css/after-login-common.css') }}">
	<link rel="stylesheet" href="{{ asset('css/detail.css') }}">
@endsection

@section('content')
<div class="card">
    <h1 class="title">
        勤怠詳細
    </h1>
    <div class="detail-table">
        <form class="application-form" action="/correct_request/{{ $attendance->id }}" method="POST">
            @method('post')
            @csrf
        <tr class="detail-table__row">   
            <th>名前</th>
            <td>{{ $attendance->user->name }}</td>
        </tr>
        <tr class="attendance-detail__row">
            <th>出勤・退勤</th>
            <td>
                @if($pendingCorrectRequest)
                    <span>
                        {{ $attendance->clock_in?->format('H:i') }} - {{ $attendance->clock_out?->format('H:i') }}
                    </span>
                @else
                    <input
                        type="text"
                        name="clock_in"
                        value="{{ $attendance->clock_in?->format('H:i') }}" 
                    >
                    <input
                        type="text"
                        name="clock_out"
                        value="{{ $attendance->clock_out?->format('H:i') }}"
                    >
                @endif
            </td>
        </tr>

        <tr class="attendance-detail__row">
            <th>休憩</th>
            <td>
                @if($pendingCorrectRequest)
                    <span>
                        {{ $break1?->start_break?->format('H:i') }} - {{ $break1?->end_break?->format('H:i') }}
                    </span>
                @else
                    <input
                        type="text"
                        name="start_break"
                        value="{{ $break1?->start_break?->format('H:i') }}"
                    >
                    <input
                        type="text"
                        name="end_break"
                        value="{{ $break1?->end_break?->format('H:i') }}"
                    >
                @endif
            </td>
        </tr>
         <tr class="attendance-detail__row">
            <th>休憩2</th>
            <td>
                @if($pendingCorrectRequest)
                    <span>
                        {{ $break2?->start_break?->format('H:i') }} - {{ $break2?->end_break?->format('H:i') }}
                    </span>
                @else
                    <input
                        type="text"
                        name="start_break2"
                        value="{{ $break2?->start_break?->format('H:i') }}"
                    >
                    <input
                        type="text"
                        name="end_break2"
                        value="{{ $break2?->end_break?->format('H:i') }}"
                    >
                @endif
            </td>
        </tr>
        <tr class="attendance-detail__row">
            <th>備考</th>
            <td>
                @if($pendingCorrectRequest)
                    <p>{{ $attendance->comment }}</p>
                @else
                <textarea
                    name="comment"
                    >
                </textarea>
                @endif
            </td>
        </tr>
        <div class="attendance-detail__button">
            @if ($pendingCorrectRequest)
                <p>※承認待ちのため修正できません</p>
            @else
                <button class="attendance-detail__request" type="submit">修正</button>
            @endif
        </div>
    </div>
@endsection
