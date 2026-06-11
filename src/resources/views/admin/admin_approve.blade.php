@extends('layouts.app')

@section('css')
	<link rel="stylesheet" href="{{ asset('css/detail.css') }}">
@endsection

@section('content')
<form class="application-form" action="/admin/approve/{{ $attendance->id }}" method="POST">
    @method('post')
    @csrf
    <div class="attendance-detail">
        <h2 class="attendance-detail__title">勤怠詳細</h2>
        <tr class="attendance-detail__row">   
            <th>名前</th>
            <td>{{ $attendance->user->name }}</td>
        </tr>
        <tr class="attendance-detail__row">
            <th>出勤・退勤</th>
            <td>
                <input
                    type="text"
                    name="clock_in"
                    value="{{ $attendance->clock_in?->format('H:i') }}" 
                >
                <input
                    type="text"
                    name="clock_out"
                    value="{{ $attendance->clock_out?->format('H:i') }}"
                 @if($pendingCorrectRequest) readonly @endif
                >
            </td>
        </tr>

        <tr class="attendance-detail__row">
            <th>休憩</th>
            <td>
                <input
                    type="text"
                    name="start_break"
                    value="{{ $break1?->start_break?->format('H:i') }}"
                >
                <input
                    type="text"
                    name="end_break"
                    value="{{ $break1?->end_break?->format('H:i') }}"
                    @if($pendingCorrectRequest) readonly @endif    
                >
            </td>
        </tr>
         <tr class="attendance-detail__row">
            <th>休憩2</th>
            <td>
                <input
                    type="text"
                    name="start_break2"
                    value="{{ $break2?->start_break?->format('H:i') }}"
                >
                <input
                    type="text"
                    name="end_break2"
                    value="{{ $break2?->end_break?->format('H:i') }}"
                    @if($pendingCorrectRequest) readonly @endif
                >
            </td>
        </tr>
        <tr class="attendance-detail__row">
            <th>備考</th>
            <td>
                <input
                    type="text"
                    name="comment"
                    value="{{ $attendance->comment}}" 
                    @if($pendingCorrectRequest) readonly @endif
                >
            </td>
        </tr>
        <div class="attendance-detail__button">
            @if ($pendingCorrectRequest)
                <button class="attendance-detail__request" type="submit">承認</button>
            @endif
        </div>
    </div>
@endsection
