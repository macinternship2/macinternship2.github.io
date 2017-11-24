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
				<div class="message text-center">
					{{$message}}
				</div>
				@if ( $confirmmessage )
				<strong>{{ $confirmmessage }}</strong>
				@else
				<form method="post" action="/signin">
					{!! csrf_field() !!}
					@include('pages.validation_messages', array('errors'=>$errors))
					<div class="row">
						<div class="col-xs-12">
							<input type="email"
								class="clean" name="email"
								placeholder="Email" value="{{ old('email', $email) }}">
						</div>
						<div class="col-xs-12">
							<input class="clean" name="password" type="password" placeholder="Password" value="{{ old('password') }}">
						</div>
						
                        <div class="col-xs-12">
							<div class="remember-password">
								<label>
									<input type="checkbox"> Remember Me
								</label>
								<a class="pull-right" href="/password-recovery"> Forgot Password? </a>
							</div>
                        </div>
						
                                                
					</div>
					<div>
						<input class="clean" type="submit" value="Sign in">
					</div>
				</form>
				@endif
			</div> 
		</div>
		<div class="col-md-6">
			<div class="social-media-signins">
				Or sign in using your social media account
				
				<a class="facebook" href="/socialauth/auth/Facebook">
					<i class="fa-lg fa fa-facebook"></i>
					<div class="pull-right">Sign in with facebook</div>
				</a>
				<a class="google-plus" href="/socialauth/auth/Google">
					<i class="fa-lg fa fa-google-plus"></i>
					<div class="pull-right">Sign in with Google</div>
				</a>
			</div>
		</div>
	</div>
</div>

@stop