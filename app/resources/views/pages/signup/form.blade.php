@extends('layouts.default')
@section('content')

<div class="sign-up">
	<div class="text-center header">
		<h1>Sign Up</h1>
		<p>Already have an AccessLocator account? <a href="/signin">Sign in</a></p>
	</div>
	<div class="row">
		<div class="col-md-6 col-xs-12">
			<div class="signup-form">
				@if (Session::has('confirm_message'))
					<div class="alert alert-success">
						<p>{{Session::get('confirm_message')}}</p>
					</div>
				@endif
				<form method="post" action="/signup">
					{!! csrf_field() !!}
					<div class="row">
						<div class="col-xs-12">
							<input class="clean" name="email" placeholder="Email" value="{{ old('email') }}">
							@if($errors->has('email'))
								<p class="text-danger">{{$errors->get('email')[0]}}</p>
							@endif
						</div>
						<div class="col-xs-6">
							<input class="clean" name="last_name" placeholder="Last Name" value="{{ old('last_name') }}">
							@if($errors->has('last_name'))
								<p class="text-danger">{{$errors->get('last_name')[0]}}</p>
							@endif
						</div>
						<div class="col-xs-6">
							<input class="clean" name="first_name" placeholder="First Name" value="{{ old('first_name') }}">
							@if($errors->has('first_name'))
								<p class="text-danger">{{$errors->get('first_name')[0]}}</p>
							@endif
						</div>
						<div class="col-xs-12">
							<input class="clean" name="password" type="password" placeholder="Password" value="{{ old('password') }}">
							@if($errors->has('password'))
								<p class="text-danger">{{$errors->get('password')[0]}}</p>
							@endif
						</div>
						<div class="col-xs-12">
							<input class="clean" name="password_confirm" type="password" placeholder="Type your password again" value="{{ old('password_confirm') }}">
							@if($errors->has('password_confirm'))
								<p class="text-danger">{{$errors->get('password_confirm')[0]}}</p>
							@endif
						</div>
						<div class="col-xs-12">
							{!! app('captcha')->display(); !!}
							@if($errors->has('g-recaptcha-response'))
								<p class="text-danger">{{$errors->get('g-recaptcha-response')[0]}}</p>
							@endif
						</div>
					</div>
					<div>
						<input class="clean" type="submit" value="Sign Up">
					</div>
				</form>
			</div>
		</div>
		<div class="col-md-6 col-xs-12">
			<div class="social-media-signins">
				Or sign in using your social media account
				
				<a rel="nofollow" class="facebook" href="/social_auth/callback/facebook">
					<i class="fa-lg fa fa-facebook"></i>
					<div class="pull-right">Sign in with facebook</div>
				</a>
				<a rel="nofollow" class="google-plus" href="/social_auth/callback/google">
					<i class="fa-lg fa fa-google-plus"></i>
					<div class="pull-right">Sign in with Google</div>
				</a>
			</div>
		</div>
	</div>
	<div class="text-center agreements">
		<p>By signing up you agree to our <a href="/terms-of-use">Terms of Use</a>,
		<a href="/privacy-policy">privacy policy</a>, and to receive newsletters &amp; updates.</p>
	</div>
</div>

@stop