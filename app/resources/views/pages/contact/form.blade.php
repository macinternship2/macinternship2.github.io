@extends('layouts.default')
@section('content')
<div class="contact">
	<h1>Contact</h1>
	<div class="contact-form">
		<form method="post" action="/contact">
			{!! csrf_field() !!}
			@include('pages.validation_messages', array('errors'=>$errors))					
			<div class="row">
				<div class="col-xs-12">
					<input class="clean" type="email" name="email"
						placeholder="Your Email"
						value="{{ old('email') }}">
				</div>
				<div class="col-xs-12">
					<textarea class="clean"
						name="comment"
						placeholder="Your Comment"
						rows="5">{{ old('comment') }}</textarea>
				</div>
				<div class="col-xs-12">
					{!! app('captcha')->display(); !!}
				</div>
				<div class="col-xs-12">
					<input type="submit" class="clean" value="Send Message">
				</div>
			</div>
		</form>
	</div>
</div>

@stop