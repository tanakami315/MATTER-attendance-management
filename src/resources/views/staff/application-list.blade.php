@extends('layouts.app')

@section('css')
	<link rel="stylesheet" href="{{ asset('css/application-list.css') }}">
@endsection

@section('content')
    <div class="application-list">
        <h2 class="application-list__title">申請一覧</h2>
        <table class="application-list__table">
            <tr class="application-list__header">
                <th>状態</th>
                <th>名前</th>
                <th>対象日時</th>
                <th>申請理由</th>
                <th>申請日時</th>
                <th>詳細</th>
            </tr>
            @foreach ($applications as $application)
                <tr class="application-list__row">
                    <td>
                        @if ($application->status == 0)
                            承認待ち
                        @elseif ($application->status == 1)
                            承認済み
                        @else
                            差戻
                        @endif
                    </td>
                    <td>{{ $application->attendance->user->name }}</td>
                    <td>{{ $application->attendance->date->format('Y-m-d') }}</td>
                    <td>{{ $application->comment }}</td>
                    <td>{{ $application->created_at->format('Y-m-d H:i:s') }}</td>
                    <td>
                        <a
                            href="{{ url('/attendance/detail/' . $application->attendance->id) }}"
                        >
                            詳細
                        </a>
                    </td>
                </tr>
            @endforeach
        </table>
    </div>
@endsection