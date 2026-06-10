@extends('layouts.app')

@section('css')
	<link rel="stylesheet" href="{{ asset('css/list.css') }}">
@endsection

@section('content')
<div class="attendance-table">
    <table class="attendance-table__inner">
        <tr class="attendance-table__row">
            <th class="attendance-table__header">
                <span class="attendance-table__header-title">名前</span>
                <span class="attendance-table__header-title">メールアドレス</span>
                <span class="attendance-table__header-title">月次勤怠</span> 
            </th>
        </tr>
        <tr class="attendance-table__row">
            <td class="attendance-table__daily">
                @foreach ($users as $user)
                    <span>{{ $user?->name }}</span>
                    <span>{{ $user?->email }}</span>
                    <a
                        href="{{ url('admin/attendance/staff/' . $user->id) }}"
                    >
                        詳細
                    </a>
                @endforeach
            </td>
        </tr>
    </table>
  </div>
</div>
@endsection
