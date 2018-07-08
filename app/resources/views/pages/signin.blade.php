@extends('layouts.default', ['body_class' => 'nav-profile'])
@section('content')

<div class="sign-in">
	<div class="text-center header">
		<h1>Sign In</h1>
		<p>New to AccessLocator? <a href="/signup">Sign Up</a></p>
	</div>
	<div class="row">
		<div class="col-md-6">
			<div class="signin-form">
				<form method="post" action="/signin">
					@if (Session::has('verification_message'))
						<div class="alert alert-success">
							{{Session::get('verification_message')}}
						</div>
					@endif
					@if($errors->has('message'))
						<div class="alert alert-danger">
							<p>{{$errors->get('message')[0]}}</p>
						</div>
					@endif
					@if($errors->has('email'))
						<div class="alert alert-danger">
							<p>{{$errors->get('email')[0]}}</p>
						</div>
					@endif
					{!! csrf_field() !!}
					<div class="row">
						<div class="col-xs-12">
							<input type="email"
								class="clean" name="email"
								placeholder="Email" value="{{ old('email') }}">
						</div>
						<div class="col-xs-12">
							<input class="clean" name="password" type="password" placeholder="Password" value="{{ old('password') }}">
							@if ($errors->has('password'))
								<p class="text-danger">{{$errors->get('password')[0]}}</p>
							@endif
						</div>
                        <div class="col-xs-12">
							<div class="remember-password">
								<input type="checkbox" name="remember_me" />
								<span>Remember Me</span>
								<a class="pull-right" href="/user/password-recovery">Forgot Password?</a>
								<a class="pull-right" href="/user/verification-mail">Didn't get verification email?</a>
							</div>
                        </div>
					</div>
					<div>
						<input class="clean" type="submit" value="Sign in">
					</div>
				</form>
			</div> 
		</div>
		<div class="col-md-6">
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
</div>

@stop