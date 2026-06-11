@extends('layouts.app')

@section('css')
	<link rel="stylesheet" href="{{ asset('css/correct-request-list.css') }}">
@endsection

@section('content')
    <div class="correct-request-list">
        <h2 class="correct-request-list__title">申請一覧</h2>
        <table class="correct-request-list__table">
            <tr class="correct-request-list__header">
                <th>状態</th>
                <th>名前</th>
                <th>対象日時</th>
                <th>申請理由</th>
                <th>申請日時</th>
                <th>詳細</th>
            </tr>
            @foreach ($attendanceCorrectRequests as $attendanceCorrectRequest)
                <tr class="correct-request-list__row">
                    <td>
                        @if ($attendanceCorrectRequest->status == 0)
                            承認待ち
                        @elseif ($attendanceCorrectRequest->status == 1)
                            承認済み
                        @else
                            差戻
                        @endif
                    </td>
                    <td>{{ $attendanceCorrectRequest->attendance->user->name }}</td>
                    <td>{{ $attendanceCorrectRequest->attendance->date->format('Y-m-d') }}</td>
                    <td>{{ $attendanceCorrectRequest->comment }}</td>
                    <td>{{ $attendanceCorrectRequest->created_at->format('Y-m-d H:i:s') }}</td>
                    <td>
                        @if (session('login_type')==='staff')
                            <a
                                href="{{ url('/attendance/detail/' . $attendanceCorrectRequest->attendance->id) }}"
                            >
                                詳細
                            </a>
                        @else
                            <a
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