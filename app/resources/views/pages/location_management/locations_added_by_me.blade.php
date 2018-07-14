@extends('layouts.default')
@section('content')
	<div class="row" style="margin-left: 20px">
		<div class="col-md-8 col-xs-12">
			<h3><b>Locations added by me: </b>{{count($locations)}}</h3>
		</div>
		<div class="col-md-4 col-xs-12">
			<a href="/location/management">
				<button class="btn btn-default" style="margin-top: 22px"><i class="fa fa-location-arrow"></i> Add New Location</button>
			</a>
		</div>
	</div>

	<div class="row" style="margin-top: 20px">
		<div class="col-md-12">
			@if (count($locations) > 0)
				<table class="table table-striped table-responsive">
					<thead>
						<tr>
							<th>Location Name</th>
							<th>Address</th>
							<th>Actions</th>
						</tr>
					</thead>
					<tbody>
						@foreach($locations as $location)
							<tr>
								<td>
									<a href="/location/rating/{{ $location->id }}">{{ $location->name }}</a>
								</td>
								<td>
									<a href="/location/rating/{{ $location->id }}">{{ $location->address }}</a>
								</td>
								<td>
									<form method="post" action="/location/management/{{$location->id}}">
										{!! csrf_field() !!}
										<input type="hidden" name="_method" value="DELETE">
										<button type="submit" class="btn btn-danger">
											<i class="fa fa-trash-o"></i> Delete
										</button>
									</form>
								</td>
							</tr>
						@endforeach
					</tbody>
		</table>
			@else
				<h4 class="text-center">No locations have been added by you!</h4>
			@endif
		</div>
	</div>
@stop