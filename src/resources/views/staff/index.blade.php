@extends('layouts.app')

@section('css')
	<link rel="stylesheet" href="{{ asset('css/index.css') }}">
@endsection

@section('content')
	<div class="attendance-status">
		<!-- 勤務前 -->
		@if($status === 'before_work')
			<p>勤務外</p>
			<div class="attendance-date" id="current-date"></div>
			<div class="attendance-time" id="current-time"></div>
			<form
				action="/start-work"
				method="POST"
			>
				@csrf
				<button
					type="submit"
					class="attendance-button"
				>
					出勤
				</button>
			</form>
		
		<!-- 勤務中 -->
		@elseif($status === 'working')
			<p>勤務中</p>
			<div class="attendance-date" id="current-date"></div>
			<div class="attendance-time" id="current-time"></div>
			<form
				action="/end-work"
				method="POST"
			>
				@csrf
				<button
					type="submit"
					class="attendance-button"
				>
					退勤
				</button>
			</form>
			<form
				action="/start-break"
				method="POST"
			>
				@csrf
				<button
					type="submit"
					class="break-button"
				>
					休憩入
				</button>
			</form>

		<!-- 休憩中 -->
		@elseif($status === 'break')
			<p>休憩中</p>
			<div class="attendance-date" id="current-date"></div>
			<div class="attendance-time" id="current-time"></div>
			<form
				action="/end-break"
				method="POST"
			>
				@csrf
				<button
					type="submit"
					class="break-button"
				>
					休憩戻
				</button>
			</form>

		<!-- 勤務後 -->
		@elseif($status === 'after_work')
			<p>勤務外</p>
			<div class="attendance-date" id="current-date"></div>
			<div class="attendance-time" id="current-time"></div>
			<div class="attendance-comment">
				お疲れ様でした。
			</div>
		@endif
	</div>

	<script>
function updateClock() {
    const now = new Date();

    const weekdays = ['日', '月', '火', '水', '木', '金', '土'];

    document.getElementById('current-date').textContent =
        `${now.getFullYear()}年${now.getMonth() + 1}月${now.getDate()}日(${weekdays[now.getDay()]})`;

    document.getElementById('current-time').textContent =
        `${String(now.getHours()).padStart(2, '0')}:${String(now.getMinutes()).padStart(2, '0')}`;
}

// 初回表示
updateClock();

// 次の分の境目までのミリ秒
const now = new Date();
const delay = (60 - now.getSeconds()) * 1000 - now.getMilliseconds();

setTimeout(() => {
    updateClock();

    // 以後は毎分更新
    setInterval(updateClock, 60000);
}, delay);
</script>
@endsection