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
				<form method="post" action="/signup">
					{!! csrf_field() !!}
					@include('pages.validation_messages', array('errors'=>$errors))
					<div class="row">
						<div class="col-xs-12">
							<input class="clean" name="email" placeholder="Email" value="{{ old('email') }}">
						</div>
						<div class="col-xs-6">
							<input class="clean" name="last_name" placeholder="Last Name" value="{{ old('last_name') }}">
						</div>
						<div class="col-xs-6">
							<input class="clean" name="first_name" placeholder="First Name" value="{{ old('first_name') }}">
						</div>
						<div class="col-xs-12">
							<input class="clean" name="password" type="password" placeholder="Password" value="{{ old('password') }}">
						</div>
						<div class="col-xs-12">
							<input class="clean" name="password_confirm" type="password" placeholder="Type your password again" value="{{ old('password_confirm') }}">
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
				
				<a class="facebook" href="">
					<i class="fa-lg fa fa-facebook"></i>
					<div class="pull-right">Sign in with facebook</div>
				</a>
				<a class="google-plus" href="">
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