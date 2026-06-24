@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/verify-email.css') }}">
@endsection

@section('content')
<div class="verify-email">
    <p class="verify-email__message">
        登録していただいたメールアドレスに認証メールを送付しました。<br>
        メール認証を完了してください。
    </p>
    
    <div class="verify-email__button">
        <a
            class=verify-email__link-button
            href=http://localhost:8025
        >
            認証はこちらから
        </a>
        <form
            action="{{ route('verification.send') }}"
            method="POST"
        >
            @csrf
            <button
                class="verify-email__remail-button"
                type="submit"
            >
                認証メールを再送する
            </button>
        </form>
    </div>
</div>
@endsection