@extends('layouts.default')
@section('content')
<div class="locations-added-by-me">
	<div class="text-center">
		<h1>Added Locations ({{ count($locations) }})</h1>
	</div>
	@if ( count($locations) === 0 )
		<p class="text-center">No locations have been added yet.  Add a location by clicking '+' below.</p>
		<p class="add-create"><a href="/add-location" title="Add New Location"><em class="fa fa-plus-circle"></em></a></p>
	@else
		<p class="add-create"><a href="/add-location" title="Add New Location"><em class="fa fa-plus-circle"></em></a></p>
	<div class="row column-titles">
		<div class="col-xs-4">
			<h3>Name</h3>
		</div>
		<div class="col-md-6 col-xs-4">
			<h3>Address</h3>
		</div>
		<div class="col-md-2 col-xs-4">
			<h3>Action</h3>
		</div>
		
	</div>
	<div class="data">
		@foreach ($locations as $location)
			<div class="row">
				<div class="col-xs-4">
					<a href="/location-rating/{{ $location->id }}">{{ $location->name }}</a>
				</div>
				<div class="col-md-6 col-xs-4">
					<a href="/location-rating/{{ $location->id }}">{{ $location->address }}</a>
				</div>
				<div class="col-md-2 col-xs-4">

					<div class="col-md-6 col-xs-6">
					<a href="/edit-my-location/{{ $location->id }}">Edit</a>
					</div>
					<div class="col-md-6 col-xs-6">
					@if ( $location->is_safe_to_delete )
						<a href="/delete-my-location/{{ $location->id }}">Delete</a>
					@else
						Unsafe
					@endif
					</div>
					
				</div>

			</div>
		@endforeach
	</div>
	@endif
</div>

@stop