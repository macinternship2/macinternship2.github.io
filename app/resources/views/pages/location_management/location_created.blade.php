@extends('layouts.default')
@section('content')
<div class="location-created">
	<h1 class="text-center">Location Created</h1>

	<p class="text-center">The new location has been created.<p>
	<div class="what-to-do-now">
		<p>What do you want to do now?</p>
		<nav>
			<ul>
				<li>
					<a href="/location/rating/{{ $location->id }}">Rate the new location</a>
				</li>
				<li>
					<a href="/location/management">Add Another Location</a>
				</li>
			</ul>
		</nav>
	</div>
</div>
@stop