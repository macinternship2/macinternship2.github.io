<div class="rate-report-toggle">
	<div class="rate-report">
		<a class="{{ !$is_reporting ? 'selected' : '' }}" href="/location/rating/{{ $location_id }}/{{ implode("-", explode(" ", strtolower($category->name))) }}">Rate</a>
		<a class="{{ $is_reporting ? 'selected' : '' }}" href="/location/report/{{ $location_id }}/{{ implode("-", explode(" ", strtolower($category->name))) }}">View</a>
	</div>
	<div class="collapse-toggle-button">
		<span class="fa fa-bars"></span>
	</div>
	<div class="collapsible-menu">
		<ul>
			@foreach ($question_categories as $category)
			<li>
				<a href="{{ $base_url }}{{ $location_id }}/{{ $category->id }}"
				@if ( $category->id === $question_category->id )
					class="selected"
				@endif
				>
					{{ $category->name }}
				</a>
			</li>
			@endforeach
			@if ( !$is_reporting )
			<li class="text-center">
				<div class="submit">
					<form method="post" action="/location/rating/{{$location->id}}">
						{!! csrf_field() !!}
						<input type="submit" class="clean" value="Submit">
					</form>
				</div>
			</li>
			@endif
		</ul>
	</div>
</div>
