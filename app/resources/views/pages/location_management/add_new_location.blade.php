@extends('layouts.default')
@section('head-content')
	<script src="/css/jquery/external/jquery/jquery.js"></script>
	<script type="text/javascript" language="JavaScript" src="/js/utils.js"></script>
	<script src="/js/add_location.js"></script>
@stop
@section('footer-content')
	@if ( !$turn_off_maps )
	<script async defer
		src="//maps.googleapis.com/maps/api/js?key={{ $google_map_api_key }}&amp;callback=initMap">
    </script>
	@endif
@stop
@section('content')
<div class="add-location @if ( !$errors->isEmpty() ) with-errors @endif">
	<h1>Add New Location</h1>
	@include('pages.validation_messages', array('errors'=>$errors, 'show_only_first' => true))
	<form method="post" action="/location/management">
		{!! csrf_field() !!}
		<input type="hidden" id="latitude" name="latitude" value="{{ $location->latitude }}">
		<input type="hidden" id="longitude" name="longitude" value="{{ $location->longitude }}">
		<div>
			<label for="name">Name</label>
			<input id="name" name="name" value="{{ $location->name }}">
			<label for="phone_number">Phone Number</label>
			<input id="phone_number" name="phone_number" value="{{ $location->phone_number }}">
			<div class="map-and-location-tags">
				<div id="map"></div>
				<div class="location-tags">
					<label for="address">Address</label>
					<input id="address" name="address" value="{{ $location->address }}">
					<label for="external_web_url">URL</label>
					<input id="external_web_url" name="external_web_url" type="url" value="{{ $location->external_web_url }}">
					<label for="location_tags">Location Categories</label>
					<select id="location_tags" name="location_tags[]" multiple>
						<option id="location-tag-i-do-not-know" value="-">I don't know</option>
						@foreach ($location_tags as $location_tag)
							<option value="{{ $location_tag->id }}" @if ($location_tag->is_selected) selected @endif>{{ $location_tag->name }}</option>
						@endforeach
					</select>
					<label for="location_group_id">Group/Franchise</label> 
					<select id="location_group_id" name="location_group_id">
						<option value="none">None</option>
					@foreach ($location_groups as $location_group) 
						<option value="{{ $location_group->id }}" @if ( $location->location_group_id === $location_group->id )
							selected
						@endif>{{ $location_group->name }}</option>
					@endforeach
						<option value="-">Other</option>
					</select>
					<button class="btn btn-primary" type="submit">Add</button>
				</div>
			</div>
		</div>
	</form>
</div>
@stop