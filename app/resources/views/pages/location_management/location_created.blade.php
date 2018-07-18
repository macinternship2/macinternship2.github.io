@extends('layouts.default')
@section('content')
<div class="location-created">
	
	@if(!empty($action))
	<h1 class="text-center">Location Modified</h1>
	<p class="text-center">The location has been modified.<p>
	@else
	<h1 class="text-center">Location Created</h1>
	<p class="text-center">The new location has been created.<p>
	@endif
	<div class="what-to-do-now">
		<p>What do you want to do now?</p>
		<nav>
			<ul>
				<li>
					<a href="/location-rating/{{ $location->id }}">Rate the @if(empty($action)) new @endif location</a>
				</li>
				<li>
					<a href="/add-location">Add Another Location</a>
				</li>
			</ul>
		</nav>
	</div>
</div>
@stop