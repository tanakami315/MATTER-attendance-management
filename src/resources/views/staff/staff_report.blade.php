@extends('layouts.staff_app')

@section('css')
	<link rel="stylesheet" href="{{ asset('css/report.css') }}">
@endsection

@section('content')
<div class="report-card">
    <h1 class="report-title">
        マイ勤怠レポート
    </h1>
    <span class="report-comment">
        過去6カ月の勤怠データから集計しています。
    </span>

    <div class="report-group">
        <h2 class="report-group__title">基本サマリー</h>
        <div class="report-group__side">
            <div class="report-group__item">
                <label class="item-title">
                    総労働時間
                </label>
                <div class="item-content">
                    {{ $summary['total_work_time'] }}
                </div>
            </div>
            <div class="report-group__item">
                <label class="item-title">
                    総残業時間
                </label>
                <div class="item-content">
                    {{ $summary['total_overtime_time'] }}
                </div>
            </div>
            <div class="report-group__item">
                <label class="item-title">
                    平均労働時間/日
                </label>
                <div class="item-content">
                    {{ $summary['average_work_time'] }}
                </div>
            </div>
        </div>
    </div>

    <div class="report-group">
        <h2 class="report-group__title">月次推移（過去6ヵ月）</h>
        <table class="report-table__inner">
            <tr class="report-table__header">
                <th class="report-table__left-align-text">月</th>
                <th class="report-table__right-align-text">労働時間</th>
                <th class="report-table__right-align-text">残業時間</th>
            </tr>
            @foreach($monthlyTotals as $month => $total)
                <tr class="report-table__row">
                    <th class="report-table__left-align-text">{{ $month }}</th>
                    <th class="report-table__right-align-text">{{ $total['work_time'] }}</th>
                    <th class="report-table__right-align-text">{{ $total['overtime_time'] }}</th>
                </tr>
            @endforeach
        </table>
    </div>
    
    <div class="report-group">
        <h2 class="report-group__title">今月の異常検知</h>
        <p class="report-group__comment">
            基準：始業 09:00 / 就業 18:00 / 長時間労働は1日10時間超
        </p>
        <div class="report-group__side">
            <div class="report-group__item">
                <label class="item-title">
                    遅刻回数
                </label>
                <div class="item-content">{{ $lateCount }} 回</div>
            </div>
            <div class="report-group__item">
                <label class="item-title">
                    早退回数
                </label>
                <div class="item-content">{{ $earlyLeaveCount }} 回</div>
            </div>
            <div class="report-group__item">
                <label class="item-title">
                    長時間労働日数
                </label>
                <div class="item-content">{{ $longWorkCount }} 日</div>
            </div>
        </div>
    </div>
</div>
@endsection