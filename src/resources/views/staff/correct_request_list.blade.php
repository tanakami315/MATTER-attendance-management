@extends('layouts.app')

@section('css')
	<link rel="stylesheet" href="{{ asset('css/after-login-common.css') }}">
	<link rel="stylesheet" href="{{ asset('css/list.css') }}">
@endsection

@section('content')
<div class="card">
    <h1 class="title">
        申請一覧
    </h1>

    <div class="list-table">
        <table class="list-table__inner">
            <tr class="list-table__header">
                <th class="list-table__text--center">状態</th>
                <th class="list-table__text--left">名前</th>
                <th class="list-table__text--left">対象日時</th>
                <th class="list-table__text--left">申請理由</th>
                <th class="list-table__text--left">申請日時</th>
                <th class="list-table__text--left">詳細</th>
            </tr>
            @foreach ($attendanceCorrectRequests as $attendanceCorrectRequest)
                <tr class="list-table__row">
                    <td class="list-table__text--center">
                        @if ($attendanceCorrectRequest->status == 0)
                            承認待ち
                        @elseif ($attendanceCorrectRequest->status == 1)
                            承認済み
                        @else
                            差戻
                        @endif
                    </td>
                    <td class="list-table__text--left">
                        {{ $attendanceCorrectRequest->attendance->user->name }}
                    </td>
                    <td class="list-table__text--left">
                        {{ $attendanceCorrectRequest->attendance->date->format('Y/m/d') }}
                    </td>
                    <td class="list-table__text--left">
                        {{ $attendanceCorrectRequest->comment }}
                    </td>
                    <td class="list-table__text--left">
                        {{ $attendanceCorrectRequest->created_at->format('Y/m/d') }}
                    </td>
                    <td class="list-table__text--center">
                        @if (session('login_type')==='staff')
                            <a
                                class="list-table__link list-table__link--flex"
                                href="{{ url('/attendance/detail/' . $attendanceCorrectRequest->attendance->id) }}"
                            >
                                詳細
                            </a>
                        @else
                            <a
                                class="list-table__link list-table__link--flex "
                                href="{{ url('/stamp_correction_request/approve/' . $attendanceCorrectRequest->attendance->id) }}" 
                            >
                                詳細
                            </a>
                        @endif


                    </td>
                </tr>
            @endforeach
        </table>
    </div>
@endsection