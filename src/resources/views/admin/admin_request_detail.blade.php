@extends('layouts.admin_app')

@section('css')
	<link rel="stylesheet" href="{{ asset('css/after-login-common.css') }}">
	<link rel="stylesheet" href="{{ asset('css/detail.css') }}">
@endsection

@section('content')
<div class="card">
    <h1 class="title">
        勤怠詳細
    </h1>
    <form action="/admin/approve/{{ $attendanceCorrectRequest->id }}" method="POST">
        @csrf
        @method('POST')
        <div class="detail-table">
            <table class="detail-table__inner">
                <tr class="detail-table__row">
                    <th class="detail-table__label">名前</th>
                    <td class="detail-table__content">
                        {{ $attendanceCorrectRequest->attendance->user->name }}
                    </td>
                </tr>
                <tr class="detail-table__row">
                    <th class="detail-table__label">日付</th>
                    <td class="detail-table__content">
                        {{ $attendanceCorrectRequest->attendance->date->format('Y')}}年
                    </td>
                    <td class="detail-table__content">
                    </td>
                    <td class="detail-table__content">
                        {{ $attendanceCorrectRequest->attendance->date->format('n月j日')}}
                    </td>
                    <td class="detail-table__content">
                    </td>
                    <td class="detail-table__content">
                    </td>
                </tr>
                <tr class="detail-table__row">
                    <th class="detail-table__label">出勤・退勤</th>
                    <td class="detail-table__content">
                        <span>
                            {{ $attendanceCorrectRequest->clock_in?->format('H:i') }}
                        </span>
                    </td>
                    <td class="detail-table__content--mark">
                        ～
                    </td>
                    <td class="detail-table__content">
                        <span>
                            {{ $attendanceCorrectRequest->clock_out?->format('H:i') }}
                        </span>
                    </td>
                </tr>

                @foreach($attendanceCorrectRequest->breakCorrectRequests as $breakCorrectRequest)
                    <tr class="detail-table__row">
                        <th class="detail-table__label">
                            休憩{{ $loop->iteration > 1 ? $loop->iteration : '' }}
                        </th>
                        <td class="detail-table__content">
                            <span>
                                {{ $breakCorrectRequest?->start_break?->format('H:i') }}
                            </span>
                        </td>
                        <td class="detail-table__content--mark">
                            ～
                        </td>
                        <td class="detail-table__content">
                            <span>
                                {{ $breakCorrectRequest?->end_break?->format('H:i') }}
                            </span>
                        </td>
                    </tr>
                @endforeach
                <tr class="detail-table__row">
                    <th class="detail-table__label">備考</th>
                    <td
                        colspan="3"
                        class="detail-table__content--comment">
                        <p>{{ $attendanceCorrectRequest->comment }}</p>
                    </td>
                </tr>
            </table>
        </div>
        @if($attendanceCorrectRequest?->status === 0)
            <div class="detail-table__action">
                <button class="detail-table__button" type="submit">承認</button>
            </div>
        @endif
    </form>
</div>
@endsection
