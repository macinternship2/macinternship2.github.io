@extends('layouts.default')
@section('head-content')
	<script>
	var locationPoint = {lat: {{ $location->latitude }}, lng: {{ $location->longitude }} };	
	</script>
	<script language="JavaScript" src="/js/jquery-3.1.1.js"></script>	
	<script src="/js/location_report.js"></script>
	<script language="JavaScript" src="/js/pie_graph.js"></script>
@stop
@section('footer-content')
	@if (!$turn_off_maps)
	<script async defer
		src="//maps.googleapis.com/maps/api/js?key={{ $google_map_api_key }}&callback=initMap">
    </script>
	@endif
@stop
@section('content')

<div class="location-report">
	@include('pages.location_report.top', array(
		'rating_system' => $rating_system,
		'location' => $location))
	<div class="basic-info">
		<div class="addresses">
		<p><em class="fa fa-map-marker"></em>{{ $location->address }}</p>
		<p><em class="fa fa-phone"></em>{{ $location->phone_number }}</p>
		<a href="{{ $location->getExternalWebURL() }}">{{ $location->getExternalWebURL() }}</a>
		</div>
		<div class="location-tags text-right">
			<div class="location-tags">
			@foreach ( $location->tags()->orderBy('name')->get() as $location_tag )
				<a class="location-tag" title="{{ $location_tag->name }}" href="/location-search?location_tag_id={{ $location_tag->id }}">
					<span class="name">{{ $location_tag->name }}</span>
					<span class="icon {{ $location_tag->icon_selector }}"></span>
				</a>
			@endforeach
			</div>
		</div>
	</div>
	@if ( $is_internal_user )
		<a class="modify-location" href="/location-modify/{{ $location->id }}">Modify</a>
		<a class="find-duplicate-location" href="/duplicate-location-finder?location_id={{ $location->id }}">Find Duplicates</a>
	@endif
	<div class="map-and-box">
		<div class="questions-box @if ( $num_ratings === 0 )
			unrated
		@else
			rated
		@endif">
			@if ($rating_system === 'personal' && !$personal_rating_is_available)
				@include('pages.location_report.personal_not_available', ['location_id' => $location->id])
			@elseif ($num_ratings === 0)
				<div class="title-bar">
					<h3>{{ $location->name }}</h3>
				</div>
				<div class="questions">
					<div class="question-category">
						<h4 class="text-center">Not Rated Yet</h4>
						
						<p><a href="/location-rating/{{ $location->id }}/6">Be the first to rate this location.</a></p>
					</div>
				</div>
			@else
				<div class="title-bar">
					<div class="graph">
						@include('pages.components.pie_graph',
							array(
								'percent' => $location->getAccessibilityRating($rating_system),
								'size' => 'big'))
					</div>
					<div class="location-name-and-comments">
						<div class="location-name">
							<h3 class="@if (strlen($location->name) > 30) 
								long-name
						@endif">{{ $location->name }}</h3>
						</div>
						<div class="comments">
							<a href="/location-comments/{{ $location->id }}">See All Comments</a>
						</div>
					</div>
					<div class="location-rating">
						<div class="percentage">{{ round($location->getAccessibilityRating($rating_system)) }}% <span class="accessible">accessible</span></div>
						<div class="num-ratings">{{ $num_ratings }} ratings</div>
					</div>
				</div>
				<div class="questions">
					@foreach ( $question_categories as $category )
						<div class="question-category">
							<a href="/location-reporting/{{ $location->id }}/{{ $category->id }}">
								<div class="graph">
									@include('pages.components.pie_graph', array('percent' => $category->getAccessibilityRating($location->id, 'universal')))
								</div>
								<div class="category-name">{{ $category->name }}</div>

								<div class="location-category-rating">
									<div class="percentage">
										<span>{{ $category->getAccessibilityRating($location->id, 'universal').'%' }}</span> accessible
									</div>
									<div class="ratings">
										{{ $category_rating_counts[$category->id] }} ratings
									</div>
								</div>
							</a>
						</div>
					@endforeach
				</div>
			@endif
			<div class="text-center">
				Close
			</div>
		</div>
		<div id="map">
		</div>
	</div>
</div>

@stop