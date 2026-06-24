@extends('layouts.admin_app')

@section('css')
    <!-- 背景色、card、titleを記載 -->
	<link rel="stylesheet" href="{{ asset('css/after-login-common.css') }}">
	<!-- list-table以下を記載 -->
	<link rel="stylesheet" href="{{ asset('css/list.css') }}">
@endsection

@section('content')
<div class="card">
    <h1 class="title">
        スタッフ一覧
    </h1>

    <div class="list-table">
        <table class="list-table__inner">
            <tr class="list-table__row">
                <th class="list-table__center-align-text">名前</th>
                <th class="list-table__center-align-text">メールアドレス</th>
                <th class="list-table__center-align-text">月次勤怠</th>
            </tr>
        
            @foreach ($users as $user)
                <tr class="list-table__row">
                    <td class="list-table__center-align-text">
                        {{ $user?->name }}
                    </td>
                    <td class="list-table__center-align-text">
                        {{ $user?->email }}
                    </td>
                    <td class="list-table__center-align-text">
                        <a
                            class="list-table__link"
                            href="{{ url('admin/attendance/staff/' . $user->id) }}"
                        >
                            詳細
                        </a>
                    </td>
                </tr>
            @endforeach
        </table>
    </div>
</div>
@endsection
