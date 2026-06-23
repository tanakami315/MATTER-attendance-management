@extends('layouts.admin_app')

@section('css')
    <!-- 背景色、card、titleを記載 -->
	<link rel="stylesheet" href="{{ asset('css/after-login-common.css') }}">
	<!-- label以下を記載 -->
	<link rel="stylesheet" href="{{ asset('css/status-label.css') }}">
	<!-- list-table以下を記載 -->
	<link rel="stylesheet" href="{{ asset('css/list.css') }}">
@endsection

@section('content')
<div class="card">
    <h1 class="title">
        申請一覧
    </h1>
    
    <nav class="status-label">
        <div class="status-label__inner">
            <a
                href="{{ url('/stamp_correction_request/list/?tab=pending') }}"
                class="status-label__link {{ request('tab')=='pending'?' status-label__link--active' : '' }}"
            >
                承認待ち
            </a>
            <a
                href="{{ url('/stamp_correction_request/list/?tab=approved') }}"
                class="status-label__link {{ request('tab')=='approved'?' status-label__link--active' : '' }}"
            >
                承認済み
            </a>
        </div>
    </nav>
    
    <div class="list-table">
        <table class="list-table__inner">
            <tr class="list-table__header">
                <th class="list-table__left-align-text">状態</th>
                <th class="list-table__left-align-text">名前</th>
                <th class="list-table__left-align-text">対象日時</th>
                <th class="list-table__left-align-text">申請理由</th>
                <th class="list-table__left-align-text">申請日時</th>
                <th class="list-table__left-align-text">詳細</th>
            </tr>
            @foreach ($attendanceCorrectRequests as $attendanceCorrectRequest)
                <tr class="list-table__row">
                    <td class="list-table__left-align-text">
                        @if ($attendanceCorrectRequest->status == 0)
                            承認待ち
                        @elseif ($attendanceCorrectRequest->status == 1)
                            承認済み
                        @else
                            差戻
                        @endif
                    </td>
                    <td class="list-table__left-align-text">
                        {{ $attendanceCorrectRequest->attendance->user->name }}
                    </td>
                    <td class="list-table__left-align-text">
                        {{ $attendanceCorrectRequest->attendance->date->format('Y/m/d') }}
                    </td>
                    <td class="list-table__left-align-text list-table__comment">
                        {{ $attendanceCorrectRequest->comment }}
                    </td>
                    <td class="list-table__left-align-text">
                        {{ $attendanceCorrectRequest->created_at->format('Y/m/d') }}
                    </td>
                    <td class="list-table__left-align-text">
                        <a
                            class="list-table__link list-table__flex-link "
                            href="{{ url('/stamp_correction_request/approve/' . $attendanceCorrectRequest->id) }}" 
                        >
                            詳細
                        </a>
                    </td>
                </tr>
            @endforeach
        </table>
    </div>
@endsection