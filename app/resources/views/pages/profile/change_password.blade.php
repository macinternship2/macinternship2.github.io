@extends('layouts.default')
@section('content')
<div class="change-password">
	<h1>Change Password</h1>
	
	<div class="password-change-form">
		<form method="post" action="/user/change-password">
			{!! csrf_field() !!}
			@include('pages.validation_messages', array('errors'=>$errors))
			<div class="row">
				<div class="col-xs-12">
					<input type="hidden" name="_method" value="put">
					<input class="clean" type="password" name="current_password"
						placeholder="Current Password"
						value="">
				</div>
				<div class="col-xs-12">
					<input class="clean" type="password" name="new_password"
						placeholder="New Password"
						value="">
				</div>
				<div class="col-xs-12">
					<input class="clean" type="password" name="confirm_new_password"
						placeholder="Password Confirmation"
						value="">
				</div>
				<div class="col-xs-12">
					<input type="submit" class="clean" value="Update Password">
				</div>
			</div>
		</form>
	</div>
</div>
@stop