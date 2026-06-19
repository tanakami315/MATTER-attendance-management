@extends('layouts.app')

@section('css')
	<link rel="stylesheet" href="{{ asset('css/after-login-common.css') }}">
	<link rel="stylesheet" href="{{ asset('css/detail.css') }}">
@endsection

@section('content')

<div class="card">
    <h1 class="title">
        勤怠詳細
    </h1>

    <div>{{ $user->name }}</div>
    <div>{{ $date }}</div>
    
</div>

@endsection