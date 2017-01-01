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
					<textarea class="clean" id="comment"
						name="comment"
						placeholder="Your Comment"
						rows="5">{{ old('comment') }}</textarea>
				</div>
				<div class="col-xs-12">
					<div class="clean">
					{!! app('captcha')->display($attributes = ['data-size'=>'normal','data-theme'=>'light'], $lang = null); !!}
					<noscript>
						your javascript is disabled, plese enable it to continue.
					</noscript>
					</div>
				</div>
				<div class="col-xs-12">
					<input type="submit" class="clean" value="Send Message">
				</div>
			</div>
		</form>
	</div>
</div>
<script type="text/javascript">
//adjust the widget
	function ScaleReCaptcha()
	{
		widget = document.getElementsByClassName('g-recaptcha');
		if (widget.length > 0)
		{
			parentWidth = document.getElementById("comment").offsetWidth;
			childWidth = 304;
			scale = (parentWidth) / (childWidth);
			new_width = childWidth * scale;
			widget[0].style.transform = 'scale('+scale+',1)';
			widget[0].style.transformOrigin = '0 0';
		}
	}
	//onload 
	(function() {
		ScaleReCaptcha();
	})();
</script>

@stop