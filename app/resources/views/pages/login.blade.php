@extends('layouts.default')
@section('content')
<h1>Sign In</h1>
<div class="form-group">
	<label class="control-label">Username</label>
	<input type="text">
</div>
<div class="form-group">
	<label class="control-label">Password</label>
	<input type="password">
</div>
<div class="form-group">
	<label class="control-label">Remember my login</label>
	<input type="checkbox">
</div>

<h4>Don't have an account?</h4>

<div class="btn">
	<a href="/signup">Sign up</a>
</div>

<div class="btn btn-block btn-social btn-large btn-facebook btn-foursquare">hi</div>
<div class="btn btn-block btn-social btn-large btn-google btn-foursquare">hi</div>
<div class="btn btn-block btn-social btn-large btn-twitter btn-foursquare">hi</div>

@stop