@extends('layouts.default')
@section('content')
	<div class="container-fluid">
		<div class="row">
			<div class="col-md-12">
				<h3><b>Reviewed Locations:</b></h3>
			</div>
		</div>
		<div class="row">
			<table class="table table-striped table-responsive">
				<thead>
					<tr>
						<th>Location Name</th>
						<th>Address</th>
						<th>Submitted On</th>
					</tr>
				</thead>
				<tbody>
					@foreach($reviews as $review)
						<tr>
							<td>
								<a href="/location/report/{{$review->location->id}}">{{$review->location->name}}</a>
							</td>
							<td>{{$review->location->address}}</td>
							<td>{{\Carbon\Carbon::parse($review->when_submitted)->toDayDateTimeString()}}</td>
						</tr>
					@endforeach
				</tbody>
			</table>
		</div>
	</div>
@stop