@extends('layouts.default')
@section('content')

<div class="row" style="margin-top: 40px">
	<div class="col-md-offset-3 col-md-6 col-sm-12 col-lg-offset-3 col-lg-6 col-xs-12">
		<div class="panel panel-default">
			<div class="panel-heading">
				<h3>Change Password</h3>
			</div>
			<div class="panel-body">
				<div class="form-group">
					<form method="post" action="/user/password-recovery/update">
						{!! csrf_field() !!}
						@include('pages.validation_messages', array('errors'=>$errors))
						<div class="row">
							<div class="col-md-12">
								<input type="hidden" name="email" value="{{ $email }}">
								<input type="hidden" name="recovery_token" value="{{ $token }}">
							</div>
						</div>
						<div class="row">
							<div class="col-md-12">
								<label>New Password</label>
								<input class="form-control" type="password" name="new_password"
									   placeholder="Please enter new password here">
							</div>
						</div>
						<div class="row" style="margin-top: 20px">
							<div class="col-md-12">
								<label>Confirm Password</label>
								<input class="form-control" type="password" name="confirm_password"
									   placeholder="Please re-enter the password again">
							</div>
						</div>
						<div class="row" style="margin-top: 20px">
							<div class="col-md-12">
								<button type="submit" class="btn btn-default"><i class="fa fa-key"></i> Update Password</button>
							</div>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
</div>
@stop
