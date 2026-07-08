@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/user.css') }}">
@endsection

@section('content')
<div class="user-form">
	<h1 class="user-form__title">会員登録</h1>

    <form class="user-form__content" action="{{ url('/register') }}" method="POST" novalidate>
        @csrf
        <div class="user-form__group">
            <div class="user-form__item">
                <label
                    class="user-form__label"
                    for="name"
                >
                    名前
                </label>
                <input
                    class="user-form__input"
                    id="name"
                    type="text"
                    name="name"
                    value="{{ old('name') }}"
                />
                <span class="input-form__error">
                    @error('name')
                        {{ $message }}
                    @enderror
                </span>
            </div>

            <div class="user-form__item">
                <label
                    class="user-form__label"
                    for="email"
                >
                    メールアドレス
                </label>
                <input
                    class="user-form__input"
                    id="email"
                    type="email"
                    name="email"
                    value="{{ old('email') }}"
                />
                <span class="input-form__error">
                    @error('email')
                        {{ $message }}
                    @enderror
                </span>
            </div>

            <div class="user-form__item">
                <label
                    class="user-form__label"
                    for="password"
                >
                    パスワード
                </label>
                <input
                    class="user-form__input"
                    id="password"
                    type="password"
                    name="password"
                />
                <span class="input-form__error">
                    @error('password')
                        @if ($message !== 'パスワードと一致しません')
                            {{ $message }}
                        @endif
                    @enderror
                </span>
            </div>

            <div class="user-form__item">
                <label
                    class="user-form__label"
                    for="password_confirmation"
                >
                    パスワード確認
                </label>
                <input
                    class="user-form__input"
                    id="password_confirmation"
                    type="password"
                    name="password_confirmation"
                />
                <span class="input-form__error">
                    @error('password')
                        @if ($message === 'パスワードと一致しません')
                            {{ $message }}
                        @endif
                    @enderror
                </span>
            </div>
        </div>

        <div class="button-wrapper">
            <button class="submit-button" type="submit">登録する</button>
        </div>
    </form>
    <div class="auth-link">
		<a class="auth-link__item" href="{{ url('/login') }}">ログインはこちら</a>
	</div>
</div>
@endsection

