@extends('layouts.app')

@section('css')
	<link rel="stylesheet" href="{{ asset('css/user.css') }}">
@endsection

@section('content')
<div class="user-form">
	<h1 class="user-form__title">管理者ログイン</h1>
	<form class="user-form__content" action="/login" method="post" novalidate>
		@csrf
		<input type="hidden" name="login_type" value="admin">
		<div class="user-form__group">
			<div class="user-form__item">
				<label
					class="user-form__label"
					for="email"
				>
					メールアドレス
				</label>
				<input
					class="user-form__input"
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
					type="password"
					name="password"
					value="{{ old('password') }}"
				/>
				<span class="input-form__error">
					@error('password')
						{{ $message }}
					@enderror
				</span>
			</div>
		</div>

		<div class="button-wrapper">
			<button class="submit-button" type="submit">管理者にログインする</button>
		</div>
	</form>
</div>
@endsection
